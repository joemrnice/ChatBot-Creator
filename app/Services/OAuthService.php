<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Application;
use App\Core\Database;
use App\Core\Security;
use App\Core\Session;
use RuntimeException;

class OAuthService
{
    public function isProviderConfigured(string $provider): bool
    {
        $config = Application::config("oauth.{$provider}", []);
        return !empty($config['enabled']) && !empty($config['client_id']) && !empty($config['client_secret']);
    }

    public function getAuthorizationUrl(string $provider): string
    {
        if (!$this->isProviderConfigured($provider)) {
            throw new RuntimeException("OAuth provider '{$provider}' is not configured.");
        }

        $config = Application::config("oauth.{$provider}");
        $state = Security::generateToken(24);
        Session::set("oauth_state_{$provider}", $state);

        $params = [
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect_uri'],
            'response_type' => 'code',
            'scope' => $config['scope'],
            'state' => $state,
        ];

        if ($provider === 'google') {
            $params['access_type'] = 'online';
            $params['prompt'] = 'select_account';
        }

        return $config['authorize_url'] . '?' . http_build_query($params);
    }

    public function handleCallback(string $provider, string $code, string $state): array
    {
        $savedState = Session::get("oauth_state_{$provider}");
        Session::remove("oauth_state_{$provider}");

        if (empty($savedState) || !hash_equals($savedState, $state)) {
            throw new RuntimeException('Invalid OAuth state. Possible CSRF attack detected.');
        }

        $tokenData = $this->exchangeCodeForToken($provider, $code);
        $accessToken = $tokenData['access_token'] ?? null;
        if (!$accessToken) {
            throw new RuntimeException("Failed to obtain access token from {$provider}.");
        }

        $profile = $this->fetchUserProfile($provider, $accessToken);
        return $this->findOrCreateUser($provider, $profile, $tokenData);
    }

    private function exchangeCodeForToken(string $provider, string $code): array
    {
        $config = Application::config("oauth.{$provider}");

        $fields = [
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'code' => $code,
            'redirect_uri' => $config['redirect_uri'],
            'grant_type' => 'authorization_code',
        ];

        $ch = curl_init($config['token_url']);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($fields),
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'User-Agent: ChatBotCreatorStudio/1.0',
            ],
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode((string)$response, true);
        if ($httpCode !== 200 || !is_array($data)) {
            // Some providers return URL-encoded response if not requesting json
            parse_str((string)$response, $parsed);
            if (isset($parsed['access_token'])) {
                return $parsed;
            }
            throw new RuntimeException("Token exchange failed for {$provider}: " . ($data['error'] ?? 'Unknown error'));
        }

        return $data;
    }

    private function fetchUserProfile(string $provider, string $accessToken): array
    {
        $config = Application::config("oauth.{$provider}");

        $ch = curl_init($config['user_url']);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$accessToken}",
                'User-Agent: ChatBotCreatorStudio/1.0',
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode((string)$response, true);
        if (!is_array($data)) {
            throw new RuntimeException("Failed to fetch user profile from {$provider}.");
        }

        // Normalize profile structure
        return match ($provider) {
            'github' => $this->normalizeGitHubProfile($data, $accessToken),
            'google' => [
                'id' => (string)($data['sub'] ?? ''),
                'name' => $data['name'] ?? 'Google User',
                'email' => $data['email'] ?? null,
                'avatar' => $data['picture'] ?? null,
                'email_verified' => (bool)($data['email_verified'] ?? false),
            ],
            'facebook' => [
                'id' => (string)($data['id'] ?? ''),
                'name' => $data['name'] ?? 'Facebook User',
                'email' => $data['email'] ?? null,
                'avatar' => $data['picture']['data']['url'] ?? null,
                'email_verified' => true,
            ],
            default => throw new RuntimeException("Unsupported OAuth provider: {$provider}"),
        };
    }

    private function normalizeGitHubProfile(array $data, string $accessToken): array
    {
        $email = $data['email'] ?? null;

        // If email is private in GitHub profile, fetch from emails endpoint
        if (!$email) {
            $ch = curl_init('https://api.github.com/user/emails');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer {$accessToken}",
                    'User-Agent: ChatBotCreatorStudio/1.0',
                    'Accept: application/json',
                ],
                CURLOPT_TIMEOUT => 10,
            ]);
            $res = curl_exec($ch);
            curl_close($ch);

            $emails = json_decode((string)$res, true);
            if (is_array($emails)) {
                foreach ($emails as $entry) {
                    if (!empty($entry['primary']) && !empty($entry['verified'])) {
                        $email = $entry['email'];
                        break;
                    }
                }
            }
        }

        return [
            'id' => (string)($data['id'] ?? ''),
            'name' => $data['name'] ?? $data['login'] ?? 'GitHub User',
            'email' => $email,
            'avatar' => $data['avatar_url'] ?? null,
            'email_verified' => !empty($email),
        ];
    }

    private function findOrCreateUser(string $provider, array $profile, array $tokenData): array
    {
        $providerUserId = $profile['id'];
        $email = !empty($profile['email']) ? strtolower(trim($profile['email'])) : null;
        $name = $profile['name'] ?? 'User';
        $avatar = $profile['avatar'] ?? null;

        return Database::transaction(function () use ($provider, $providerUserId, $email, $name, $avatar, $tokenData) {
            // 1. Check if OAuth link already exists
            $oauthAccount = Database::fetchOne(
                "SELECT * FROM oauth_accounts WHERE provider = :p AND provider_user_id = :puid LIMIT 1",
                ['p' => $provider, 'puid' => $providerUserId]
            );

            if ($oauthAccount) {
                $user = Database::fetchOne("SELECT * FROM users WHERE id = :id LIMIT 1", ['id' => $oauthAccount['user_id']]);
                if ($user) {
                    // Update oauth tokens
                    Database::update('oauth_accounts', [
                        'token_data' => json_encode($tokenData),
                        'avatar' => $avatar,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ], 'id = :id', ['id' => $oauthAccount['id']]);

                    return $user;
                }
            }

            // 2. If email provided, check if user exists with that email
            $user = null;
            if ($email) {
                $user = Database::fetchOne("SELECT * FROM users WHERE email = :email LIMIT 1", ['email' => $email]);
            }

            if (!$user) {
                // 3. Create new user assigned to Free plan
                $freePlan = Database::fetchOne("SELECT id FROM plans WHERE slug = 'free' LIMIT 1");
                $planId = $freePlan ? (int)$freePlan['id'] : 1;

                $userId = Database::insert('users', [
                    'uuid' => Security::generateUuid(),
                    'name' => $name,
                    'email' => $email ?? ($provider . '_' . $providerUserId . '@oauth.local'),
                    'password_hash' => null, // OAuth-only user
                    'avatar_url' => $avatar,
                    'plan_id' => $planId,
                    'status' => 'active',
                    'email_verified_at' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                // Assign user role
                $userRole = Database::fetchOne("SELECT id FROM roles WHERE slug = 'user' LIMIT 1");
                if ($userRole) {
                    Database::query(
                        "INSERT IGNORE INTO user_roles (user_id, role_id, created_at) VALUES (:uid, :rid, NOW())",
                        ['uid' => $userId, 'rid' => $userRole['id']]
                    );
                }

                $user = Database::fetchOne("SELECT * FROM users WHERE id = :id LIMIT 1", ['id' => $userId]);
            }

            // 4. Link OAuth Account
            Database::query(
                "INSERT INTO oauth_accounts (user_id, provider, provider_user_id, avatar, token_data, created_at, updated_at)
                 VALUES (:uid, :prov, :puid, :av, :td, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE avatar = VALUES(avatar), token_data = VALUES(token_data), updated_at = NOW()",
                [
                    'uid' => $user['id'],
                    'prov' => $provider,
                    'puid' => $providerUserId,
                    'av' => $avatar,
                    'td' => json_encode($tokenData),
                ]
            );

            return $user;
        });
    }
}

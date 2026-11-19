<?php
declare(strict_types=1);

namespace App\Core;

class Auth
{
    private static ?array $cachedUser = null;

    public static function check(): bool
    {
        return Session::has('user_id');
    }

    public static function id(): ?int
    {
        return Session::get('user_id');
    }

    public static function user(): ?array
    {
        if (self::$cachedUser !== null) {
            return self::$cachedUser;
        }

        $userId = self::id();
        if (!$userId) {
            return null;
        }

        try {
            $sql = "SELECT u.*, p.name as plan_name, p.slug as plan_slug, s.status as subscription_status,
                           s.id as subscription_id, s.current_period_end
                    FROM users u
                    LEFT JOIN subscriptions s ON s.user_id = u.id AND s.status IN ('active', 'trialing', 'past_due')
                    LEFT JOIN plans p ON p.id = COALESCE(s.plan_id, u.plan_id)
                    WHERE u.id = :id AND u.status = 'active'
                    LIMIT 1";
            $user = Database::fetchOne($sql, ['id' => $userId]);
            if ($user) {
                // Fetch user roles
                $roles = Database::fetchAll(
                    "SELECT r.slug FROM roles r
                     JOIN user_roles ur ON ur.role_id = r.id
                     WHERE ur.user_id = :id",
                    ['id' => $userId]
                );
                $user['roles'] = array_column($roles, 'slug');
                self::$cachedUser = $user;
                return $user;
            }
        } catch (\Throwable $e) {
            error_log('Auth user fetch error: ' . $e->getMessage());
        }

        return null;
    }

    public static function attempt(string $email, string $password): bool
    {
        $user = Database::fetchOne(
            "SELECT * FROM users WHERE email = :email LIMIT 1",
            ['email' => strtolower(trim($email))]
        );

        if (!$user) {
            return false;
        }

        if ($user['status'] === 'suspended') {
            return false;
        }

        if (!Security::verifyPassword($password, $user['password_hash'])) {
            return false;
        }

        self::login($user);
        return true;
    }

    public static function login(array $user): void
    {
        Session::regenerate(true);
        Session::set('user_id', (int)$user['id']);
        self::$cachedUser = null;

        // Update last login
        try {
            Database::update(
                'users',
                ['last_login_at' => date('Y-m-d H:i:s')],
                'id = :id',
                ['id' => $user['id']]
            );
        } catch (\Throwable $e) {
            // Non-blocking
        }
    }

    public static function logout(): void
    {
        Session::remove('user_id');
        self::$cachedUser = null;
        Session::destroy();
    }

    public static function hasRole(string $role): bool
    {
        $user = self::user();
        if (!$user || !isset($user['roles'])) {
            return false;
        }
        return in_array($role, $user['roles'], true);
    }

    public static function isAdmin(): bool
    {
        $user = self::user();
        if (!$user || !isset($user['roles'])) {
            return false;
        }
        return in_array('superadmin', $user['roles'], true) || in_array('admin', $user['roles'], true);
    }
}

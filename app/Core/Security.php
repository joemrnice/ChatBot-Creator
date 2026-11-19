<?php
declare(strict_types=1);

namespace App\Core;

class Security
{
    public static function generateToken(int $bytes = 32): string
    {
        return bin2hex(random_bytes($bytes));
    }

    public static function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public static function generateBotToken(string $prefix = 'bot_'): string
    {
        return $prefix . bin2hex(random_bytes(16));
    }

    public static function getCsrfToken(): string
    {
        $token = Session::get('_csrf_token');
        if (!$token) {
            $token = self::generateToken(32);
            Session::set('_csrf_token', $token);
        }
        return $token;
    }

    public static function validateCsrfToken(?string $token): bool
    {
        if (empty($token)) {
            return false;
        }
        $known = Session::get('_csrf_token');
        if (!$known) {
            return false;
        }
        return hash_equals($known, $token);
    }

    public static function hashPassword(string $password): string
    {
        $algo = Application::config('auth.password_algo', PASSWORD_BCRYPT);
        $options = Application::config('auth.password_options', []);
        return password_hash($password, $algo, $options);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function encrypt(string $plainText): string
    {
        $key = hash('sha256', (string)Application::config('app.secret', 'fallback-key'), true);
        $iv = random_bytes(16);
        $ciphertext = openssl_encrypt($plainText, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $iv . $ciphertext, $key, true);
        return base64_encode($iv . $hmac . $ciphertext);
    }

    public static function decrypt(string $cipherText): ?string
    {
        $key = hash('sha256', (string)Application::config('app.secret', 'fallback-key'), true);
        $decoded = base64_decode($cipherText, true);
        if ($decoded === false || strlen($decoded) < 48) {
            return null;
        }

        $iv = substr($decoded, 0, 16);
        $hmac = substr($decoded, 16, 32);
        $ciphertext = substr($decoded, 48);

        $calculatedHmac = hash_hmac('sha256', $iv . $ciphertext, $key, true);
        if (!hash_equals($hmac, $calculatedHmac)) {
            return null;
        }

        $decrypted = openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return $decrypted !== false ? $decrypted : null;
    }

    public static function checkRateLimit(string $key, int $maxHits, int $decaySeconds): bool
    {
        $cacheDir = Application::getInstance()->storagePath('cache/rate-limits');
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }

        $file = $cacheDir . DIRECTORY_SEPARATOR . md5($key) . '.json';
        $now = time();
        $data = ['hits' => 0, 'expires' => $now + $decaySeconds];

        if (file_exists($file)) {
            $content = @file_get_contents($file);
            $parsed = json_decode((string)$content, true);
            if (is_array($parsed) && isset($parsed['expires']) && $parsed['expires'] > $now) {
                $data = $parsed;
            }
        }

        if ($data['hits'] >= $maxHits) {
            return false;
        }

        $data['hits']++;
        @file_put_contents($file, json_encode($data), LOCK_EX);
        return true;
    }

    public static function sanitizeString(?string $str): string
    {
        return trim(strip_tags((string)($str ?? '')));
    }

    public static function sanitizeHtml(?string $html): string
    {
        return strip_tags((string)($html ?? ''), '<p><br><b><strong><i><em><u><a><ul><ol><li><code><pre>');
    }
}

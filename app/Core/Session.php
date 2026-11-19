<?php
declare(strict_types=1);

namespace App\Core;

class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        if (headers_sent()) {
            if (session_status() !== PHP_SESSION_ACTIVE) {
                @session_start();
            }
            self::$started = true;
            self::ageFlash();
            return;
        }

        $securityConfig = Application::config('security', []);
        $cookieParams = $securityConfig['session_cookie_params'] ?? [
            'lifetime' => 86400 * 7,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax',
        ];

        session_name($securityConfig['session_name'] ?? 'CBC_SESSION');
        session_set_cookie_params([
            'lifetime' => $cookieParams['lifetime'] ?? 86400 * 7,
            'path' => $cookieParams['path'] ?? '/',
            'domain' => $cookieParams['domain'] ?? '',
            'secure' => $cookieParams['secure'] ?? false,
            'httponly' => $cookieParams['httponly'] ?? true,
            'samesite' => $cookieParams['samesite'] ?? 'Lax',
        ]);

        @session_start();
        self::$started = true;

        // Age out flash data from previous request
        self::ageFlash();
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function regenerate(bool $deleteOldSession = true): bool
    {
        self::start();
        return session_regenerate_id($deleteOldSession);
    }

    public static function destroy(): void
    {
        self::start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
        self::$started = false;
    }

    public static function flash(string $key, mixed $value): void
    {
        self::start();
        $_SESSION['_flash']['new'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION['_flash']['old'][$key] ?? $default;
    }

    public static function hasFlash(string $key): bool
    {
        self::start();
        return isset($_SESSION['_flash']['old'][$key]);
    }

    private static function ageFlash(): void
    {
        if (isset($_SESSION['_flash']['old'])) {
            unset($_SESSION['_flash']['old']);
        }

        if (isset($_SESSION['_flash']['new'])) {
            $_SESSION['_flash']['old'] = $_SESSION['_flash']['new'];
            unset($_SESSION['_flash']['new']);
        } else {
            $_SESSION['_flash']['old'] = [];
        }
    }
}

<?php
declare(strict_types=1);

/**
 * Global Helper Functions for ChatBot Creator Studio
 */

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed {
        return \App\Core\Application::env($key, $default);
    }
}

if (!function_exists('app')) {
    function app(): \App\Core\Application {
        return \App\Core\Application::getInstance();
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed {
        return \App\Core\Application::config($key, $default);
    }
}

if (!function_exists('e')) {
    function e(?string $value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string {
        $base = \App\Core\Request::basePath();
        $path = '/' . ltrim($path, '/');
        if ($path === '/') {
            return $base ?: '/';
        }
        return ($base ? rtrim($base, '/') : '') . $path;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string {
        return url('public-assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        return \App\Core\Security::getCsrfToken();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed {
        return \App\Core\Session::getFlash('old_' . $key, $default);
    }
}

if (!function_exists('auth_user')) {
    function auth_user(): ?array {
        return \App\Core\Auth::user();
    }
}

if (!function_exists('auth_id')) {
    function auth_id(): ?int {
        return \App\Core\Auth::id();
    }
}

if (!function_exists('is_admin')) {
    function is_admin(): bool {
        return \App\Core\Auth::isAdmin();
    }
}

if (!function_exists('flash')) {
    function flash(string $key): mixed {
        return \App\Core\Session::getFlash($key);
    }
}

if (!function_exists('json_response')) {
    function json_response(array $data, int $status = 200): void {
        \App\Core\Response::json($data, $status);
    }
}

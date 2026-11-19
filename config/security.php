<?php
declare(strict_types=1);

return [
    'csrf_token_name' => 'csrf_token',
    'csrf_header_name' => 'X-CSRF-Token',
    'session_name' => 'CBC_SESSION',
    'session_cookie_params' => [
        'lifetime' => 86400 * 7,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax',
    ],
    'rate_limit' => [
        'api_requests_per_minute' => 60,
        'login_attempts_per_ip_hour' => 20,
    ],
    'content_security_policy' => "default-src 'self' https: data: 'unsafe-inline' 'unsafe-eval';",
];

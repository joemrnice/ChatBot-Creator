<?php
declare(strict_types=1);

return [
    'session_lifetime' => 86400 * 7, // 7 days
    'password_algo' => defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT,
    'password_options' => [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 1,
    ],
    'lockout_threshold' => 5, // max failed attempts
    'lockout_duration' => 900, // 15 minutes
    'token_expiry' => 3600, // 1 hour for reset/verify tokens
];

<?php
declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'ChatBot Creator Studio'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool)env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'secret' => env('APP_SECRET', 'change-this-to-a-secure-random-key'),
    'version' => '1.0.0',
];

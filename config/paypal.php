<?php
declare(strict_types=1);

return [
    'mode' => env('PAYPAL_MODE', 'sandbox'), // sandbox or live
    'client_id' => env('PAYPAL_CLIENT_ID', ''),
    'client_secret' => env('PAYPAL_CLIENT_SECRET', ''),
    'webhook_id' => env('PAYPAL_WEBHOOK_ID', ''),
    'currency' => env('PAYPAL_CURRENCY', 'USD'),
    'endpoints' => [
        'sandbox' => 'https://api-m.sandbox.paypal.com',
        'live' => 'https://api-m.paypal.com',
    ],
];

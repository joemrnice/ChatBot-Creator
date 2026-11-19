<?php
declare(strict_types=1);

return [
    'default_categories' => [
        'business' => 'Business & Sales',
        'support' => 'Customer Support',
        'personal' => 'Personal Assistant',
        'education' => 'Education & Tutoring',
        'real_estate' => 'Real Estate',
        'healthcare' => 'Healthcare Info',
        'ecommerce' => 'E-Commerce & Orders',
        'custom' => 'Custom Purpose',
    ],
    'default_avatar' => 'default-bot.svg',
    'max_upload_size' => 10 * 1024 * 1024, // 10MB
    'allowed_import_extensions' => ['json', 'csv'],
    'ai' => [
        'default_provider' => env('AI_PROVIDER', 'fallback'),
        'api_key' => env('AI_API_KEY', ''),
        'model' => env('AI_MODEL', 'gpt-4o-mini'),
        'endpoint' => env('AI_ENDPOINT', 'https://api.openai.com/v1/chat/completions'),
        'timeout' => 15,
        'temperature' => 0.7,
        'max_tokens' => 800,
    ],
    'rate_limits' => [
        'chat_messages_per_minute' => 30,
        'imports_per_hour' => 20,
    ],
];

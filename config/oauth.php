<?php
declare(strict_types=1);

return [
    'github' => [
        'enabled' => !empty(env('GITHUB_CLIENT_ID')),
        'client_id' => env('GITHUB_CLIENT_ID', ''),
        'client_secret' => env('GITHUB_CLIENT_SECRET', ''),
        'redirect_uri' => env('GITHUB_REDIRECT_URI', ''),
        'authorize_url' => 'https://github.com/login/oauth/authorize',
        'token_url' => 'https://github.com/login/oauth/access_token',
        'user_url' => 'https://api.github.com/user',
        'emails_url' => 'https://api.github.com/user/emails',
        'scope' => 'read:user user:email',
    ],
    'google' => [
        'enabled' => !empty(env('GOOGLE_CLIENT_ID')),
        'client_id' => env('GOOGLE_CLIENT_ID', ''),
        'client_secret' => env('GOOGLE_CLIENT_SECRET', ''),
        'redirect_uri' => env('GOOGLE_REDIRECT_URI', ''),
        'authorize_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
        'token_url' => 'https://oauth2.googleapis.com/token',
        'user_url' => 'https://www.googleapis.com/oauth2/v3/userinfo',
        'scope' => 'openid email profile',
    ],
    'facebook' => [
        'enabled' => !empty(env('FACEBOOK_CLIENT_ID')),
        'client_id' => env('FACEBOOK_CLIENT_ID', ''),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET', ''),
        'redirect_uri' => env('FACEBOOK_REDIRECT_URI', ''),
        'authorize_url' => 'https://www.facebook.com/v19.0/dialog/oauth',
        'token_url' => 'https://graph.facebook.com/v19.0/oauth/access_token',
        'user_url' => 'https://graph.facebook.com/me?fields=id,name,email,picture',
        'scope' => 'email,public_profile',
    ],
];

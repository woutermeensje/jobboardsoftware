<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'stripe' => [
        'prices' => [
            'starter' => env('STRIPE_PRICE_FREE', env('STRIPE_PRICE_STARTER')),
            'growth' => env('STRIPE_PRICE_STANDARD', env('STRIPE_PRICE_GROWTH')),
            'enterprise' => env('STRIPE_PRICE_PRO', env('STRIPE_PRICE_ENTERPRISE', env('STRIPE_PRICE_PLATFORM'))),
        ],
    ],

    'laravel_cloud' => [
        'base_url' => env('LARAVEL_CLOUD_API_URL', 'https://cloud.laravel.com/api'),
        'token' => env('LARAVEL_CLOUD_API_TOKEN'),
        'environment_id' => env('LARAVEL_CLOUD_ENVIRONMENT_ID'),
        'domain_sync' => env('LARAVEL_CLOUD_DOMAIN_SYNC', true),
        'domain_defaults' => [
            'www_redirect' => env('LARAVEL_CLOUD_DOMAIN_WWW_REDIRECT'),
            'wildcard_enabled' => (bool) env('LARAVEL_CLOUD_DOMAIN_WILDCARD_ENABLED', false),
            'allow_downtime' => (bool) env('LARAVEL_CLOUD_DOMAIN_ALLOW_DOWNTIME', true),
            'cloudflare_strategy' => env('LARAVEL_CLOUD_DOMAIN_CLOUDFLARE_STRATEGY', 'none'),
            'verification_method' => env('LARAVEL_CLOUD_DOMAIN_VERIFICATION_METHOD', 'real_time'),
        ],
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];

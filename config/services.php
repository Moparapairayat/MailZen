<?php

return [
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],
    'update_server' => [
        'base_url' => 'https://api.getmailpurse.com',
        'product_id' => 2,
        'product_secret' => 'fv870t4vmO9HY7bQzclolcsQ3zul1CkC',
        'product_name' => 'MailZen',
        'license' => [
            'activate' => 'https://api.getmailpurse.com/wp-json/v1/license/activate',
            'check' => 'https://api.getmailpurse.com/wp-json/v1/license/check',
            'verify' => 'https://api.getmailpurse.com/wp-json/v1/license/verify',
            'deactivate' => 'https://api.getmailpurse.com/wp-json/v1/license/deactivate',
        ],
    ],
    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],
    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'clock_skew_check' => [
        'url' => env('CLOCK_SKEW_CHECK_URL', 'https://www.google.com'),
        'threshold_seconds' => (int) env('CLOCK_SKEW_THRESHOLD_SECONDS', 120),
        'timeout_seconds' => (int) env('CLOCK_SKEW_TIMEOUT_SECONDS', 5),
        'cache_seconds' => (int) env('CLOCK_SKEW_CACHE_SECONDS', 60),
        'auto_pause_campaigns' => (bool) env('CLOCK_SKEW_AUTO_PAUSE_CAMPAIGNS', true),
        'auto_pause_seconds' => (int) env('CLOCK_SKEW_AUTO_PAUSE_SECONDS', 300),
    ],
    'sendgrid' => [
        'api_key' => env('SENDGRID_API_KEY'),
    ],
    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],
    'ipinfo' => [
        'token' => env('IPINFO_TOKEN'),
    ],
    'unlayer' => [
        'api_key' => env('UNLAYER_API_KEY'),
        'project_id' => env('UNLAYER_PROJECT_ID'),
    ],
];

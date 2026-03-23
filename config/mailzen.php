<?php

return [
    'version' => '1.7.2',
    'skip_install_wizard' => (bool) env('SKIP_INSTALL_WIZARD', false),
    'tracking_domains' => [
        'cname_target' => env('TRACKING_DOMAIN_CNAME_TARGET'),
    ],
    'fonts' => [
        'supported_google_families' => [
            'Inter',
            'Roboto',
            'Open Sans',
            'Lato',
            'Sora',
            'Montserrat',
            'Poppins',
            'Nunito',
            'Raleway',
        ],
    ],
    'clock_skew_check' => [
        'url' => env('CLOCK_SKEW_CHECK_URL', 'https://www.google.com'),
        'threshold_seconds' => (int) env('CLOCK_SKEW_THRESHOLD_SECONDS', 180),
        'timeout_seconds' => (int) env('CLOCK_SKEW_TIMEOUT_SECONDS', 5),
        'auto_queue_restart' => (bool) env('CLOCK_SKEW_AUTO_QUEUE_RESTART', false),
    ],
    'email_validation' => [
        'snapvalid_429_delay_seconds' => (int) env('SNAPVALID_429_DELAY_SECONDS', 10),
    ],
    'reply_tracking' => [
        'enabled' => (bool) env('REPLY_TRACKING_ENABLED', false),
        'reply_domain' => env('REPLY_TRACKING_REPLY_DOMAIN'),
        'imap' => [
            'hostname' => env('REPLY_TRACKING_IMAP_HOST'),
            'port' => (int) env('REPLY_TRACKING_IMAP_PORT', 993),
            'encryption' => env('REPLY_TRACKING_IMAP_ENCRYPTION', 'ssl'),
            'username' => env('REPLY_TRACKING_IMAP_USERNAME'),
            'password' => env('REPLY_TRACKING_IMAP_PASSWORD'),
            'mailbox' => env('REPLY_TRACKING_IMAP_MAILBOX', 'INBOX'),
            'delete_after_processing' => (bool) env('REPLY_TRACKING_IMAP_DELETE_AFTER_PROCESSING', false),
            'max_emails_per_batch' => (int) env('REPLY_TRACKING_IMAP_MAX_EMAILS_PER_BATCH', 50),
        ],
    ],
    'spam_scoring' => [
        'blocking_threshold' => (int) env('SPAM_BLOCKING_THRESHOLD', 15),
        'enabled_by_default' => (bool) env('SPAM_SCORING_ENABLED_BY_DEFAULT', false),
    ],
];

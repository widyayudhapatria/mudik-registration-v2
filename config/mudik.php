<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Form Link Configuration
    |--------------------------------------------------------------------------
    */
    'form_link_expiry_days' => env('FORM_LINK_EXPIRY_DAYS', 3),
    'form_link_max_resend' => env('FORM_LINK_MAX_RESEND', 5),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    */
    'rate_limit' => [
        'email_submission' => env('RATE_LIMIT_EMAIL_SUBMISSION', 5),
        'window_minutes' => env('RATE_LIMIT_WINDOW_MINUTES', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | QR Code Configuration
    |--------------------------------------------------------------------------
    */
    'qr_code' => [
        'size' => env('QR_CODE_SIZE', 300),
        'margin' => env('QR_CODE_MARGIN', 2),
        'format' => env('QR_CODE_FORMAT', 'png'),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Configuration
    |--------------------------------------------------------------------------
    */
    'upload' => [
        'max_file_size' => env('MAX_FILE_SIZE', 2048), // in KB
        'allowed_types' => explode(',', env('ALLOWED_FILE_TYPES', 'jpg,jpeg,png,pdf')),
        'kk_document_path' => 'uploads/kk_documents',
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Configuration
    |--------------------------------------------------------------------------
    */
    'email' => [
        'from_address' => env('MAIL_FROM_ADDRESS', 'noreply@mudiklebaran.id'),
        'from_name' => env('MAIL_FROM_NAME', 'Mudik Lebaran 2026'),
        'max_retry' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pusher Configuration
    |--------------------------------------------------------------------------
    */
    'pusher' => [
        'enabled' => env('BROADCAST_DRIVER', 'pusher') === 'pusher',
        'scan_channel' => 'scan-monitoring',
        'scan_event' => 'ScanPerformed',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Quota Configuration
    |--------------------------------------------------------------------------
    */
    'default_daily_quota' => env('DEFAULT_DAILY_QUOTA', 100),
];
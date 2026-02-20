<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Form Link Configuration
    |--------------------------------------------------------------------------
    */
    'form_link_expiry_days' => (int)env('FORM_LINK_EXPIRY_DAYS', 3),
    'form_link_max_resend' => (int) env('FORM_LINK_MAX_RESEND', 5),

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
        'from_name' => env('MAIL_FROM_NAME', 'Mudik Bersama Kabupaten Banten 2026'),
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

    /*
    |--------------------------------------------------------------------------
    | Website Information
    |--------------------------------------------------------------------------
    */
    'website' => [
        'name' => env('APP_NAME', 'Mudik Bersama Kabupaten Banten 2026'),
        'tagline' => env('APP_TAGLINE', 'Platform Registrasi Mudik Bersama Kabupaten Banten 2026'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Support Information
    |--------------------------------------------------------------------------
    */
    'support' => [
        'email' => env('SUPPORT_EMAIL', 'support@mudiklebaran.id'),
        'phone' => env('SUPPORT_PHONE', '+62-XXX-XXXX-XXXX'),
        'schedule' => env('SUPPORT_SCHEDULE', 'Senin-Jumat: 08:00 - 17:00 WIB'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Business Rules & Schedule
    |--------------------------------------------------------------------------
    */
    'schedule' => [
        'qr_code_valid_from' => env('QR_CODE_VALID_FROM', '2026-03-01 10:00:00'),
        'qr_code_valid_until' => env('QR_CODE_VALID_UNTIL', '2026-03-01 23:00:00'),
        'ticket_exchange' => env('TICKET_EXCHANGE_SCHEDULE', 'Tanggal 1-5 April 2026'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Departure Days Configuration
    |--------------------------------------------------------------------------
    */
    'departure_date' => '2026-05-30',
    'departure_time' => '08:00',
];

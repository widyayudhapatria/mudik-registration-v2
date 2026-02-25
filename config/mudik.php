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
        'from_name' => env('MAIL_FROM_NAME', 'Mudik Bersama Kabupaten Tangerang 2026'),
        'max_retry' => 3,

        // Queue rate limit (emails per hour)
        // Set buffer 10% from SMTP limit to prevent hitting limit
        // Example: SMTP limit 100/hour -> set to 90
        'queue_rate_limit_per_hour' => env('EMAIL_QUEUE_RATE_LIMIT_PER_HOUR', 90),
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
        'name' => env('APP_NAME', 'Mudik Gratis Bersama Pemerintah Kabupaten Tangerang 2026'),
        'tagline' => env('APP_TAGLINE', 'Platform Registrasi Mudik Bersama Pemerintah Kabupaten Tangerang 2026 | MUDIK AMAN BERBAGI HARAPAN'),
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
    | Departure Days Configuration & Ticketing & Schedule
    |--------------------------------------------------------------------------
    */
    'schedule' => [
        'departure_date' => env('DEPARTURE_DATE', '2026-03-18'),
        'departure_time' => env('DEPARTURE_TIME', '08:00'),
        'qr_code_valid_from' => env('QR_CODE_VALID_FROM', '2026-03-18 00:00:00'),
        'qr_code_valid_until' => env('QR_CODE_VALID_UNTIL', '2026-03-18 23:59:59'),
        'ticket_exchange_date' => env('TICKET_EXCHANGE_DATE', '18 Maret 2026'),
        'ticket_exchange_time' => env('TICKET_EXCHANGE_TIME', '08:00 - 12:00 WIB'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Location Configuration
    |--------------------------------------------------------------------------
    */
    'locations' => [
        'departure_location' => env('DEPARTURE_LOCATION', 'Kantor Pusat Pemerintahan Kabupaten Tangerang'),
        'ticket_exchange_location' => env('TICKET_EXCHANGE_LOCATION', 'Lapangan Pusat Pemerintahan Kabupaten Tangerang - (GSG Tigaraksa)'),
        'address_location' => env('ADDRESS_LOCATION', 'Jl. H. Somawinata No.1, Kadu Agung, Kec. Tigaraksa, Kabupaten Tangerang, Banten'),
    ],


    /*
    |--------------------------------------------------------------------------
    | Registration Period Configuration
    |--------------------------------------------------------------------------
    */
    'registration' => [
        'start_date' => env('REGISTRATION_START_DATE', '2025-02-22 00:00:00'),
        'end_date'   => env('REGISTRATION_END_DATE', '2025-03-17 23:59:59'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Counter Seat Configuration
    |--------------------------------------------------------------------------
    */
    'counter_seat' => [
        'bus_max_seats' => env('BUS_MAX_SEATS', 50),
    ],
];

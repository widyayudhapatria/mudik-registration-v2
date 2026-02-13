<?php

return [
    /*
    |--------------------------------------------------------------------------
    | QR Code Image Backend
    |--------------------------------------------------------------------------
    |
    | Supported: "imagick", "gd", "svg"
    | 
    | Force GD for Windows/XAMPP compatibility
    |
    */
    'image_backend' => env('QRCODE_IMAGE_BACKEND', 'gd'),
];
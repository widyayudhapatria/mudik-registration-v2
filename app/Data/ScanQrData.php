<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class ScanQrData extends Data
{
    public function __construct(
        #[Required, StringType, Min(16)]
        public string $token_qr,
    ) {}
}

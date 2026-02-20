<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class RejectRegistrationData extends Data
{
    public function __construct(
        #[Required, StringType, Max(500)]
        public string $rejection_reason,
        
        #[StringType, Max(1000)]
        public string|Optional|null $admin_notes = null,
    ) {}
}
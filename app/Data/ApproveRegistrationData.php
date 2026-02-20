<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class ApproveRegistrationData extends Data
{
    public function __construct(
        #[StringType, Max(1000)]
        public string|Optional|null $admin_notes = null,
    ) {}
}
<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class SubmitEmailData extends Data
{
    public function __construct(
        #[Required, StringType, Email]
        public string $email,
    ) {}
}
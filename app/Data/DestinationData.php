<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Integer;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class DestinationData extends Data
{
    public function __construct(
        #[Required, StringType, Min(3), Max(100)]
        public string $name,

        #[Required, StringType, Min(2), Max(20)]
        public string $code,

        #[Required, Integer, Min(1)]
        public int $total_quota,

        #[StringType]
        public string|Optional $description = '',

        public bool $is_active = true,

        #[Integer, Min(0)]
        public int $display_order = 0,
    ) {}
}

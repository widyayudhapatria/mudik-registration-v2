<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Integer;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class EditQuotaData extends Data
{
    public function __construct(
        #[Required, Integer, Min(1)]
        public int $id,

        #[Required, Integer, Min(0)]
        public int $quota_daily,
    ) {}
}

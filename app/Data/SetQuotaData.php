<?php

namespace App\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\Integer;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class SetQuotaData extends Data
{
    public function __construct(
        #[Required, Date]
        public Carbon|string $date,
        
        #[Required, Integer, Min(0)]
        public int $quota,
    ) {
        if (is_string($this->date)) {
            $this->date = Carbon::parse($this->date);
        }
    }
}
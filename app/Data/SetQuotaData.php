<?php

namespace App\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Attributes\Validation\Integer;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\AfterOrEqual;
use Spatie\LaravelData\Data;

class SetQuotaData extends Data
{
    public function __construct(
        #[Required, Integer, Min(1)]
        public int $destination_id,

        #[Required]
        #[Date]
        #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
        public Carbon|string $date,

        #[Required, Integer, Min(0)]
        public int $quota_daily,
    ) {
        if (is_string($this->date)) {
            $this->date = Carbon::parse($this->date);
        }

        // Prevent backdating
        if ($this->date->isBefore(Carbon::today())) {
            throw new \InvalidArgumentException(
                'Tanggal kuota tidak boleh sebelum hari ini. Silakan pilih tanggal hari ini atau lebih baru.'
            );
        }
    }
}

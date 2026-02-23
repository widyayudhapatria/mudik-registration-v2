<?php

namespace App\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Size;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class ParticipantData extends Data
{
    public function __construct(
        #[Required, StringType, Min(3), Max(255)]
        public string $full_name,

        #[Required, StringType, Size(16), Regex('/^\d{16}$/')]
        public string $nik_kia,

        #[Required, StringType]
        public string $birth_date,
    ) {}

    public function toModelArray(): array
    {
        return [
            'full_name' => $this->full_name,
            'nik_kia' => $this->nik_kia,
            'birth_date' => Carbon::parse($this->birth_date)->format('Y-m-d'),
        ];
    }

    public function isUnderFourYears(): bool
    {
        return Carbon::parse($this->birth_date)->age < 4;
    }
}

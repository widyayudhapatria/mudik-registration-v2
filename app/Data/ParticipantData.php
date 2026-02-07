<?php

namespace App\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Size;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class ParticipantData extends Data
{
    public function __construct(
        #[Required, StringType, Max(255)]
        public string $full_name,
        
        #[Required, StringType, Size(16), Min(16)]
        public string $nik_kia,
        
        #[Required, StringType]
        public string $birth_date,
    ) {
        if (is_string($this->birth_date)) {
            $this->birth_date = Carbon::parse($this->birth_date);
        }
    }

    public function toModelArray(): array
    {
        return [
            'full_name' => $this->full_name,
            'nik_kia' => $this->nik_kia,
            'birth_date' => $this->birth_date instanceof Carbon 
                ? $this->birth_date->format('Y-m-d') 
                : $this->birth_date,
        ];
    }
}
<?php

namespace App\Data;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\File;
use Spatie\LaravelData\Attributes\Validation\Integer;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Size;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class RegistrationData extends Data
{
    public function __construct(
        #[Required, StringType, Max(255)]
        public string $representative_name,
        
        #[Required, StringType, Size(16), Min(16)]
        public string $representative_nik,
        
        #[Required, StringType]
        public string $representative_birth_date,
        
        #[Required, Integer, Min(1), Max(6)]
        public int $family_count,
        
        #[Required, StringType, Size(16), Min(16)]
        public string $kk_number,
        
        #[Required, File, Max(2048)] // Max 2MB
        public UploadedFile $kk_document,
        
        #[Required, BooleanType]
        public bool $has_child_under_4 = false,
        
        #[Required, ArrayType]
        /** @var ParticipantData[] */
        public DataCollection $participants,
    ) {
        if (is_string($this->representative_birth_date)) {
            $this->representative_birth_date = Carbon::parse($this->representative_birth_date);
        }
    }

    public function toModelArray(): array
    {
        return [
            'representative_name' => $this->representative_name,
            'representative_nik' => $this->representative_nik,
            'representative_birth_date' => $this->representative_birth_date instanceof Carbon 
                ? $this->representative_birth_date->format('Y-m-d') 
                : $this->representative_birth_date,
            'family_count' => $this->family_count,
            'kk_number' => $this->kk_number,
            'has_child_under_4' => $this->has_child_under_4,
        ];
    }

    public function getParticipantsArray(): array
    {
        return $this->participants->toCollection()
            ->map(fn(ParticipantData $participant) => $participant->toModelArray())
            ->toArray();
    }
}
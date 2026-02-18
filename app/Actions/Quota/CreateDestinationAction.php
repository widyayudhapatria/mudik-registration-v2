<?php

namespace App\Actions\Quota;

use App\Data\DestinationData;
use App\Enums\ErrorCode;
use App\Exceptions\MudikException;
use App\Models\Destination;
use App\Models\GlobalQuotaConfig;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateDestinationAction
{
    use AsAction;

    /**
     * Create a new destination with quota allocation.
     *
     * @param DestinationData $data
     * @return Destination
     * @throws MudikException
     */
    public function handle(DestinationData $data): Destination
    {
        return DB::transaction(function () use ($data) {
            // Get current year global quota config
            $globalConfig = GlobalQuotaConfig::getCurrentYear();

            if (!$globalConfig) {
                throw new MudikException(
                    ErrorCode::ServerError,
                    'Konfigurasi kuota global belum diatur.'
                );
            }

            // Calculate current allocated quota
            $currentAllocated = Destination::sum('total_quota');

            // Validate: new allocation should not exceed global quota
            if ($currentAllocated + $data->total_quota > $globalConfig->total_quota) {
                throw new MudikException(
                    ErrorCode::QuotaExceededGlobal,
                    "Total alokasi kuota ({$currentAllocated} + {$data->total_quota}) " .
                        "melebihi kuota global ({$globalConfig->total_quota}). " .
                        "Sisa kuota yang bisa dialokasikan: " . ($globalConfig->total_quota - $currentAllocated)
                );
            }

            // Create destination
            $destination = Destination::create([
                'name' => $data->name,
                'code' => strtoupper($data->code),
                'total_quota' => $data->total_quota,
                'used_quota' => 0,
                'is_active' => $data->is_active,
                'display_order' => $data->display_order,
                'description' => $data->description ?? '',
            ]);

            return $destination;
        });
    }
}

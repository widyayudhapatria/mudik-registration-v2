<?php

namespace App\Actions\Quota;

use App\Data\DestinationData;
use App\Enums\ErrorCode;
use App\Exceptions\MudikException;
use App\Models\Destination;
use App\Models\GlobalQuotaConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateDestinationAction
{
    use AsAction;

    public function handle(Destination $destination, DestinationData $data): Destination
    {
        return DB::transaction(function () use ($destination, $data) {
            // Lock destination for update
            $destination = Destination::lockForUpdate()->find($destination->id);

            // Get global quota config
            $globalConfig = GlobalQuotaConfig::getCurrentYear();

            // Calculate total allocated (excluding current destination)
            $totalAllocated = Destination::where('id', '!=', $destination->id)
                ->sum('total_quota');

            // Validate: total allocation should not exceed global quota
            if (($totalAllocated + $data->total_quota) > $globalConfig->total_quota) {
                throw new MudikException(
                    ErrorCode::QuotaExceededGlobal,
                    sprintf(
                        'Total alokasi quota (%d + %d = %d) melebihi quota global (%d). Sisa yang bisa dialokasi: %d',
                        $totalAllocated,
                        $data->total_quota,
                        $totalAllocated + $data->total_quota,
                        $globalConfig->total_quota,
                        $globalConfig->total_quota - $totalAllocated
                    )
                );
            }

            // Validate: new total_quota should not be less than already used
            if ($data->total_quota < $destination->used_quota) {
                throw new MudikException(
                    ErrorCode::QuotaExceededDestination,
                    sprintf(
                        'Total quota baru (%d) tidak boleh lebih kecil dari quota yang sudah terpakai (%d)',
                        $data->total_quota,
                        $destination->used_quota
                    )
                );
            }

            // Check if reducing quota below scheduled daily quotas
            $totalDailyScheduled = $destination->dailyQuotas()
                ->whereDate('date', '>=', today())
                ->sum('quota_daily');

            // Hard validation: throw exception if scheduled quotas exceed new total
            if ($totalDailyScheduled > $data->total_quota) {
                throw new MudikException(
                    ErrorCode::QuotaExceededDestination,
                    sprintf(
                        'Tidak dapat mengurangi quota. Total daily quota sudah terjadwal (%d) melebihi total quota baru (%d). Hapus atau reduce daily quotas terlebih dahulu sebelum edit.',
                        $totalDailyScheduled,
                        $data->total_quota
                    )
                );
            }

            // Update destination
            $destination->update([
                'name' => $data->name,
                'code' => $data->code,
                'total_quota' => $data->total_quota,
                'description' => $data->description,
                'is_active' => $data->is_active,
                'display_order' => $data->display_order,
            ]);

            Log::info('Destination updated', [
                'destination_id' => $destination->id,
                'name' => $data->name,
                'total_quota' => $data->total_quota,
                'used_quota' => $destination->used_quota,
                'remaining_quota' => $destination->remaining_quota,
            ]);

            return $destination->fresh();
        });
    }
}

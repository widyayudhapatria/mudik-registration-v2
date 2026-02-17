<?php

namespace App\Actions\Quota;

use App\Data\EditQuotaData;
use App\Enums\ErrorCode;
use App\Exceptions\MudikException;
use App\Models\DailyQuota;
use App\Models\Destination;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class EditQuotaAction
{
    use AsAction;

    public function handle(EditQuotaData $data): DailyQuota
    {
        return DB::transaction(function () use ($data) {
            // Lock quota for update to prevent race condition
            $quota = DailyQuota::lockForUpdate()->findOrFail($data->id);

            // Get immutable fields from database
            $destination_id = $quota->destination_id;
            $dateString = $quota->date instanceof \Carbon\Carbon
                ? $quota->date->toDateString()
                : (string)$quota->date;

            // Lock destination for update
            $destination = Destination::lockForUpdate()->findOrFail($destination_id);

            // Check if destination is active
            if (!$destination->is_active) {
                throw new MudikException(
                    ErrorCode::ServerError,
                    "Destination {$destination->name} tidak aktif"
                );
            }

            // Validate: daily quota should not exceed destination total quota
            // Calculate total daily scheduled (excluding current quota)
            $today = Carbon::today()->toDateString();
            $totalNotPassedQuota = DailyQuota::where('destination_id', $destination_id)
                ->where('id', '!=', $quota->id)
                ->where('date', '>=', $today)
                ->sum('quota_daily');

            $newTotal = $totalNotPassedQuota + $data->quota_daily;

            // remining destination = total quota - used quota
            $remainingDestinationQuota = $destination->total_quota - $destination->used_quota;

            if ($newTotal > $remainingDestinationQuota) {
                $remaining = $remainingDestinationQuota - $totalNotPassedQuota;
                throw new MudikException(
                    ErrorCode::QuotaExceededDestination,
                    sprintf(
                        'Total kuota hari ini dan ke depan (%d + %d = %d) melebihi sisa kuota destinasi %s (%d). Hanya sisa: %d',
                        $totalNotPassedQuota,
                        $data->quota_daily,
                        $newTotal,
                        $destination->name,
                        $remainingDestinationQuota,
                        $remaining
                    )
                );
            }

            // Update quota - preserve 'used_daily' value
            $quota->updateQuota($data->quota_daily);

            // Clear cache
            Cache::forget('destinations:available:' . $dateString);

            Log::info('Quota updated successfully', [
                'quota_id' => $quota->id,
                'destination_id' => $destination_id,
                'destination_name' => $destination->name,
                'date' => $dateString,
                'old_quota_daily' => $quota->getOriginal('quota_daily'),
                'new_quota_daily' => $data->quota_daily,
                'used_daily' => $quota->used_daily,
            ]);

            return $quota->fresh();
        });
    }
}

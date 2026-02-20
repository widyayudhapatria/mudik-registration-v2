<?php

namespace App\Actions\Quota;

use App\Data\SetQuotaData;
use App\Enums\ErrorCode;
use App\Exceptions\MudikException;
use App\Models\DailyQuota;
use App\Models\Destination;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class SetQuotaAction
{
    use AsAction;

    public function handle(SetQuotaData $data): DailyQuota
    {
        return DB::transaction(function () use ($data) {
            $dateString = $data->date->format('Y-m-d');

            // Lock destination for update
            $destination = Destination::lockForUpdate()->findOrFail($data->destination_id);

            // Check if destination is active
            if (!$destination->is_active) {
                throw new MudikException(
                    ErrorCode::ServerError,
                    "Destination {$destination->name} tidak aktif"
                );
            }

            // Validate: daily quota should not exceed destination total quota
            // Calculate total daily quota from today onwards (exclude past dates)
            $today = Carbon::today()->toDateString();
            $totalNotPassedQuota = DailyQuota::where('destination_id', $data->destination_id)
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

            // Find or create daily quota
            $quota = DailyQuota::where('destination_id', $data->destination_id)
                ->where('date', $dateString)
                ->first();

            if ($quota) {
                // Prevent accidental update via create path - require explicit Edit flow
                throw new \Exception('Kuota untuk tanggal ini sudah ada. Silahkan gunakan tombol "Edit" pada listing untuk memperbarui kuota.');
            } else {
                // Create new quota
                $quota = DailyQuota::create([
                    'destination_id' => $data->destination_id,
                    'date' => $dateString,
                    'quota_daily' => $data->quota_daily,
                    'used_daily' => 0,
                    'remaining_daily' => $data->quota_daily,
                ]);
                $action = 'created';
            }

            // Clear cache
            Cache::forget('destinations:available:' . $dateString);

            Log::info('Quota set successfully', [
                'destination_id' => $data->destination_id,
                'destination_name' => $destination->name,
                'date' => $dateString,
                'quota_daily' => $data->quota_daily,
                'action' => $action,
            ]);

            return $quota->fresh();
        });
    }
}

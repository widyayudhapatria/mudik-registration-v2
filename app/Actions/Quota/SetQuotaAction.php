<?php

namespace App\Actions\Quota;

use App\Data\SetQuotaData;
use App\Models\DailyQuota;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class SetQuotaAction
{
    use AsAction;

    public function handle(SetQuotaData $data): DailyQuota
    {
        DB::beginTransaction();

        try {
            $dateString = $data->date->format('Y-m-d');

            // Check if quota exists for this date
            $quota = DailyQuota::where('date', $dateString)
                ->lockForUpdate()
                ->first();

            if ($quota) {
                // Update existing quota
                $quota->updateQuota($data->quota);
            } else {
                // Create new quota
                $quota = DailyQuota::create([
                    'date' => $dateString,
                    'quota' => $data->quota,
                    'used' => 0,
                    'remaining' => $data->quota,
                ]);
            }

            // Clear cache
            Cache::forget('quota:' . $dateString);

            DB::commit();

            Log::info('Quota set successfully', [
                'date' => $dateString,
                'quota' => $data->quota,
            ]);

            return $quota;

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to set quota', [
                'date' => $data->date->format('Y-m-d'),
                'quota' => $data->quota,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
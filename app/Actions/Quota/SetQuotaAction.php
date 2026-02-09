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
        return DB::transaction(function () use ($data) {
            $dateString = $data->date->format('Y-m-d');

            $quota = DailyQuota::whereDate('date', $data->date)->first();

            if ($quota) {
                // Update existing quota - preserve 'used' value
                $quota->updateQuota($data->quota);
                $action = 'updated';
            } else {
                // Create new quota
                $quota = DailyQuota::create([
                    'date' => $dateString,
                    'quota' => $data->quota,
                    'used' => 0,
                    'remaining' => $data->quota,
                ]);
                $action = 'created';
            }

            // Clear cache
            Cache::forget('quota:' . $dateString);

            Log::info('Quota set successfully', [
                'date' => $dateString,
                'quota' => $data->quota,
                'action' => $action,
            ]);

            return $quota->fresh();
        });
    }
}
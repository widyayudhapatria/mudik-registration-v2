<?php

namespace App\Actions\Quota;

use App\Models\DailyQuota;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;

class GetQuotaAction
{
    use AsAction;

    public function handle(?Carbon $date = null): ?DailyQuota
    {
        $date = $date ?? Carbon::today();
        $dateString = $date->format('Y-m-d');
        $cacheKey = 'quota:' . $dateString;

        // Cache until end of day
        $ttl = $date->copy()->endOfDay();

        return Cache::remember($cacheKey, $ttl, function () use ($dateString) {
            return DailyQuota::where('date', $dateString)->first();
        });
    }

    public function getTodayQuota(): ?DailyQuota
    {
        return $this->handle(Carbon::today());
    }

    public function checkAvailability(?Carbon $date = null): bool
    {
        $quota = $this->handle($date);
        
        return $quota && $quota->hasAvailableQuota();
    }
}
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

        $currentTime = Carbon::now();
        $endOfDay = $date->copy()->endOfDay();

        // Calculate TTL in seconds until end of day
        $ttl = $currentTime->diffInSeconds($endOfDay, false);

        if ($ttl <= 0) {
            $ttl = 60; // Fallback to 1 minute if calculation fails
        }

        return Cache::remember($cacheKey, $ttl, function () use ($date) {
            return DailyQuota::where('date', $date->format('Y-m-d'))->first();
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

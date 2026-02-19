<?php

namespace App\Actions\Quota;

use App\Enums\ErrorCode;
use App\Exceptions\MudikException;
use App\Models\Destination;
use Carbon\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class GetAvailableDestinationsAction
{
    use AsAction;

    /**
     * Get available destinations with today's daily quota.
     *
     * @param Carbon|null $date
     * @return array
     * @throws MudikException
     */
    public function handle(?Carbon $date = null): array
    {
        $date = $date ?? Carbon::today();

        // Get active destinations with their daily quotas for the specified date
        $destinations = Destination::where('is_active', true)
            ->ordered()
            ->with(['dailyQuotas' => function ($query) use ($date) {
                $query->whereDate('date', $date);
            }])
            ->get();

        $hasAnyQuota = false;
        $result = [];

        foreach ($destinations as $destination) {
            $dailyQuota = $destination->dailyQuotas->first();

            // Skip if daily quota not set for this destination
            if (!$dailyQuota) {
                continue;
            }

            $hasAnyQuota = true;

            $result[] = [
                'id'          => $destination->id,
                'name'        => $destination->name,
                'code'        => $destination->code,
                'daily_quota' => $dailyQuota->quota_daily,
                'remaining'   => $dailyQuota->remaining_daily,
                'is_available' => $dailyQuota->remaining_daily > 0, 
            ];
        }

        // If no destinations have daily quota set
        if (!$hasAnyQuota) {
            throw new MudikException(
                ErrorCode::DailyQuotaNotSet,
                'Kuota harian untuk hari ini belum diset untuk destinasi apapun. Silakan coba lagi nanti.'
            );
        }

        return $result;
    }
}
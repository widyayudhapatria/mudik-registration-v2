<?php

namespace Database\Seeders;

use App\Models\DailyQuota;
use App\Models\Destination;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DailyQuotaSeeder extends Seeder
{
    /**
     * Seed daily quotas for existing destinations.
     *
     * NOTE: This seeder assumes destinations already exist.
     * For complete setup (destinations + daily quotas), use DestinationQuotaSeeder instead.
     */
    public function run(): void
    {
        $destinations = Destination::where('is_active', true)->get();

        if ($destinations->isEmpty()) {
            $this->command->warn('⚠️  No active destinations found. Please run DestinationQuotaSeeder first.');
            return;
        }

        // Fixed period: 15 Feb - 24 Feb 2026
        $quotaPerDay = 2;
        $startDate = Carbon::create(2026, 2, 15);
        $endDate = Carbon::create(2026, 2, 24);

        $totalCreated = 0;
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            foreach ($destinations as $destination) {
                // Check if quota already exists
                $exists = DailyQuota::where('destination_id', $destination->id)
                    ->where('date', $currentDate->format('Y-m-d'))
                    ->exists();

                if (!$exists) {
                    DailyQuota::create([
                        'destination_id' => $destination->id,
                        'date' => $currentDate->format('Y-m-d'),
                        'quota_daily' => $quotaPerDay,
                        'used_daily' => 0,
                        'remaining_daily' => $quotaPerDay,
                    ]);

                    $totalCreated++;
                }
            }

            $currentDate->addDay();
        }

        $this->command->info("✅ Daily quotas created: {$totalCreated} records for {$destinations->count()} destinations (15 Feb - 24 Feb 2026, {$quotaPerDay} per day)");
    }
}

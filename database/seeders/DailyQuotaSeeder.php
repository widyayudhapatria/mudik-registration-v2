<?php

namespace Database\Seeders;

use App\Models\DailyQuota;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DailyQuotaSeeder extends Seeder
{
    public function run(): void
    {
        $defaultQuota = config('mudik.default_daily_quota', 100);
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(30);

        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            DailyQuota::create([
                'date' => $currentDate->format('Y-m-d'),
                'quota' => $defaultQuota,
                'used' => 0,
                'remaining' => $defaultQuota,
            ]);

            $currentDate->addDay();
        }

        $this->command->info("Daily quotas created for 30 days with {$defaultQuota} quota per day!");
    }
}
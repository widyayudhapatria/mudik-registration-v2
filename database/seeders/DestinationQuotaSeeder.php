<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\DailyQuota;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DestinationQuotaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // 1. Create Global Quota Config
            DB::table('global_quota_config')->insert([
                'year' => 2026,
                'total_quota' => 100,
                'is_active' => true,
                'description' => 'Program Mudik Gratis 2026',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info('✅ Global quota config created: 100 orang');

            // 2. Create Destinations
            $destinations = [
                ['name' => 'Malang', 'code' => 'MLG', 'total_quota' => 20, 'display_order' => 1],
                ['name' => 'Semarang', 'code' => 'SMG', 'total_quota' => 20, 'display_order' => 2],
                ['name' => 'Lampung', 'code' => 'LPG', 'total_quota' => 20, 'display_order' => 3],
                ['name' => 'Surabaya', 'code' => 'SBY', 'total_quota' => 20, 'display_order' => 4],
                ['name' => 'Bali', 'code' => 'DPS', 'total_quota' => 20, 'display_order' => 5],
            ];

            $createdDestinations = [];
            foreach ($destinations as $dest) {
                $destination = Destination::create([
                    'name' => $dest['name'],
                    'code' => $dest['code'],
                    'total_quota' => $dest['total_quota'],
                    'used_quota' => 0,
                    'is_active' => true,
                    'display_order' => $dest['display_order'],
                    'description' => "Tujuan mudik ke {$dest['name']}",
                ]);

                $createdDestinations[] = $destination;
                $this->command->info("✅ Destination created: {$dest['name']} (quota: {$dest['total_quota']})");
            }

            // 3. Create Daily Quotas: 15 Feb - 24 Feb, each destination = 2 per day
            $startDate = Carbon::create(2026, 2, 15);
            $endDate = Carbon::create(2026, 2, 24);
            $dailyQuotaPerDestination = 2;

            $totalDailyQuotas = 0;
            $currentDate = $startDate->copy();

            while ($currentDate->lte($endDate)) {
                foreach ($createdDestinations as $destination) {
                    DailyQuota::create([
                        'destination_id' => $destination->id,
                        'date' => $currentDate->format('Y-m-d'),
                        'quota_daily' => $dailyQuotaPerDestination,
                        'used_daily' => 0,
                        'remaining_daily' => $dailyQuotaPerDestination,
                    ]);

                    $totalDailyQuotas++;
                }

                $currentDate->addDay();
            }

            $this->command->info("✅ Daily quotas created: {$totalDailyQuotas} records (15 Feb - 24 Feb, 2 per destination per day)");

            DB::commit();

            $this->command->info('');
            $this->command->info('===========================================');
            $this->command->info('📊 QUOTA SETUP SUMMARY:');
            $this->command->info('===========================================');
            $this->command->info('Global Quota: 100 orang');
            $this->command->info('Destinations: 5 kota (Malang, Semarang, Lampung, Surabaya, Bali)');
            $this->command->info('Each Destination Quota: 20 orang');
            $this->command->info('Daily Quota Period: 15 Feb - 24 Feb 2026 (10 days)');
            $this->command->info('Daily Quota per Destination: 2 orang/hari');
            $this->command->info('Total Daily Quota Records: ' . $totalDailyQuotas);
            $this->command->info('===========================================');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error seeding destination quota data: ' . $e->getMessage());
            throw $e;
        }
    }
}

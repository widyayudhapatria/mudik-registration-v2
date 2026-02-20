<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\SeatCounter;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SeatCounterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create seat counters for all existing destinations
        $destinations = Destination::all();

        foreach ($destinations as $destination) {
            // Check if counter already exists
            $exists = SeatCounter::where('destination_id', $destination->id)->exists();

            if (!$exists) {
                SeatCounter::create([
                    'destination_id' => $destination->id,
                    'current_bus_number' => 1,
                    'current_seat_number' => 0,
                    'total_seats_allocated' => 0,
                ]);

                $this->command->info("Created seat counter for: {$destination->name}");
            } else {
                $this->command->warn("Seat counter already exists for: {$destination->name}");
            }
        }

        $this->command->info('✅ Seat counter seeding completed!');
    }
}

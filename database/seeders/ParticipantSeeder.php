<?php

namespace Database\Seeders;

use App\Models\Participant;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ParticipantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all registrations (including soft deleted for rejected ones)
        $registrations = Registration::withTrashed()->with('formLink')->get();

        if ($registrations->isEmpty()) {
            $this->command->error('❌ No registrations found. Please run RegistrationSeeder first.');
            return;
        }

        $totalCreated = 0;

        $firstNames = ['Ahmad', 'Budi', 'Citra', 'Dewi', 'Eko', 'Fitriani', 'Gunawan', 'Hendra', 'Indah', 'Joko'];
        $lastNames = ['Santoso', 'Wijaya', 'Kusuma', 'Pratama', 'Susanto', 'Utami', 'Wibowo', 'Setiawan', 'Rahayu', 'Hidayat'];

        foreach ($registrations as $registration) {
            $familyCount = $registration->family_count;

            // Generate participants based on family_count
            for ($i = 0; $i < $familyCount; $i++) {
                $isChild = false;
                $age = rand(5, 60); // Default adult age

                // Randomly make some participants children
                if ($i > 0 && rand(0, 2) === 0) { // 33% chance after first person
                    $isChild = true;
                    $age = rand(0, 17);
                }

                // Ensure at least one child under 4 if has_child_under_4 is true
                if ($i === 1 && $registration->has_child_under_4) {
                    $isChild = true;
                    $age = rand(0, 3);
                }

                $firstName = $firstNames[array_rand($firstNames)];
                $lastName = $lastNames[array_rand($lastNames)];
                $fullName = $firstName . ' ' . $lastName;

                // First participant (index 0) is usually the representative
                if ($i === 0) {
                    $fullName = $registration->representative_name;
                    $nikKia = $registration->representative_nik;
                    $birthDate = $registration->representative_birth_date;
                } else {
                    // Generate NIK/KIA (NIK for 17+, KIA for under 17)
                    if ($age >= 17) {
                        $nikKia = '32' . str_pad(rand(1, 99), 2, '0', STR_PAD_LEFT) . str_pad(rand(1, 999999999999), 12, '0', STR_PAD_LEFT);
                    } else {
                        $nikKia = '32' . str_pad(rand(1, 99), 2, '0', STR_PAD_LEFT) . str_pad(rand(1, 999999999999), 12, '0', STR_PAD_LEFT);
                    }

                    $birthDate = Carbon::now()->subYears($age)->subDays(rand(0, 364));
                }

                $participant = Participant::create([
                    'registration_id' => $registration->id,
                    'full_name' => $fullName,
                    'nik_kia' => $nikKia,
                    'birth_date' => $birthDate->format('Y-m-d'),
                    'is_child_under_4' => $age < 4,
                ]);

                $totalCreated++;
            }

            $status = $registration->trashed() ? 'REJECTED' : strtoupper($registration->formLink->status ?? 'UNKNOWN');
            $this->command->info("✅ Participants created for: {$registration->representative_name} ({$status}) - {$familyCount} people");
        }

        $this->command->info('');
        $this->command->info('===========================================');
        $this->command->info('👥 PARTICIPANT SUMMARY:');
        $this->command->info('===========================================');
        $this->command->info('Total participants: ' . $totalCreated);
        $this->command->info('For registrations: ' . $registrations->count());
        $this->command->info('Average family size: ' . round($totalCreated / $registrations->count(), 1));
        $this->command->info('===========================================');
    }
}

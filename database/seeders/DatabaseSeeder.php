<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,              // Seed admin users
            DestinationQuotaSeeder::class,   // Seed destinations + daily quotas
            FormLinkSeeder::class,           // Seed form links with various statuses
            RegistrationSeeder::class,       // Seed registrations (submitted, approved, rejected)
            ParticipantSeeder::class,        // Seed participants for each registration
            QrCodeSeeder::class,             // Seed QR codes for approved registrations
        ]);
    }
}

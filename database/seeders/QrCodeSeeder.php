<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\QrCode;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class QrCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get approved registrations only
        $approvedRegistrations = Registration::whereHas('formLink', function ($q) {
            $q->where('status', 'approved');
        })->get();

        if ($approvedRegistrations->isEmpty()) {
            $this->command->warn('⚠️  No approved registrations found. QR codes only created for approved registrations.');
            return;
        }

        // Get admin for scanned QR codes
        $admin = Admin::first();

        $totalCreated = 0;
        $scannedCount = 0;

        foreach ($approvedRegistrations as $registration) {
            // Generate unique QR code data
            $qrData = sprintf(
                'MUDIK2026|REG:%d|NIK:%s|DEST:%s|FAM:%d|DATE:%s',
                $registration->id,
                $registration->representative_nik,
                $registration->destination->code ?? 'N/A',
                $registration->family_count,
                now()->format('Y-m-d')
            );

            // QR valid from approval date to end of mudik period (24 Feb)
            $validFrom = $registration->approved_at ?? now();
            $validUntil = Carbon::create(2026, 2, 24, 23, 59, 59);

            // Randomly mark some as scanned (for testing scan functionality)
            $isScanned = rand(0, 3) === 0; // 25% chance already scanned

            $qrCode = QrCode::create([
                'registration_id' => $registration->id,
                'token_qr' => Str::uuid()->toString(),
                'qr_data' => $qrData,
                'valid_from' => $validFrom,
                'valid_until' => $validUntil,
                'scanned_at' => $isScanned && $admin ? now()->subHours(rand(1, 48)) : null,
                'scanned_by' => $isScanned && $admin ? $admin->id : null,
            ]);

            if ($isScanned) {
                $scannedCount++;
            }

            $totalCreated++;
            $status = $isScanned ? '✓ SCANNED' : '○ UNSCANNED';
            $this->command->info("✅ QR Code created for: {$registration->representative_name} ({$status})");
        }

        $this->command->info('');
        $this->command->info('===========================================');
        $this->command->info('📱 QR CODE SUMMARY:');
        $this->command->info('===========================================');
        $this->command->info('Total QR codes: ' . $totalCreated);
        $this->command->info('Already scanned: ' . $scannedCount);
        $this->command->info('Not yet scanned: ' . ($totalCreated - $scannedCount));
        $this->command->info('Valid until: 24 Feb 2026, 23:59:59');
        $this->command->info('===========================================');
    }
}

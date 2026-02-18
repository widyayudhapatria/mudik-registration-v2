<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Destination;
use App\Models\DailyQuota;
use App\Models\FormLink;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $destinations = Destination::where('is_active', true)->orderBy('display_order')->get();

        if ($destinations->isEmpty()) {
            $this->command->error('❌ No destinations found. Please run DestinationQuotaSeeder first.');
            return;
        }

        $admin = Admin::first();
        if (!$admin) {
            $this->command->error('❌ No admin found. Please run AdminSeeder first.');
            return;
        }

        // Get approved & rejected form links (15-17 Feb only)
        $approvedLinks = FormLink::where('status', 'approved')->get();
        $rejectedLinks = FormLink::where('status', 'rejected')->get();

        $totalCreated = 0;
        $totalDeducted = ['daily' => 0, 'destination' => 0];

        // ============================================
        // 1. APPROVED REGISTRATIONS (with quota deduction)
        // ============================================
        $approvedIndex = 0;
        foreach ($approvedLinks as $formLink) {
            $destination = $destinations->get($approvedIndex % $destinations->count());
            $approvedDate = $formLink->used_at->toDateString();

            // Use 1 family member per registration to safely fit within daily quota (2/day)
            // This ensures all 15 registrations can be processed: 15 total ÷ 5 destinations = 3 per destination
            $familyCount = 1;

            DB::transaction(function () use (
                $formLink,
                $destination,
                $approvedDate,
                $familyCount,
                $admin,
                &$totalCreated,
                &$totalDeducted
            ) {
                // Lock daily quota
                $dailyQuota = DailyQuota::where('destination_id', $destination->id)
                    ->where('date', $approvedDate)
                    ->lockForUpdate()
                    ->first();

                if (!$dailyQuota) {
                    $this->command->error("❌ Daily quota not found for {$destination->name} on {$approvedDate}");
                    return;
                }

                // Check & deduct daily quota
                if ($dailyQuota->remaining_daily < $familyCount) {
                    // Try to find another day with available quota
                    $alternateQuota = DailyQuota::where('destination_id', $destination->id)
                        ->where('date', '>=', $approvedDate)
                        ->where('remaining_daily', '>=', $familyCount)
                        ->orderBy('date')
                        ->lockForUpdate()
                        ->first();

                    if (!$alternateQuota) {
                        $this->command->warn("⚠️  No available quota found. Skipping {$formLink->email}");
                        return;
                    }

                    $dailyQuota = $alternateQuota;
                }

                $dailyQuota->used_daily += $familyCount;
                $dailyQuota->remaining_daily = $dailyQuota->quota_daily - $dailyQuota->used_daily;
                $dailyQuota->save();

                // Lock & deduct destination quota
                $dest = Destination::lockForUpdate()->find($destination->id);
                $dest->used_quota += $familyCount;
                $dest->save();

                // Create registration
                $registration = Registration::create([
                    'form_link_id' => $formLink->id,
                    'destination_id' => $destination->id,
                    'representative_name' => 'Approved User ' . ($totalCreated + 1),
                    'representative_nik' => '3201' . str_pad($totalCreated + 1, 12, '0', STR_PAD_LEFT),
                    'representative_birth_date' => Carbon::now()->subYears(30)->format('Y-m-d'),
                    'family_count' => $familyCount,
                    'kk_number' => '3201' . str_pad(1000 + $totalCreated, 12, '0', STR_PAD_LEFT),
                    'kk_document_path' => 'kk_documents/approved_' . ($totalCreated + 1) . '.pdf',
                    'has_child_under_4' => (bool)rand(0, 1),
                    'admin_notes' => 'Valid documents',
                    'approved_by' => $admin->id,
                    'approved_at' => $formLink->used_at,
                ]);

                $totalCreated++;
                $totalDeducted['daily'] += $familyCount;
                $totalDeducted['destination'] += $familyCount;

                $this->command->info("✅ APPROVED: {$formLink->email} → {$destination->name} ({$familyCount} member, Daily: {$dailyQuota->remaining_daily}/{$dailyQuota->quota_daily})");
            });

            $approvedIndex++;
        }

        // ============================================
        // 2. REJECTED REGISTRATIONS (no quota deduction)
        // ============================================
        $rejectedIndex = 0;
        foreach ($rejectedLinks as $formLink) {
            $destination = $destinations->get($rejectedIndex % $destinations->count());

            $registration = Registration::create([
                'form_link_id' => $formLink->id,
                'destination_id' => $destination->id,
                'representative_name' => 'Rejected User ' . ($rejectedIndex + 1),
                'representative_nik' => '3203' . str_pad($rejectedIndex + 1, 12, '0', STR_PAD_LEFT),
                'representative_birth_date' => Carbon::now()->subYears(32)->format('Y-m-d'),
                'family_count' => rand(2, 3),
                'kk_number' => '3203' . str_pad(3000 + $rejectedIndex, 12, '0', STR_PAD_LEFT),
                'kk_document_path' => 'kk_documents/rejected_' . ($rejectedIndex + 1) . '.pdf',
                'has_child_under_4' => false,
                'admin_notes' => 'Invalid documents',
                'rejected_by' => Admin::first()->id,
                'rejected_at' => $formLink->used_at,
                'rejection_reason' => 'Document not clear',
            ]);

            // Soft delete
            $registration->delete();

            $totalCreated++;
            $rejectedIndex++;

            $this->command->info("✅ REJECTED (soft deleted): {$formLink->email} → {$destination->name}");
        }

        // Summary
        $this->command->info('');
        $this->command->info('===========================================');
        $this->command->info('📝 REGISTRATION & QUOTA SUMMARY:');
        $this->command->info('===========================================');
        $this->command->info('Total Registrations (Approved + Rejected): ' . $totalCreated);
        $this->command->info('Approved: ' . $approvedLinks->count());
        $this->command->info('Rejected (soft deleted): ' . $rejectedLinks->count());
        $this->command->info('');
        $this->command->info('❌ QUOTA DEDUCTED:');
        $this->command->info('Daily Quotas Deducted: ' . $totalDeducted['daily'] . ' total members');
        $this->command->info('Destination Quotas Deducted: ' . $totalDeducted['destination'] . ' total members');
        $this->command->info('');
        $this->command->info('✅ SAFETY MARGIN:');
        $this->command->info('Per Destination: 20 total - ' . ($totalDeducted['destination'] / $destinations->count()) . ' approved = ~' . round(20 - ($totalDeducted['destination'] / $destinations->count())) . ' remaining per city');
        $this->command->info('===========================================');
    }
}

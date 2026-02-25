<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Destination;
use App\Models\FormLink;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PaginationTestSeeder extends Seeder
{
    private const TOTAL = 100;

    private const STATUSES = ['pending', 'approved', 'rejected', 'submitted'];

    private const DISTRIBUTION = [
        'approved' => 50,
        'pending'  => 55,
        'rejected' => 50,
        'submitted' => 50,
    ];

    public function run(): void
    {
        $admin = Admin::first();
        $destinations = Destination::where('is_active', true)->orderBy('display_order')->get();

        if (!$admin) {
            $this->command->error('No admin found. Run AdminSeeder first.');
            return;
        }

        if ($destinations->isEmpty()) {
            $this->command->error('No destinations found. Run DestinationQuotaSeeder first.');
            return;
        }

        $this->command->info('Seeding ' . self::TOTAL . ' FormLinks + Registrations for pagination testing...');
        $this->command->newLine();

        $index = 0;

        foreach (self::DISTRIBUTION as $status => $count) {
            for ($i = 1; $i <= $count; $i++) {
                $index++;
                $daysAgo = rand(1, 30);
                $createdAt = Carbon::now()->subDays($daysAgo)->subHours(rand(0, 23))->subMinutes(rand(0, 59));
                $expiredAt = Carbon::now()->addDays(rand(-5, 10)); 

                $formLink = FormLink::create([
                    'email'          => "test_{$status}_{$i}@example.com",
                    'token'          => Str::random(32),
                    'generated_link' => '',
                    'status'         => $status,
                    'expired_at'     => $expiredAt,
                    'used_at'        => in_array($status, ['approved', 'rejected', 'submitted'])
                                            ? $createdAt->copy()->addHours(rand(1, 5))
                                            : null,
                    'resend_count'   => rand(0, 3),
                    'created_at'     => $createdAt,
                    'updated_at'     => $createdAt,
                ]);

                $formLink->generated_link = config('app.url') . "/form/{$formLink->token}";
                $formLink->save();

                // Buat registration untuk status yang relevan
                if (in_array($status, ['approved', 'rejected', 'submitted'])) {
                    $destination = $destinations->get($index % $destinations->count());
                    $familyCount = rand(1, 5);

                    $registrationData = [
                        'form_link_id'            => $formLink->id,
                        'destination_id'          => $destination->id,
                        'representative_name'     => "Test User {$index}",
                        'representative_nik'      => '3201' . str_pad($index, 12, '0', STR_PAD_LEFT),
                        'representative_birth_date' => Carbon::now()->subYears(rand(20, 55))->format('Y-m-d'),
                        'family_count'            => $familyCount,
                        'kk_number'               => '3201' . str_pad(5000 + $index, 12, '0', STR_PAD_LEFT),
                        'kk_document_path'        => "kk_documents/test_{$index}.pdf",
                        'has_child_under_4'       => (bool) rand(0, 1),
                        'created_at'              => $createdAt,
                        'updated_at'              => $createdAt,
                    ];

                    if ($status === 'approved') {
                        $registrationData['admin_notes'] = 'Valid documents - seeded';
                        $registrationData['approved_by'] = $admin->id;
                        $registrationData['approved_at'] = $formLink->used_at;
                    }

                    if ($status === 'rejected') {
                        $registrationData['admin_notes'] = 'Invalid documents - seeded';
                        $registrationData['rejected_by'] = $admin->id;
                        $registrationData['rejected_at'] = $formLink->used_at;
                        $registrationData['rejection_reason'] = 'Document not clear (seeded)';
                    }

                    $registration = Registration::create($registrationData);

                    if ($status === 'rejected') {
                        $registration->delete(); // soft delete
                    }
                }

                $this->command->line("  [{$index}/". self::TOTAL ."] ✅ {$status} — test_{$status}_{$i}@example.com");
            }
        }

        $this->command->newLine();
        $this->command->info('===========================================');
        $this->command->info('PAGINATION TEST SEEDER SUMMARY');
        $this->command->info('===========================================');

        foreach (self::DISTRIBUTION as $status => $count) {
            $this->command->info("  {$status}: {$count} records");
        }

        $this->command->info('-------------------------------------------');
        $this->command->info('  Total FormLinks   : ' . self::TOTAL);
        $this->command->info('  Total Registrations: ' . (self::TOTAL - self::DISTRIBUTION['pending']));
        $this->command->info('===========================================');
    }
}
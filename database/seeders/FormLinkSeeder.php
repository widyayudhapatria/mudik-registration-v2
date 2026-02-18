<?php

namespace Database\Seeders;

use App\Models\FormLink;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FormLinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $formLinks = [
            // APPROVED - 15 Feb (6 total)
            [
                'email' => 'approved15_1@example.com',
                'token' => Str::random(32),
                'generated_link' => '',
                'expired_at' => Carbon::now()->addDays(3),
                'used_at' => Carbon::create(2026, 2, 15, 9, 0, 0),
                'resend_count' => 0,
                'status' => 'approved',
            ],
            [
                'email' => 'approved15_2@example.com',
                'token' => Str::random(32),
                'generated_link' => '',
                'expired_at' => Carbon::now()->addDays(3),
                'used_at' => Carbon::create(2026, 2, 15, 10, 30, 0),
                'resend_count' => 0,
                'status' => 'approved',
            ],
            [
                'email' => 'approved15_3@example.com',
                'token' => Str::random(32),
                'generated_link' => '',
                'expired_at' => Carbon::now()->addDays(3),
                'used_at' => Carbon::create(2026, 2, 15, 11, 45, 0),
                'resend_count' => 0,
                'status' => 'approved',
            ],
            [
                'email' => 'approved15_4@example.com',
                'token' => Str::random(32),
                'generated_link' => '',
                'expired_at' => Carbon::now()->addDays(3),
                'used_at' => Carbon::create(2026, 2, 15, 13, 0, 0),
                'resend_count' => 0,
                'status' => 'approved',
            ],
            [
                'email' => 'approved15_5@example.com',
                'token' => Str::random(32),
                'generated_link' => '',
                'expired_at' => Carbon::now()->addDays(3),
                'used_at' => Carbon::create(2026, 2, 15, 14, 15, 0),
                'resend_count' => 0,
                'status' => 'approved',
            ],
            [
                'email' => 'approved15_6@example.com',
                'token' => Str::random(32),
                'generated_link' => '',
                'expired_at' => Carbon::now()->addDays(3),
                'used_at' => Carbon::create(2026, 2, 15, 15, 30, 0),
                'resend_count' => 0,
                'status' => 'approved',
            ],

            // APPROVED - 16 Feb (5 total)
            [
                'email' => 'approved16_1@example.com',
                'token' => Str::random(32),
                'generated_link' => '',
                'expired_at' => Carbon::now()->addDays(2),
                'used_at' => Carbon::create(2026, 2, 16, 9, 0, 0),
                'resend_count' => 0,
                'status' => 'approved',
            ],
            [
                'email' => 'approved16_2@example.com',
                'token' => Str::random(32),
                'generated_link' => '',
                'expired_at' => Carbon::now()->addDays(2),
                'used_at' => Carbon::create(2026, 2, 16, 10, 30, 0),
                'resend_count' => 0,
                'status' => 'approved',
            ],
            [
                'email' => 'approved16_3@example.com',
                'token' => Str::random(32),
                'generated_link' => '',
                'expired_at' => Carbon::now()->addDays(2),
                'used_at' => Carbon::create(2026, 2, 16, 12, 0, 0),
                'resend_count' => 0,
                'status' => 'approved',
            ],
            [
                'email' => 'approved16_4@example.com',
                'token' => Str::random(32),
                'generated_link' => '',
                'expired_at' => Carbon::now()->addDays(2),
                'used_at' => Carbon::create(2026, 2, 16, 13, 45, 0),
                'resend_count' => 0,
                'status' => 'approved',
            ],
            [
                'email' => 'approved16_5@example.com',
                'token' => Str::random(32),
                'generated_link' => '',
                'expired_at' => Carbon::now()->addDays(2),
                'used_at' => Carbon::create(2026, 2, 16, 15, 0, 0),
                'resend_count' => 0,
                'status' => 'approved',
            ],

            // APPROVED - 17 Feb (4 total)
            [
                'email' => 'approved17_1@example.com',
                'token' => Str::random(32),
                'generated_link' => '',
                'expired_at' => Carbon::now()->addDays(1),
                'used_at' => Carbon::create(2026, 2, 17, 9, 0, 0),
                'resend_count' => 0,
                'status' => 'approved',
            ],
            [
                'email' => 'approved17_2@example.com',
                'token' => Str::random(32),
                'generated_link' => '',
                'expired_at' => Carbon::now()->addDays(1),
                'used_at' => Carbon::create(2026, 2, 17, 10, 30, 0),
                'resend_count' => 0,
                'status' => 'approved',
            ],
            [
                'email' => 'approved17_3@example.com',
                'token' => Str::random(32),
                'generated_link' => '',
                'expired_at' => Carbon::now()->addDays(1),
                'used_at' => Carbon::create(2026, 2, 17, 12, 0, 0),
                'resend_count' => 0,
                'status' => 'approved',
            ],
            [
                'email' => 'approved17_4@example.com',
                'token' => Str::random(32),
                'generated_link' => '',
                'expired_at' => Carbon::now()->addDays(1),
                'used_at' => Carbon::create(2026, 2, 17, 13, 30, 0),
                'resend_count' => 0,
                'status' => 'approved',
            ],

            // REJECTED - 15 Feb (1)
            [
                'email' => 'rejected15_1@example.com',
                'token' => Str::random(32),
                'generated_link' => '',
                'expired_at' => Carbon::now()->addDays(3),
                'used_at' => Carbon::create(2026, 2, 15, 8, 0, 0),
                'resend_count' => 0,
                'status' => 'rejected',
            ],

            // REJECTED - 16 Feb (1)
            [
                'email' => 'rejected16_1@example.com',
                'token' => Str::random(32),
                'generated_link' => '',
                'expired_at' => Carbon::now()->addDays(2),
                'used_at' => Carbon::create(2026, 2, 16, 8, 0, 0),
                'resend_count' => 0,
                'status' => 'rejected',
            ],

            // REJECTED - 17 Feb (1)
            [
                'email' => 'rejected17_1@example.com',
                'token' => Str::random(32),
                'generated_link' => '',
                'expired_at' => Carbon::now()->addDays(1),
                'used_at' => Carbon::create(2026, 2, 17, 8, 0, 0),
                'resend_count' => 0,
                'status' => 'rejected',
            ],
        ];

        foreach ($formLinks as $data) {
            $formLink = FormLink::create($data);
            $formLink->generated_link = config('app.url') . "/form/{$formLink->token}";
            $formLink->save();

            $this->command->info("✅ FormLink created: {$data['email']} ({$data['status']})");
        }

        $this->command->info('');
        $this->command->info('===========================================');
        $this->command->info('📧 FORM LINK SUMMARY (15-17 Feb 2026):');
        $this->command->info('===========================================');
        $this->command->info('15 Feb - Approved: 6, Rejected: 1');
        $this->command->info('16 Feb - Approved: 5, Rejected: 1');
        $this->command->info('17 Feb - Approved: 4, Rejected: 1');
        $this->command->info('Total: 15 approved + 3 rejected = 18 form links');
        $this->command->info('===========================================');
    }
}

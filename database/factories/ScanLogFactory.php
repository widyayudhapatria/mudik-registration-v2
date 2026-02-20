<?php

namespace Database\Factories;

use App\Enums\ScanResult;
use App\Models\Admin;
use App\Models\QrCode;
use App\Models\ScanLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScanLogFactory extends Factory
{
    protected $model = ScanLog::class;

    public function definition(): array
    {
        return [
            'qr_code_id' => QrCode::factory(),
            'admin_id' => Admin::factory(),
            'scan_result' => ScanResult::Success->value,
            'scanned_at' => Carbon::now(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'failure_reason' => null,
        ];
    }

    /**
     * Successful scan.
     */
    public function success(): static
    {
        return $this->state(fn (array $attributes) => [
            'scan_result' => ScanResult::Success->value,
            'failure_reason' => null,
        ]);
    }

    /**
     * Failed scan.
     */
    public function failed(string $reason = null): static
    {
        return $this->state(fn (array $attributes) => [
            'scan_result' => ScanResult::Failed->value,
            'failure_reason' => $reason ?? 'QR Code already scanned',
        ]);
    }

    /**
     * Scanned today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'scanned_at' => Carbon::today()->addHours(fake()->numberBetween(8, 20)),
        ]);
    }

    /**
     * Scanned at specific time.
     */
    public function at(Carbon $dateTime): static
    {
        return $this->state(fn (array $attributes) => [
            'scanned_at' => $dateTime,
        ]);
    }

    /**
     * With specific IP address.
     */
    public function fromIp(string $ip): static
    {
        return $this->state(fn (array $attributes) => [
            'ip_address' => $ip,
        ]);
    }
}
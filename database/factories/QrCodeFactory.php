<?php

namespace Database\Factories;

use App\Models\QrCode;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class QrCodeFactory extends Factory
{
    protected $model = QrCode::class;

    public function definition(): array
    {
        $validFrom = Carbon::today()->setTime(0, 0, 0);
        $validUntil = Carbon::today()->setTime(23, 59, 59);

        return [
            'registration_id' => Registration::factory(),
            'token_qr' => Str::random(64),
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'scanned_at' => null,
            'scanned_by' => null,
        ];
    }

    /**
     * Valid for today.
     */
    public function validToday(): static
    {
        return $this->state(fn (array $attributes) => [
            'valid_from' => Carbon::today()->setTime(0, 0, 0),
            'valid_until' => Carbon::today()->setTime(23, 59, 59),
        ]);
    }

    /**
     * Valid for tomorrow.
     */
    public function validTomorrow(): static
    {
        return $this->state(fn (array $attributes) => [
            'valid_from' => Carbon::tomorrow()->setTime(0, 0, 0),
            'valid_until' => Carbon::tomorrow()->setTime(23, 59, 59),
        ]);
    }

    /**
     * Valid for specific date.
     */
    public function validFor(Carbon $date): static
    {
        return $this->state(fn (array $attributes) => [
            'valid_from' => $date->copy()->setTime(0, 0, 0),
            'valid_until' => $date->copy()->setTime(23, 59, 59),
        ]);
    }

    /**
     * Expired (yesterday).
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'valid_from' => Carbon::yesterday()->setTime(0, 0, 0),
            'valid_until' => Carbon::yesterday()->setTime(23, 59, 59),
        ]);
    }

    /**
     * Not yet valid (tomorrow).
     */
    public function notYetValid(): static
    {
        return $this->state(fn (array $attributes) => [
            'valid_from' => Carbon::tomorrow()->setTime(0, 0, 0),
            'valid_until' => Carbon::tomorrow()->setTime(23, 59, 59),
        ]);
    }

    /**
     * Already scanned.
     */
    public function scanned(): static
    {
        return $this->state(fn (array $attributes) => [
            'scanned_at' => Carbon::now(),
            'scanned_by' => \App\Models\Admin::factory(),
        ]);
    }

    /**
     * Scanned by specific admin.
     */
    public function scannedBy(int $adminId): static
    {
        return $this->state(fn (array $attributes) => [
            'scanned_at' => Carbon::now(),
            'scanned_by' => $adminId,
        ]);
    }

    /**
     * With specific token.
     */
    public function withToken(string $token): static
    {
        return $this->state(fn (array $attributes) => [
            'token_qr' => $token,
        ]);
    }
}
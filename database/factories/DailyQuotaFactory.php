<?php

namespace Database\Factories;

use App\Models\DailyQuota;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class DailyQuotaFactory extends Factory
{
    protected $model = DailyQuota::class;

    public function definition(): array
    {
        $quota = fake()->numberBetween(50, 200);
        $used = fake()->numberBetween(0, $quota);
        $remaining = $quota - $used;

        return [
            'date' => Carbon::today(),
            'quota' => $quota,
            'used' => $used,
            'remaining' => $remaining,
        ];
    }

    /**
     * Quota for today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => Carbon::today(),
        ]);
    }

    /**
     * Quota for tomorrow.
     */
    public function tomorrow(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => Carbon::tomorrow(),
        ]);
    }

    /**
     * Quota for specific date.
     */
    public function forDate(Carbon $date): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $date,
        ]);
    }

    /**
     * Full quota (no remaining).
     */
    public function full(): static
    {
        return $this->state(function (array $attributes) {
            $quota = $attributes['quota'] ?? 100;
            
            return [
                'quota' => $quota,
                'used' => $quota,
                'remaining' => 0,
            ];
        });
    }

    /**
     * Empty quota (not used yet).
     */
    public function empty(): static
    {
        return $this->state(function (array $attributes) {
            $quota = $attributes['quota'] ?? 100;
            
            return [
                'quota' => $quota,
                'used' => 0,
                'remaining' => $quota,
            ];
        });
    }

    /**
     * With specific quota amount.
     */
    public function withQuota(int $quota): static
    {
        return $this->state(function (array $attributes) use ($quota) {
            $used = $attributes['used'] ?? 0;
            
            return [
                'quota' => $quota,
                'remaining' => $quota - $used,
            ];
        });
    }

    /**
     * With specific used amount.
     */
    public function withUsed(int $used): static
    {
        return $this->state(function (array $attributes) use ($used) {
            $quota = $attributes['quota'] ?? 100;
            
            return [
                'used' => $used,
                'remaining' => $quota - $used,
            ];
        });
    }

    /**
     * Half used (50%).
     */
    public function halfUsed(): static
    {
        return $this->state(function (array $attributes) {
            $quota = $attributes['quota'] ?? 100;
            $used = (int) ($quota / 2);
            
            return [
                'quota' => $quota,
                'used' => $used,
                'remaining' => $quota - $used,
            ];
        });
    }

    /**
     * Almost full (90% used).
     */
    public function almostFull(): static
    {
        return $this->state(function (array $attributes) {
            $quota = $attributes['quota'] ?? 100;
            $used = (int) ($quota * 0.9);
            
            return [
                'quota' => $quota,
                'used' => $used,
                'remaining' => $quota - $used,
            ];
        });
    }
}
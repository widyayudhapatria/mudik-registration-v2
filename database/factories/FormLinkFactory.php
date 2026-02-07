<?php

namespace Database\Factories;

use App\Enums\FormLinkStatus;
use App\Models\FormLink;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FormLinkFactory extends Factory
{
    protected $model = FormLink::class;

    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'token' => Str::random(64),
            'expired_at' => Carbon::now()->addDays(3),
            'used_at' => null,
            'resend_count' => 0,
            'status' => FormLinkStatus::Pending->value,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expired_at' => Carbon::now()->subDays(1),
        ]);
    }

    public function used(): static
    {
        return $this->state(fn (array $attributes) => [
            'used_at' => Carbon::now(),
            'status' => FormLinkStatus::Submitted->value,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => FormLinkStatus::Approved->value,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => FormLinkStatus::Rejected->value,
        ]);
    }
}
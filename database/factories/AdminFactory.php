<?php

namespace Database\Factories;

use App\Enums\AdminRole;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'), // Default password for testing
            'role' => AdminRole::Scanner->value,
            'can_scan' => false,
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Super admin state.
     */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => AdminRole::SuperAdmin->value,
            'can_scan' => true,
        ]);
    }

    /**
     * Validator state.
     */
    public function validator(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => AdminRole::Validator->value,
            'can_scan' => false,
        ]);
    }

    /**
     * Scanner state.
     */
    public function scanner(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => AdminRole::Scanner->value,
            'can_scan' => true,
        ]);
    }

    /**
     * Inactive account state.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * With specific email.
     */
    public function withEmail(string $email): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => $email,
        ]);
    }
}
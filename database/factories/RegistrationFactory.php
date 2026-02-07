<?php

namespace Database\Factories;

use App\Models\FormLink;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class RegistrationFactory extends Factory
{
    protected $model = Registration::class;

    public function definition(): array
    {
        $hasChildUnder4 = fake()->boolean(30); // 30% chance

        return [
            'form_link_id' => FormLink::factory(),
            'representative_name' => fake()->name(),
            'representative_nik' => $this->generateNIK(),
            'representative_birth_date' => fake()->dateTimeBetween('-60 years', '-18 years'),
            'family_count' => fake()->numberBetween(1, 6),
            'kk_number' => $this->generateKKNumber(),
            'kk_document_path' => 'uploads/kk_documents/' . fake()->uuid() . '.jpg',
            'has_child_under_4' => $hasChildUnder4,
            'admin_notes' => null,
            'approved_by' => null,
            'approved_at' => null,
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ];
    }

    /**
     * Approved state.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_by' => \App\Models\Admin::factory(),
            'approved_at' => Carbon::now(),
        ]);
    }

    /**
     * Rejected state.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'rejected_by' => \App\Models\Admin::factory(),
            'rejected_at' => Carbon::now(),
            'rejection_reason' => fake()->sentence(),
        ]);
    }

    /**
     * With child under 4.
     */
    public function withChildUnder4(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_child_under_4' => true,
        ]);
    }

    /**
     * Without child under 4.
     */
    public function withoutChildUnder4(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_child_under_4' => false,
        ]);
    }

    /**
     * Generate NIK (16 digits).
     */
    protected function generateNIK(): string
    {
        return '32' . fake()->numerify('##############');
    }

    /**
     * Generate KK Number (16 digits).
     */
    protected function generateKKNumber(): string
    {
        return '32' . fake()->numerify('##############');
    }
}
<?php

namespace Database\Factories;

use App\Models\Participant;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParticipantFactory extends Factory
{
    protected $model = Participant::class;

    public function definition(): array
    {
        $birthDate = fake()->dateTimeBetween('-60 years', '-5 years');
        $age = Carbon::parse($birthDate)->age;
        $isChildUnder4 = $age < 4;

        return [
            'registration_id' => Registration::factory(),
            'full_name' => fake()->name(),
            'nik_kia' => $this->generateNIKOrKIA(),
            'birth_date' => $birthDate,
            'is_child_under_4' => $isChildUnder4,
        ];
    }

    /**
     * Child under 4 years old.
     */
    public function childUnder4(): static
    {
        $birthDate = fake()->dateTimeBetween('-3 years', '-1 month');

        return $this->state(fn (array $attributes) => [
            'birth_date' => $birthDate,
            'is_child_under_4' => true,
        ]);
    }

    /**
     * Adult participant.
     */
    public function adult(): static
    {
        $birthDate = fake()->dateTimeBetween('-60 years', '-18 years');

        return $this->state(fn (array $attributes) => [
            'birth_date' => $birthDate,
            'is_child_under_4' => false,
        ]);
    }

    /**
     * Child (4-17 years).
     */
    public function child(): static
    {
        $birthDate = fake()->dateTimeBetween('-17 years', '-4 years');

        return $this->state(fn (array $attributes) => [
            'birth_date' => $birthDate,
            'is_child_under_4' => false,
        ]);
    }

    /**
     * Generate NIK or KIA (16 digits).
     */
    protected function generateNIKOrKIA(): string
    {
        return '32' . fake()->numerify('##############');
    }
}
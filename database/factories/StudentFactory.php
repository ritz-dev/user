<?php

namespace Database\Factories;

use App\Models\Personal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->uuid(),
            'personal_id' => Personal::inRandomOrder()->first()->id,
            'student_code' => fake()->numerify('STU-####'),
            'name' => fake()->name(),
            'address' => fake()->address(),
            'email' => fake()->email(),
            'phonenumber' => fake()->phoneNumber(),
            'pob' => fake()->city(),
            'nationality' => fake()->word(),
            'religion' => fake()->word(),
            'blood_type' => 'A',
            'status' => 'active',
            'academic_level' => 'Primary 1',
            'academic_year' => '2025-2026',
            'enrollment_date' => '2025-01-16',
            'graduation_date' => '2026-12-30'
        ];
    }
}

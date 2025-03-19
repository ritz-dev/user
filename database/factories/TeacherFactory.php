<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Teacher>
 */
class TeacherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $personalId = 2;
        return [
            'slug' => fake()->unique()->uuid(),
            'personal_id' => $personalId++,
            'teacher_code' => fake()->numerify('TCH-####'),
            'email' => fake()->email(),
            'phonenumber' => fake()->phoneNumber(),
            'department' => fake()->word(),
            'salary' => fake()->numberBetween(1, 100),
            'hire_date' => fake()->date(),
            'status' => "active",
            'employment_type' => "full-time",
            'specialization' => fake()->word(),
            'designation' => fake()->word(),
        ];
    }
}

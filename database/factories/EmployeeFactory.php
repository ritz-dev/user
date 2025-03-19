<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Generator as Faker;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        static $personalId = 1;

        return [
            'slug' => fake()->unique()->uuid(),
            'personal_id' => $personalId++,
            'email' => fake()->email(),
            'phonenumber' => fake()->phoneNumber(),
            'department' => fake()->word(),
            'salary' => fake()->numberBetween(200000, 400000),
            'hire_date' => fake()->date(),
            'status' => 'active',
            'employment_type' => 'full-time'
        ];
    }
}

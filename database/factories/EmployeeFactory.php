<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Generator as Faker;

class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        static $personalId = 1;

        return [
            'slug' => $this->faker->unique()->uuid(),
            'personal_id' => $personalId++,
            'email' => $this->faker->unique()->email(),
            'phonenumber' => $this->faker->phoneNumber(),
            'department' => $this->faker->word(),
            'salary' => $this->faker->numberBetween(200000, 400000),
            'hire_date' => $this->faker->date(),
            'status' => 'active',
            'employment_type' => 'full-time',
        ];
    }
}

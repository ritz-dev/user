<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        static $personalId = 1;

        return [
            'slug' => (string) \Illuminate\Support\Str::uuid(),
            'personal_id' => $personalId++,
            'email' => 'employee' . $personalId . '@example.com',
            'phonenumber' => '123-456-7890',
            'department' => 'Engineering',
            'salary' => 300000,
            'hire_date' => now()->toDateString(),
            'status' => 'active',
            'employment_type' => 'full-time',
        ];
    }
}

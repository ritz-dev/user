<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        static $teacherCounter = 1;

        return [
            'slug' => (string) Str::uuid(),
            'personal_id' => $personalId++,
            'teacher_code' => 'TCH-' . str_pad($teacherCounter++, 4, '0', STR_PAD_LEFT),
            'email' => 'teacher' . $teacherCounter . '@example.com',
            'phonenumber' => '123-456-7890',
            'department' => 'Mathematics',
            'salary' => 50000,
            'hire_date' => now()->toDateString(),
            'status' => "active",
            'employment_type' => "full-time",
            'specialization' => 'Science',
            'designation' => 'Senior Teacher',
        ];
    }
}

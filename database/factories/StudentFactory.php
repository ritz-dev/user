<?php

namespace Database\Factories;

use App\Models\Personal;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        static $studentCounter = 1;

        return [
            'slug' => (string) Str::uuid(),
            'personal_id' => Personal::inRandomOrder()->value('id') ?? 1, // Fallback to 1 if no records exist
            'student_code' => 'STU-' . str_pad($studentCounter++, 4, '0', STR_PAD_LEFT),
            'name' => 'Student ' . $studentCounter,
            'address' => '123 Main St, City, Country',
            'email' => 'student' . $studentCounter . '@example.com',
            'phonenumber' => '123-456-7890',
            'pob' => 'Sample City',
            'nationality' => 'Sample Nationality',
            'religion' => 'Sample Religion',
            'blood_type' => 'A',
            'status' => 'active',
            'academic_level' => 'Primary 1',
            'academic_year' => '2025-2026',
            'enrollment_date' => '2025-01-16',
            'graduation_date' => '2026-12-30',
        ];
    }
}

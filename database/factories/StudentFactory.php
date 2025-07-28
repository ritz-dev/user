<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\Personal;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    
    protected $model = Student::class;

    public function definition(): array
    {
        // Create a related personal first
        $personal = Personal::factory()->create();

        return [
            'slug' => Str::uuid()->toString(),
            'personal_slug' => $personal->slug,
            'student_name' => $this->faker->name(),
            'student_number' => strtoupper(Str::random(10)),
            'registration_number' => strtoupper(Str::random(8)),
            'school_name' => $this->faker->company . ' High School',
            'school_code' => strtoupper(Str::random(6)),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->unique()->phoneNumber(),
            'address' => $this->faker->address(),
            'status' => $this->faker->randomElement(['enrolled', 'graduated', 'suspended', 'inactive']),
            'graduation_date' => $this->faker->optional()->date(),
            'admission_date' => $this->faker->optional()->date(),
        ];
    }
}

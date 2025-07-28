<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\Guardian;
use App\Models\Personal;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Guardian>
 */
class GuardianFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Guardian::class;


    public function definition(): array
    {
        return [
            'slug' => Str::uuid(),
            'personal_slug' => Personal::factory()->create()->slug,
            'student_slug' => Student::factory()->create()->slug,
            'relation' => $this->faker->randomElement(['father', 'mother', 'guardian']),
            'occupation' => $this->faker->jobTitle(),
            'name' => $this->faker->name(),
            'phone' => $this->faker->unique()->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
        ];
    }
}

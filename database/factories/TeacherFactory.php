<?php

namespace Database\Factories;

use App\Models\Teacher;
use App\Models\Personal;
use Illuminate\Support\Str;
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
    protected $model = Teacher::class;
    
    public function definition(): array
    {
        $personal = Personal::factory()->create();

        return [
            'slug'             => Str::uuid()->toString(),
            'personal_slug'    => $personal->slug, // You can override this with a real personal_slug in tests
            'teacher_name'     => $this->faker->name,
            'teacher_code'     => 'TCH-' . strtoupper(Str::random(6)),
            'email'            => $this->faker->unique()->safeEmail,
            'phone'            => $this->faker->unique()->phoneNumber,
            'address'          => $this->faker->address,
            'qualification'    => $this->faker->randomElement(['B.Ed', 'M.Ed', 'Ph.D', 'B.Sc']),
            'subject'          => $this->faker->randomElement(['Math', 'Science', 'History', 'English']),
            'experience_years' => $this->faker->numberBetween(0, 20),
            'salary'           => $this->faker->randomFloat(2, 300000, 1500000),
            'hire_date'        => $this->faker->date(),
            'status'           => $this->faker->randomElement(['active', 'resigned', 'on_leave']),
            'employment_type'  => $this->faker->randomElement(['fulltime', 'parttime', 'contract']),
        ];
    }
}

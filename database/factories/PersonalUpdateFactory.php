<?php

namespace Database\Factories;

use App\Models\Personal;
use Illuminate\Support\Str;
use App\Models\PersonalUpdate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PersonalUpdate>
 */
class PersonalUpdateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = PersonalUpdate::class;

    public function definition(): array
    {
        return [
            'personal_slug'   => Personal::factory()->create()->slug,
            // Snapshot of personal details
            'full_name'       => $this->faker->name(),
            'birth_date'      => $this->faker->date('Y-m-d'),
            'gender'          => $this->faker->randomElement(['male', 'female']),
            'region_code'     => $this->faker->randomElement(['1', '2', '3']),
            'township_code'   => strtoupper($this->faker->lexify('??')),
            'citizenship'     => $this->faker->randomElement(['N', 'F']),
            'serial_number'   => strtoupper(Str::random(6)),
            'nationality'     => $this->faker->country(),
            'religion'        => $this->faker->randomElement(['Buddhism', 'Christianity', 'Islam', 'Hinduism']),
            'blood_type'      => $this->faker->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),

            // Polymorphic source (e.g. Student, Employee)
            'updatable_slug'  => Str::uuid(),
            'updatable_type'  => 'student',

            'created_at'      => now(),
            'updated_at'      => now(),
        ];
    }
}

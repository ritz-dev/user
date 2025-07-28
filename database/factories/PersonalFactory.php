<?php

namespace Database\Factories;

use App\Models\Personal;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Personal>
 */
class PersonalFactory extends Factory
{
    protected $model = Personal::class;

    public function definition(): array
    {
        return [
            'slug' => Str::uuid()->toString(),
            'full_name' => $this->faker->name(),
            'birth_date' => $this->faker->date(),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'region_code' => strtoupper(Str::random(2)),
            'township_code' => strtoupper(Str::random(3)),
            'citizenship' => $this->faker->country(),
            'serial_number' => strtoupper(Str::random(6)),
            'nationality' => $this->faker->optional()->country(),
            'religion' => $this->faker->optional()->randomElement(['Buddhism', 'Christianity', 'Islam', 'Hinduism']),
            'blood_type' => $this->faker->optional()->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
        ];
    }
}

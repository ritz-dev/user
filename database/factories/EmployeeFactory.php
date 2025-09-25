<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Personal;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {

        return [
            'slug'              => Str::uuid()->toString(),
            'employee_name'     => $this->faker->name,
            'employee_code'     => strtoupper('EMP-' . $this->faker->unique()->numerify('#####')),
            'email'             => $this->faker->unique()->safeEmail,
            'phone'             => $this->faker->unique()->phoneNumber,
            'address'           => $this->faker->address,
            'position'          => $this->faker->randomElement(['clerk', 'manager', 'cashier']),
            'department'        => $this->faker->randomElement(['HR', 'Finance', 'IT']),
            'salary'            => $this->faker->numberBetween(300000, 1000000),
            'hire_date'         => $this->faker->date(),
            'status'            => $this->faker->randomElement(['active', 'resigned', 'on_leave']),
            'employment_type'   => $this->faker->randomElement(['full-time', 'part-time', 'contract']),
            'personal_slug'     => Personal::factory()->create()->slug,
        ];
    }
}

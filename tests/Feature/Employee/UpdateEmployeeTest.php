<?php

namespace Tests\Feature\Employee;

use Tests\TestCase;
use App\Models\Employee;
use App\Models\Personal;
use App\Models\PersonalUpdate;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateEmployeeTest extends TestCase
{
    use RefreshDatabase;

    protected string $endpoint = 'gateway/employees/update';

    protected $employee;
    protected $personal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->personal = Personal::factory()->create();
        $this->employee = Employee::factory()->create([
            'personal_slug' => $this->personal->slug,
            'employee_code' => 'EMP001',
            'email' => 'existing@example.com',
            'phone' => '091111111',
        ]);
    }

    protected function payload(array $overrides = []): array
    {
        return array_merge([
            'slug' => $this->employee->slug,
            'employee_code' => 'EMP999',
            'email' => 'newemail@example.com',
            'phone' => '092222222',
            'address' => 'New Address',
            'position' => 'Clerk',
            'department' => 'HR',
            'experience_years' => 2,
            'salary' => 75000,
            'hire_date' => '2023-01-01',
            'status' => 'active',
            'employment_type' => 'full-time',
            'resign_date' => null,

            'full_name' => 'Updated Name',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'region_code' => '12',
            'township_code' => '05',
            'citizenship' => 'Myanmar',
            'serial_number' => '123456',
            'nationality' => 'Bamar',
            'religion' => 'Buddhism',
            'blood_type' => 'O+',
        ], $overrides);
    }

    /** @test */
    public function update_requires_slug()
    {
        $this->postJson($this->endpoint, $this->payload(['slug' => null]))
             ->assertJsonValidationErrors('slug');
    }

    /** @test */
    public function update_requires_unique_employee_code_except_self()
    {
        $another = Employee::factory()->create(['employee_code' => 'EMP999']);

        $this->postJson($this->endpoint, $this->payload(['employee_code' => 'EMP999']))
             ->assertJsonValidationErrors('employee_code');
    }

    /** @test */
    public function update_requires_required_personal_fields()
    {
        $this->postJson($this->endpoint, $this->payload([
            'full_name' => null,
            'birth_date' => null,
            'gender' => null,
            'region_code' => null,
            'township_code' => null,
            'citizenship' => null,
            'serial_number' => null,
        ]))->assertJsonValidationErrors([
            'full_name', 'birth_date', 'gender', 'region_code', 'township_code', 'citizenship', 'serial_number'
        ]);
    }

    /** @test */
    public function update_fails_with_invalid_enum_values()
    {
        $this->postJson($this->endpoint, $this->payload([
            'status' => 'paused',
            'employment_type' => 'casual',
            'blood_type' => 'X+',
        ]))->assertJsonValidationErrors(['status', 'employment_type', 'blood_type']);
    }

    /** @test */
    public function update_fails_with_non_numeric_salary()
    {
        $this->postJson($this->endpoint, $this->payload(['salary' => 'abc']))
             ->assertJsonValidationErrors('salary');
    }

    /** @test */
    public function update_fails_with_invalid_date()
    {
        $this->postJson($this->endpoint, $this->payload(['hire_date' => 'not-a-date']))
             ->assertJsonValidationErrors('hire_date');
    }

    /** @test */
    public function update_fails_with_duplicate_email()
    {
        Employee::factory()->create(['email' => 'taken@example.com']);

        $this->postJson($this->endpoint, $this->payload(['email' => 'taken@example.com']))
             ->assertJsonValidationErrors('email');
    }

    /** @test */
    public function update_fails_with_duplicate_phone()
    {
        Employee::factory()->create(['phone' => '099999999']);

        $this->postJson($this->endpoint, $this->payload(['phone' => '099999999']))
             ->assertJsonValidationErrors('phone');
    }

    /** @test */
    public function update_fails_with_empty_request()
    {
        $this->postJson($this->endpoint, [])
             ->assertStatus(422)
             ->assertJsonStructure(['success', 'message', 'errors']);
    }

    /** @test */
    public function update_returns_proper_response_structure()
    {
        $response = $this->postJson($this->endpoint, $this->payload());

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'data' => [
                        'slug', 'employee_code', 'employee_name', 'email', 'phone',
                         'personal' => [
                             'slug', 'full_name', 'birth_date', 'gender', 'region_code',
                             'township_code', 'citizenship', 'serial_number'
                         ]
                     ]
                 ]);
    }
}

<?php

namespace Tests\Feature\Employee;

use Tests\TestCase;
use App\Models\Employee;
use App\Models\Personal;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateEmployeeTest extends TestCase
{
    use RefreshDatabase;

    protected string $endpoint = 'gateway/employees/store';

    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            // Personal
            'full_name' => 'Test Employee',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'region_code' => '12',
            'township_code' => 'THP',
            'citizenship' => 'N',
            'serial_number' => '123456',
            'nationality' => 'Myanmar',
            'religion' => 'Buddhism',
            'blood_type' => 'O+',

            // Employee
            'employee_code' => 'EMP001',
            'email' => 'employee@example.com',
            'phone' => '09123456789',
            'address' => 'Yangon',
            'position' => 'Manager',
            'department' => 'HR',
            'employment_type' => 'full-time',
            'hire_date' => '2023-01-01',
            'resign_date' => null,
            'experience_years' => 5,
            'salary' => 500000,
            'status' => 'active',
        ], $overrides);
    }

    public function test_01_create_employee_successfully()
    {
        $response = $this->postJson($this->endpoint, $this->validPayload());

        $response->assertCreated()
                 ->assertJsonStructure(['message', 'data' => ['slug', 'employee_code', 'personal']]);

        $this->assertDatabaseCount('employees', 1);
        $this->assertDatabaseCount('personals', 1);
    }

    public function test_02_required_fields_validation()
    {
        $response = $this->postJson($this->endpoint, []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'full_name', 'birth_date', 'gender', 'region_code', 'township_code',
                     'citizenship', 'serial_number', 'employee_code',
                     'employment_type', 'hire_date', 'salary', 'status'
                 ]);
    }

    public function test_03_invalid_email_format()
    {
        $payload = $this->validPayload(['email' => 'invalid-email']);
        $this->postJson($this->endpoint, $payload)->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_04_invalid_gender()
    {
        $payload = $this->validPayload(['gender' => 'other']);
        $this->postJson($this->endpoint, $payload)->assertStatus(422)->assertJsonValidationErrors(['gender']);
    }

    public function test_05_invalid_blood_type()
    {
        $payload = $this->validPayload(['blood_type' => 'X+']);
        $this->postJson($this->endpoint, $payload)->assertStatus(422)->assertJsonValidationErrors(['blood_type']);
    }

    public function test_06_invalid_employment_type()
    {
        $payload = $this->validPayload(['employment_type' => 'intern']);
        $this->postJson($this->endpoint, $payload)->assertStatus(422)->assertJsonValidationErrors(['employment_type']);
    }

    public function test_07_invalid_status()
    {
        $payload = $this->validPayload(['status' => 'suspended']);
        $this->postJson($this->endpoint, $payload)->assertStatus(422)->assertJsonValidationErrors(['status']);
    }

    public function test_08_duplicate_employee_code()
    {
        Employee::factory()->create(['employee_code' => 'EMP001']);
        $this->postJson($this->endpoint, $this->validPayload())->assertStatus(422)->assertJsonValidationErrors(['employee_code']);
    }

    public function test_09_duplicate_email()
    {
        Employee::factory()->create(['email' => 'employee@example.com']);
        $this->postJson($this->endpoint, $this->validPayload())->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_10_duplicate_phone()
    {
        Employee::factory()->create(['phone' => '09123456789']);
        $this->postJson($this->endpoint, $this->validPayload())->assertStatus(422)->assertJsonValidationErrors(['phone']);
    }

    public function test_11_personal_already_assigned()
    {
        $personal = Personal::factory()->create([
            'region_code' => '12',
            'township_code' => 'THP',
            'citizenship' => 'N',
            'serial_number' => '123456',
        ]);

        Employee::factory()->create(['personal_slug' => $personal->slug]);

        $this->postJson($this->endpoint, $this->validPayload())->assertStatus(409);
    }

    public function test_12_negative_experience_years()
    {
        $payload = $this->validPayload(['experience_years' => -3]);
        $this->postJson($this->endpoint, $payload)->assertStatus(422)->assertJsonValidationErrors(['experience_years']);
    }

    public function test_13_negative_salary()
    {
        $payload = $this->validPayload(['salary' => -20000]);
        $this->postJson($this->endpoint, $payload)->assertStatus(422)->assertJsonValidationErrors(['salary']);
    }

    public function test_14_optional_fields_can_be_null()
    {
        $payload = $this->validPayload([
            'religion' => null,
            'email' => null,
            'phone' => null,
            'resign_date' => null,
        ]);

        $this->postJson($this->endpoint, $payload)->assertCreated();
    }

    public function test_15_invalid_nrc_fields()
    {
        $payload = $this->validPayload([
            'region_code' => str_repeat('A', 11),
            'township_code' => str_repeat('B', 11),
            'citizenship' => str_repeat('C', 11),
            'serial_number' => str_repeat('D', 21),
        ]);

        $this->postJson($this->endpoint, $payload)->assertStatus(422)->assertJsonValidationErrors([
            'region_code', 'township_code', 'citizenship', 'serial_number',
        ]);
    }
}

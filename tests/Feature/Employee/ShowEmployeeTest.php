<?php

namespace Tests\Feature\Employee;

use Tests\TestCase;
use App\Models\Employee;
use App\Models\PersonalUpdate;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShowEmployeeTest extends TestCase
{
    use RefreshDatabase;

    protected string $endpoint = 'gateway/employees/show';

    public function test_01_valid_slug_returns_employee()
    {
        $employee = Employee::factory()->create();

        $response = $this->postJson($this->endpoint, [
            'slug' => $employee->slug,
        ]);

        $response->assertOk()
                 ->assertJson([
                    'status' => 'OK! The request was successful',
                    'data' => [
                        'slug' => $employee->slug,
                        'personal' => [
                            'full_name' => $employee->personal->full_name,
                        ],
                    ],
                 ]);
    }

    public function test_02_non_existing_slug_returns_404()
    {
        $response = $this->postJson($this->endpoint, [
            'slug' => 'non-existent-slug',
        ]);

        $response->assertStatus(422); // Because of 'exists' validation rule failing
    }

    public function test_03_validation_error_when_slug_missing()
    {
        $response = $this->postJson($this->endpoint, []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['slug']);
    }

    public function test_04_validation_error_when_slug_invalid_type()
    {
        $response = $this->postJson($this->endpoint, ['slug' => 12345]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['slug']);
    }

    public function test_05_personal_update_replaces_personal_data()
    {
        $employee = Employee::factory()->create();

        $update = PersonalUpdate::factory()->create([
            'updatable_type' => Employee::class,
            'updatable_slug' => $employee->slug,
            'personal_slug' => $employee->personal_slug,
            'full_name' => 'Updated Full Name',
        ]);

        $response = $this->postJson($this->endpoint, [
            'slug' => $employee->slug,
        ]);

        $response->assertOk()
                 ->assertJsonPath('data.personal.full_name', 'Updated Full Name');
    }
}

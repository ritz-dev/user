<?php

namespace Tests\Feature\Employee;

use Tests\TestCase;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActionEmployeeTest extends TestCase
{
    use RefreshDatabase;

    protected string $endpoint = 'gateway/employees/action';

    public function test_01_set_status_active()
    {
        $employee = Employee::factory()->create(['status' => 'resigned']);

        $response = $this->postJson($this->endpoint, [
            'slug' => $employee->slug,
            'action' => 'active',
        ]);

        $response->assertOk()
                 ->assertJson(['message' => 'Employee status set to active']);

        $this->assertDatabaseHas('employees', [
            'slug' => $employee->slug,
            'status' => 'active',
        ]);
    }

    public function test_02_set_status_resigned()
    {
        $employee = Employee::factory()->create(['status' => 'active']);

        $response = $this->postJson($this->endpoint, [
            'slug' => $employee->slug,
            'action' => 'resigned',
        ]);

        $response->assertOk()
                 ->assertJson(['message' => 'Employee status set to resigned']);

        $this->assertDatabaseHas('employees', [
            'slug' => $employee->slug,
            'status' => 'resigned',
        ]);
    }

    public function test_03_set_status_on_leave()
    {
        $employee = Employee::factory()->create(['status' => 'active']);

        $response = $this->postJson($this->endpoint, [
            'slug' => $employee->slug,
            'action' => 'on_leave',
        ]);

        $response->assertOk()
                 ->assertJson(['message' => 'Employee status set to on_leave']);

        $this->assertDatabaseHas('employees', [
            'slug' => $employee->slug,
            'status' => 'on_leave',
        ]);
    }

    public function test_04_soft_delete_employee()
    {
        $employee = Employee::factory()->create(['status' => 'active']);

        $response = $this->postJson($this->endpoint, [
            'slug' => $employee->slug,
            'action' => 'delete',
        ]);

        $response->assertOk()
                 ->assertJson(['message' => 'Employee soft-deleted']);

        $this->assertSoftDeleted('employees', [
            'slug' => $employee->slug,
        ]);

        $this->assertDatabaseHas('employees', [
            'slug' => $employee->slug,
            'status' => 'resigned',
        ]);
    }

    public function test_05_restore_soft_deleted_employee()
    {
        $employee = Employee::factory()->create(['status' => 'resigned']);
        $employee->delete();

        $response = $this->postJson($this->endpoint, [
            'slug' => $employee->slug,
            'action' => 'restore',
        ]);

        $response->assertOk()
                 ->assertJson(['message' => 'Employee restored']);

        $this->assertDatabaseHas('employees', [
            'slug' => $employee->slug,
            'status' => 'active',
            'deleted_at' => null,
        ]);
    }

    public function test_06_restore_non_deleted_employee_fails()
    {
        $employee = Employee::factory()->create(['status' => 'active']);

        $response = $this->postJson($this->endpoint, [
            'slug' => $employee->slug,
            'action' => 'restore',
        ]);

        $response->assertStatus(400)
                 ->assertJson(['message' => 'Employee is not deleted']);
    }

    public function test_07_validation_error_missing_slug()
    {
        $response = $this->postJson($this->endpoint, [
            'action' => 'active',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['slug']);
    }

    public function test_08_validation_error_missing_action()
    {
        $employee = Employee::factory()->create();

        $response = $this->postJson($this->endpoint, [
            'slug' => $employee->slug,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['action']);
    }

    public function test_09_validation_error_invalid_action()
    {
        $employee = Employee::factory()->create();

        $response = $this->postJson($this->endpoint, [
            'slug' => $employee->slug,
            'action' => 'invalid_action',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['action']);
    }
}

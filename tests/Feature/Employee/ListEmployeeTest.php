<?php

namespace Tests\Feature\Employee;

use Tests\TestCase;
use App\Models\Employee;
use App\Models\Personal;
use App\Models\PersonalUpdate;
use Illuminate\Foundation\Testing\WithFaker;
use App\Http\Controllers\APIs\EmployeeController;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ListEmployeeTest extends TestCase
{
    use RefreshDatabase;

    protected string $endpoint = 'gateway/employees';

    protected function setUp(): void
    {
        parent::setUp();
        // Seed related data if needed
    }

    public function test_returns_all_employees_by_default()
    {
        Employee::factory()->count(5)->create();

        $response = $this->postJson($this->endpoint);

        $response->assertStatus(200)
                 ->assertJsonStructure(['status', 'total', 'data']);
        $this->assertCount(5, $response->json('data'));
    }

    public function test_filters_by_status()
    {
        Employee::factory()->create(['status' => 'active']);
        Employee::factory()->create(['status' => 'resigned']);

        $response = $this->postJson($this->endpoint, [
            'status' => 'resigned',
        ]);

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('total'));
        $this->assertEquals('resigned', $response->json('data.0.status'));
    }

    public function test_filters_by_slugs()
    {
        $e1 = Employee::factory()->create();
        $e2 = Employee::factory()->create();

        $response = $this->postJson($this->endpoint, [
            'slugs' => [$e1->slug],
        ]);

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('total'));
        $this->assertEquals($e1->slug, $response->json('data.0.slug'));
    }

    public function test_filters_by_search_criteria()
    {
        Employee::factory()->create(['employee_name' => 'John Doe']);
        Employee::factory()->create(['employee_name' => 'Jane Doe']);

        $response = $this->postJson($this->endpoint, [
            'search' => ['employee_name' => 'john'],
        ]);

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('total'));
        $this->assertEquals('John Doe', $response->json('data.0.employee_name'));
    }

    public function test_orders_by_given_field_and_direction()
    {
        Employee::factory()->create(['employee_name' => 'Zebra']);
        Employee::factory()->create(['employee_name' => 'Apple']);

        $response = $this->postJson($this->endpoint, [
            'orderBy' => 'employee_name',
            'sortedBy' => 'asc',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Apple', $response->json('data.0.employee_name'));
    }

    public function test_applies_skip_and_limit()
    {
        Employee::factory()->count(10)->create();

        $response = $this->postJson($this->endpoint, [
            'skip' => 5,
            'limit' => 3,
        ]);

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_uses_latest_personal_update_if_available()
    {
        $personal = Personal::factory()->create();
        $employee = Employee::factory()->create(['personal_slug' => $personal->slug]);

        PersonalUpdate::factory()->create([
            'updatable_type' => Employee::class,
            'updatable_slug' => $employee->slug,
            'personal_slug' => $personal->slug,
            'full_name' => 'Updated Name',
        ]);

        $response = $this->postJson($this->endpoint);

        $response->assertStatus(200);
        $this->assertEquals('Updated Name', $response->json('data.0.personal.full_name'));
    }

    public function test_returns_validation_error_on_invalid_status()
    {
        $response = $this->postJson($this->endpoint, [
            'status' => 'not-a-valid-status',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['status']);
    }

    public function test_returns_validation_error_on_invalid_slugs_array()
    {
        $response = $this->postJson($this->endpoint, [
            'slugs' => 'not-an-array',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['slugs']);
    }

    public function test_returns_validation_error_on_invalid_order_by()
    {
        $response = $this->postJson($this->endpoint, [
            'orderBy' => 'invalid_field',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['orderBy']);
    }

    public function test_returns_validation_error_on_invalid_sorted_by()
    {
        $response = $this->postJson($this->endpoint, [
            'sortedBy' => 'wrong',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['sortedBy']);
    }

    public function test_fallbacks_to_personal_if_no_update()
    {
        $employee = Employee::factory()->create();
        $response = $this->postJson($this->endpoint);

        $response->assertStatus(200);
        $this->assertEquals($employee->personal->full_name, $response->json('data.0.personal.full_name'));
    }          
}

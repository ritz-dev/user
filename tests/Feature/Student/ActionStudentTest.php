<?php

namespace Tests\Feature\Student;

use Tests\TestCase;
use App\Models\Student;
use Tests\Feature\Student\ActionStudentTest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActionStudentTest extends TestCase
{
    use RefreshDatabase;

    protected string $endpoint = 'gateway/students/action'; // Change to your actual route

    public function setUp(): void
    {
        parent::setUp();
        // You might want to set up auth or middleware here if needed
    }

    public function test_requires_slug_and_valid_action()
    {
        $response = $this->postJson($this->endpoint, []);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['slug', 'action']);

        $response = $this->postJson($this->endpoint, [
            'slug' => 'non-existing-slug',
            'action' => 'invalid-action',
        ]);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['slug', 'action']);
    }

    public function test_can_set_status_to_enrolled()
    {
        $student = Student::factory()->create(['status' => 'graduated']);
        $response = $this->postJson($this->endpoint, [
            'slug' => $student->slug,
            'action' => 'enrolled',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Student status set to enrolled']);
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'status' => 'enrolled',
        ]);
    }

    public function test_can_set_status_to_graduated()
    {
        $student = Student::factory()->create();
        $response = $this->postJson($this->endpoint, [
            'slug' => $student->slug,
            'action' => 'graduated',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Student graduated']);
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'status' => 'graduated',
        ]);
    }

    public function test_can_set_status_to_suspended()
    {
        $student = Student::factory()->create();
        $response = $this->postJson($this->endpoint, [
            'slug' => $student->slug,
            'action' => 'suspended',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Student suspended']);
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'status' => 'suspended',
        ]);
    }

    public function test_can_set_status_to_inactive()
    {
        $student = Student::factory()->create();
        $response = $this->postJson($this->endpoint, [
            'slug' => $student->slug,
            'action' => 'inactive',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Student deactivated']);
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'status' => 'inactive',
        ]);
    }

    public function test_can_soft_delete_student()
    {
        $student = Student::factory()->create(['status' => 'enrolled']);
        $response = $this->postJson($this->endpoint, [
            'slug' => $student->slug,
            'action' => 'delete',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Student soft-deleted']);

        $this->assertSoftDeleted('students', ['id' => $student->id]);
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'status' => 'inactive',
        ]);
    }

    public function test_can_restore_soft_deleted_student()
    {
        $student = Student::factory()->create(['status' => 'inactive']);
        $student->delete();

        $response = $this->postJson($this->endpoint, [
            'slug' => $student->slug,
            'action' => 'restore',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Student restored']);

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'status' => 'enrolled',
            'deleted_at' => null,
        ]);
    }

    public function restore_returns_error_if_student_not_deleted()
    {
        $student = Student::factory()->create();

        $response = $this->postJson($this->endpoint, [
            'slug' => $student->slug,
            'action' => 'restore',
        ]);

        $response->assertStatus(400)
                 ->assertJson(['message' => 'Student is not deleted']);
    }

    public function test_returns_error_for_invalid_action()
    {
        $student = Student::factory()->create();

        $response = $this->postJson($this->endpoint, [
            'slug' => $student->slug,
            'action' => 'invalid-action',
        ]);

        // Actually, validation catches invalid action, so 422 expected
        $response->assertStatus(422);
    }
}

<?php

namespace Tests\Feature\Teacher;

use Tests\TestCase;
use App\Models\Teacher;

use Illuminate\Foundation\Testing\RefreshDatabase;

class ActionTeacherTest extends TestCase
{
    use RefreshDatabase;

    protected string $endpoint = 'gateway/teachers/action'; // Change to your actual route

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

    public function test_can_set_status_to_resigned()
    {
        $teacher = Teacher::factory()->create(['status' => 'active']);
        $response = $this->postJson($this->endpoint, [
            'slug' => $teacher->slug,
            'action' => 'resigned',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Teacher resigned']);
        $this->assertDatabaseHas('teachers', [
            'id' => $teacher->id,
            'status' => 'resigned',
        ]);
    }

    public function test_can_set_status_to_on_leave()
    {
        $teacher = Teacher::factory()->create();
        $response = $this->postJson($this->endpoint, [
            'slug' => $teacher->slug,
            'action' => 'on_leave',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Teacher is on leave']);
        $this->assertDatabaseHas('teachers', [
            'id' => $teacher->id,
            'status' => 'on_leave',
        ]);
    }

    public function test_can_set_status_to_active()
    {
        $teacher = Teacher::factory()->create();
        $response = $this->postJson($this->endpoint, [
            'slug' => $teacher->slug,
            'action' => 'active',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Teacher status set to active']);
        $this->assertDatabaseHas('teachers', [
            'id' => $teacher->id,
            'status' => 'active',
        ]);
    }

    public function test_can_soft_delete_teacher()
    {
        $teacher = Teacher::factory()->create(['status' => 'active']);
        $response = $this->postJson($this->endpoint, [
            'slug' => $teacher->slug,
            'action' => 'delete',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Teacher soft-deleted']);

        $this->assertSoftDeleted('teachers', ['id' => $teacher->id]);
        $this->assertDatabaseHas('teachers', [
            'id' => $teacher->id,
            'status' => 'resigned',
        ]);
    }

    public function test_can_restore_soft_deleted_teacher()
    {
        $teacher = Teacher::factory()->create(['status' => 'active']);
        $teacher->delete();

        $response = $this->postJson($this->endpoint, [
            'slug' => $teacher->slug,
            'action' => 'restore',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Teacher restored']);

        $this->assertDatabaseHas('teachers', [
            'id' => $teacher->id,
            'status' => 'active',
            'deleted_at' => null,
        ]);
    }

    public function restore_returns_error_if_teacher_not_deleted()
    {
        $teacher = Teacher::factory()->create();

        $response = $this->postJson($this->endpoint, [
            'slug' => $teacher->slug,
            'action' => 'restore',
        ]);

        $response->assertStatus(400)
                 ->assertJson(['message' => 'Teacher is not deleted']);
    }

    public function test_returns_error_for_invalid_action()
    {
        $teacher = Teacher::factory()->create();

        $response = $this->postJson($this->endpoint, [
            'slug' => $teacher->slug,
            'action' => 'invalid-action',
        ]);

        // Actually, validation catches invalid action, so 422 expected
        $response->assertStatus(422);
    }
}

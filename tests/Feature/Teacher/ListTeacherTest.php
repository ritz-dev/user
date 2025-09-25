<?php

namespace Tests\Feature\Teacher;

use Tests\TestCase;
use App\Models\Teacher;
use App\Models\Personal;
use App\Models\PersonalUpdate;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ListTeacherTest extends TestCase
{
    use RefreshDatabase;

    protected string $endpoint = 'gateway/teachers';

    public function test_list_all_teachers(): void
    {
        Teacher::factory()->count(3)->create();
        $response = $this->postJson($this->endpoint);
        $response->assertOk()
            ->assertJsonStructure(['status', 'total', 'data']);
        $this->assertCount(3, $response['data']);
    }

    public function test_filter_by_slugs(): void
    {
        $t1 = Teacher::factory()->create();
        $t2 = Teacher::factory()->create();

        $response = $this->postJson($this->endpoint, ['slugs' => [$t2->slug]]);
        $response->assertOk();
        $this->assertCount(1, $response['data']);
        $this->assertEquals($t2->slug, $response['data'][0]['slug']);
    }

    public function test_filter_by_status(): void
    {
        Teacher::factory()->create(['status' => 'active']);
        Teacher::factory()->create(['status' => 'resigned']);

        $response = $this->postJson($this->endpoint, ['status' => 'resigned']);
        $response->assertOk();
        $this->assertCount(1, $response['data']);
        $this->assertEquals('resigned', $response['data'][0]['status']);
    }

    public function test_search_by_fields(): void
    {
        Teacher::factory()->create(['teacher_name' => 'John Smith']);
        Teacher::factory()->create(['teacher_name' => 'Jane Doe']);

        $response = $this->postJson($this->endpoint, ['search' => ['teacher_name' => 'Jane']]);
        $response->assertOk();
        $this->assertCount(1, $response['data']);
        $this->assertEquals('Jane Doe', $response['data'][0]['teacher_name']);
    }

    public function test_results_are_ordered_by_teacher_name(): void
    {
        Teacher::factory()->create(['teacher_name' => 'Zara']);
        Teacher::factory()->create(['teacher_name' => 'Anna']);
        Teacher::factory()->create(['teacher_name' => 'Mike']);

        $response = $this->postJson($this->endpoint);
        $response->assertOk();

        $names = array_column($response['data'], 'teacher_name');
        $this->assertEquals(['Anna', 'Mike', 'Zara'], $names);
    }

    public function test_pagination_with_skip_and_limit(): void
    {
        Teacher::factory()->count(10)->create();

        $response = $this->postJson($this->endpoint, ['skip' => 5, 'limit' => 3]);
        $response->assertOk();
        $this->assertCount(3, $response['data']);
    }

    public function test_invalid_slugs_should_return_422(): void
    {
        $response = $this->postJson($this->endpoint, ['slugs' => 'not-an-array']);
        $response->assertStatus(422);
    }

    public function test_invalid_skip_should_return_422(): void
    {
        $response = $this->postJson($this->endpoint, ['skip' => -1]);
        $response->assertStatus(422);
    }

    public function test_invalid_limit_should_return_422(): void
    {
        $response = $this->postJson($this->endpoint, ['limit' => 999]);
        $response->assertStatus(422);
    }

    public function test_invalid_status_should_return_422(): void
    {
        $response = $this->postJson($this->endpoint, ['status' => 'invalid-status']);
        $response->assertStatus(422);
    }

    public function test_empty_result_should_return_empty_array(): void
    {
        Teacher::factory()->create(['teacher_name' => 'Bob']);

        $response = $this->postJson($this->endpoint, ['search' => ['' => 'NothingMatches']]);
        $response->assertOk();
    }

    public function test_personal_update_override(): void
    {
        $personal = Personal::factory()->create(['full_name' => 'Old Name']);
        $teacher = Teacher::factory()->create([
            'personal_slug' => $personal->slug,
        ]);

        PersonalUpdate::factory()->create([
            'personal_slug' => $personal->slug,
            'updatable_type' => Teacher::class,
            'updatable_slug' => $teacher->slug,
            'full_name' => 'Updated Name',
        ]);

        $response = $this->postJson($this->endpoint);
        $response->assertOk();
        $this->assertEquals('Updated Name', $response['data'][0]['personal']['full_name']);
    }
}

<?php

namespace Tests\Feature\Student;

use Tests\TestCase;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ListStudentTest extends TestCase
{
    use RefreshDatabase;

    protected string $endpoint = 'gateway/students';

    public function test_valid_with_structure()
    {
        Student::factory()->count(3)->create();

        $response = $this->postJson($this->endpoint);

        $response->assertStatus(200)
                ->assertJsonStructure(['data' => [
                    '*' => ['slug', 'student_name', 'student_number', 'status']
                ],
                'total']);
    }


    public function test_empty_when_no_students()
    {
        $response = $this->postJson($this->endpoint);

        $response->assertStatus(200)
                 ->assertJson(['data' => [], 'total' => 0]);
    }


    public function test_pagination()
    {
        \App\Models\Student::factory()->count(25)->create();

        $response = $this->postJson($this->endpoint, [
            'skip' => 2,
            'limit' => 10,
        ]);

        $response->assertStatus(200)
                ->assertJsonPath('data.0', fn ($item) => $item !== null);
    }

    public function test_sort_by()
    {
        \App\Models\Student::factory()->create(['student_name' => 'Zebra']);
        \App\Models\Student::factory()->create(['student_name' => 'Alpha']);

        $response = $this->postJson($this->endpoint, [
            'orderBy' => 'student_name',
            'sortedBy' => 'asc',
        ]);

        $response->assertStatus(200);
        $names = array_column($response->json('data'), 'student_name');
        $this->assertEquals('Alpha', $names[0]);
    }

    public function test_search_by()
    {
        \App\Models\Student::factory()->create(['status' => 'graduated']);
        \App\Models\Student::factory()->create(['status' => 'enrolled']);

        $response = $this->postJson($this->endpoint, [
            'search' => ['status' => 'graduated']
        ]);

        $response->assertStatus(200);
        foreach ($response->json('data') as $student) {
            $this->assertEquals('graduated', $student['status']);
        }
    }

    public function test_invalid_order_by()
    {
        $response = $this->postJson($this->endpoint, [
            'orderBy' => 'invalid_column'
        ]);

        $response->assertStatus(422);
    }

public function test_student_list_soft_deleted_are_excluded()
{
    $deleted = \App\Models\Student::factory()->create();
    $deleted->delete();

    $response = $this->postJson($this->endpoint);

    $response->assertStatus(200);
    $this->assertNotContains(
        $deleted->slug,
        array_column($response->json('data'), 'slug')
    );
}
}

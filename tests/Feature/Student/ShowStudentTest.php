<?php

namespace Tests\Feature\Student;

use Tests\TestCase;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Personal;
use App\Models\PersonalUpdate;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShowStudentTest extends TestCase
{
    use RefreshDatabase;

    protected string $endpoint = 'gateway/students/show';

    protected $student;
    protected $personal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->personal = Personal::factory()->create([
            'full_name' => 'John Doe',
        ]);

        $this->student = Student::factory()->create([
            'personal_slug' => $this->personal->slug,
            'student_name' => 'John Doe',
        ]);

        Guardian::factory()->create([
            'student_slug' => $this->student->slug,
            'personal_slug' => $this->personal->slug,
            'relation' => 'Father',
        ]);
    }

    public function test_show_student_success()
    {
        $response = $this->postJson($this->endpoint, [
            'slug' => $this->student->slug,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'slug',
                'student_name',
                'personal' => [
                    'slug', 'full_name', 'gender'
                ],
                'guardians' => [['slug', 'relation', 'full_name']],
            ]);
    }

    public function test_show_student_validation_error()
    {
        $this->postJson($this->endpoint, [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    }

    public function test_show_student_not_found()
    {
        $this->postJson($this->endpoint, ['slug' => 'invalid-slug'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['slug' => 'The selected slug is invalid.']);
    }

    public function test_show_student_with_no_guardians()
    {
        Guardian::query()->delete(); // Remove guardians

        $response = $this->postJson($this->endpoint, [
            'slug' => $this->student->slug,
        ]);

        $response->assertStatus(200);
        $this->assertIsArray($response['guardians']);
        $this->assertEmpty($response['guardians']);
    }

    public function test_show_student_ignores_extra_fields()
    {
        $response = $this->postJson($this->endpoint, [
            'slug' => $this->student->slug,
            'unexpected' => 'value',
            'extra' => 123,
        ]);

        $response->assertStatus(200);
        $this->assertArrayNotHasKey('unexpected', $response->json());
    }

    public function test_show_student_response_fields_are_correct()
    {
        $response = $this->postJson($this->endpoint, [
            'slug' => $this->student->slug,
        ]);

        $response->assertStatus(200);

        $data = $response->json();

        $this->assertIsString($data['slug']);
        $this->assertIsString($data['student_name']);
        $this->assertIsArray($data['guardians']);
    }
}

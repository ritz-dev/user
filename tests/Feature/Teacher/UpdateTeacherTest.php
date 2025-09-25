<?php

namespace Tests\Feature\Teacher;

use Tests\TestCase;
use App\Models\Teacher;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateTeacherTest extends TestCase
{
    use RefreshDatabase;

    protected string $endpoint = 'gateway/teachers/update'; // Adjust route as needed

    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'teacher_code' => 'TCH' . Str::random(5),
            'email' => 'teacher@example.com',
            'phone' => '0912345678',
            'address' => '123 Main St',
            'qualification' => 'Bachelor of Education',
            'subject' => 'Mathematics',
            'experience_years' => 5,
            'salary' => 1500.50,
            'hire_date' => '2023-01-01',
            'status' => 'active',
            'employment_type' => 'fulltime',
            'full_name' => 'John Doe',
            'birth_date' => '1990-05-10',
            'gender' => 'male',
            'region_code' => 'RC123',
            'township_code' => 'TC456',
            'citizenship' => 'Myanmar',
            'serial_number' => 'SN789',
            'nationality' => 'Burmese',
            'religion' => 'Buddhism',
            'blood_type' => 'A+',
        ], $overrides);
    }

    public function test_requires_mandatory_fields()
    {
        $response = $this->postJson($this->endpoint, []);
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'slug', 'teacher_code', 'salary', 'hire_date', 'status', 'employment_type',
                'full_name', 'birth_date', 'gender', 'region_code', 'township_code', 'citizenship', 'serial_number'
            ]);
    }

    public function test_validates_enum_fields_correctly()
    {
        $teacher = Teacher::factory()->create();
        $payload = $this->validPayload([
            'slug' => $teacher->slug,
            'status' => 'invalid_status',
            'employment_type' => 'invalid_type',
            'gender' => 'unknown_gender',
            'blood_type' => 'invalid_blood',
        ]);
        $response = $this->postJson($this->endpoint, $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status', 'employment_type', 'gender', 'blood_type']);
    }

    public function test_rejects_duplicate_teacher_code()
    {
        $teacher1 = Teacher::factory()->create(['teacher_code' => 'TCHDUPL1']);
        $teacher2 = Teacher::factory()->create();

        $payload = $this->validPayload([
            'slug' => $teacher2->slug,
            'teacher_code' => 'TCHDUPL1', // duplicate code from teacher1
        ]);
        $response = $this->postJson($this->endpoint, $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors('teacher_code');
    }

    public function test_rejects_duplicate_email()
    {
        $teacher1 = Teacher::factory()->create(['email' => 'dup@example.com']);
        $teacher2 = Teacher::factory()->create();

        $payload = $this->validPayload([
            'slug' => $teacher2->slug,
            'email' => 'dup@example.com',
        ]);
        $response = $this->postJson($this->endpoint, $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_rejects_duplicate_phone()
    {
        $teacher1 = Teacher::factory()->create(['phone' => '0912345678']);
        $teacher2 = Teacher::factory()->create();

        $payload = $this->validPayload([
            'slug' => $teacher2->slug,
            'phone' => '0912345678',
        ]);
        $response = $this->postJson($this->endpoint, $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors('phone');
    }

    public function test_successfully_updates_teacher_and_personal()
    {
        $teacher = Teacher::factory()->create();
        $personal = $teacher->personal;

        $payload = $this->validPayload([
            'slug' => $teacher->slug,
            'teacher_code' => 'NEWCODE123',
            'email' => 'newemail@example.com',
            'phone' => '0999888777',
            'full_name' => 'Updated Name',
            'birth_date' => '2021-03-01',
            'gender' => 'female',
            'region_code' => 'NEWRC',
            'township_code' => 'NEWTW',
            'citizenship' => 'New Citizenship',
            'serial_number' => 'NEWSN',
            'nationality' => 'New Nationality',
            'religion' => 'New Religion',
            'blood_type' => 'B+',
        ]);

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'teacher_code' => 'NEWCODE123',
                'email' => 'newemail@example.com',
                'phone' => '0999888777',
                'teacher_name' => 'Updated Name',
                'birth_date' => '2021-03-01'
            ]);

        // Reload teacher and personal
        $teacher->refresh();

        $this->assertEquals('NEWCODE123', $teacher->teacher_code);
        $this->assertEquals('newemail@example.com', $teacher->email);
        $this->assertEquals('0999888777', $teacher->phone);
        $this->assertEquals('Updated Name', $teacher->teacher_name);

        $this->assertEquals('2021-03-01', $teacher->personal->birth_date);
        $this->assertEquals('female', $teacher->personal->gender);
        $this->assertEquals('NEWRC', $teacher->personal->region_code);
        $this->assertEquals('NEWTW', $teacher->personal->township_code);
        $this->assertEquals('New Citizenship', $teacher->personal->citizenship);
        $this->assertEquals('NEWSN', $teacher->personal->serial_number);
        $this->assertEquals('New Nationality', $teacher->personal->nationality);
        $this->assertEquals('New Religion', $teacher->personal->religion);
        $this->assertEquals('B+', $teacher->personal->blood_type);
    }

    public function test_does_not_create_personal_update_if_no_personal_change()
    {
        $teacher = Teacher::factory()->create();
        $personal = $teacher->personal;

        $payload = $this->validPayload([
            'slug' => $teacher->slug,
            'full_name' => $personal->full_name,
            'birth_date' => $personal->birth_date,
            'gender' => $personal->gender,
            'region_code' => $personal->region_code,
            'township_code' => $personal->township_code,
            'citizenship' => $personal->citizenship,
            'serial_number' => $personal->serial_number,
            'nationality' => $personal->nationality,
            'religion' => $personal->religion,
            'blood_type' => $personal->blood_type,
        ]);

        $this->assertDatabaseCount('personal_updates', 0);

        $this->postJson($this->endpoint, $payload)
            ->assertStatus(200);

        $this->assertDatabaseCount('personal_updates', 0);
    }

    public function test_returns_404_if_teacher_not_found()
    {
        $payload = $this->validPayload([
            'slug' => 'non-existing-slug',
        ]);

        $response = $this->postJson($this->endpoint, $payload);
        $response->assertJsonValidationErrors(['slug']);

    }
}

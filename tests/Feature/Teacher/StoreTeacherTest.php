<?php

namespace Tests\Feature\Teacher;

use Tests\TestCase;
use App\Models\Teacher;
use App\Models\Personal;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StoreTeacherTest extends TestCase
{
   use RefreshDatabase;

    protected string $endpoint = 'gateway/teachers/store'; // adjust if your route differs

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
            'salary' => '1500.50',
            'hire_date' => '2023-01-01T00:00:00.000000Z',
            'status' => 'active',
            'employment_type' => 'fulltime',
            'personal' => [
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
            ],
        ], $overrides);
    }

    public function test_requires_mandatory_fields()
    {
        $response = $this->postJson($this->endpoint, []);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'teacher_code',
                    'salary',
                    'hire_date',
                    'status',
                    'employment_type',
                    'personal.full_name',
                    'personal.gender',
                    'personal.region_code',
                    'personal.township_code',
                    'personal.citizenship',
                    'personal.serial_number',
                 ]);
    }

    public function test_validates_enum_fields_correctly()
    {
        $payload = $this->validPayload([
            'status' => 'invalid_status',
            'employment_type' => 'invalid_type',
            'personal' => [
                'full_name' => 'Jane Doe',
                'gender' => 'unknown_gender',
                'region_code' => 'RC1',
                'township_code' => 'TC1',
                'citizenship' => 'CountryX',
                'serial_number' => 'SN123',
            ],
        ]);

        $response = $this->postJson($this->endpoint, $payload);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['status', 'employment_type', 'personal.gender']);
    }

    public function test_rejects_duplicate_teacher_code()
    {
        $existingTeacher = Teacher::factory()->create([
            'teacher_code' => 'TCHDUPL',
        ]);

        $payload = $this->validPayload(['teacher_code' => 'TCHDUPL']);
        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('teacher_code');
    }

    public function test_rejects_duplicate_email()
    {
        $existingTeacher = Teacher::factory()->create([
            'email' => 'teacher@example.com',
        ]);

        $payload = $this->validPayload(['email' => 'teacher@example.com']);
        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('email');
    }

    public function test_rejects_duplicate_phone()
    {
        $existingTeacher = Teacher::factory()->create([
            'phone' => '0912345678',
        ]);

        $payload = $this->validPayload(['phone' => '0912345678']);
        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('phone');
    }

    public function test_rejects_invalid_email_format()
    {
        $payload = $this->validPayload(['email' => 'not-an-email']);
        $response = $this->postJson($this->endpoint, $payload);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors('email');
    }

    public function test_rejects_negative_salary_or_experience()
    {
        $payload = $this->validPayload([
            'salary' => -100,
            'experience_years' => -2,
        ]);

        $response = $this->postJson($this->endpoint, $payload);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['salary', 'experience_years']);
    }

    public function test_rejects_invalid_date_formats()
    {
        $payload = $this->validPayload([
            'hire_date' => 'not-a-date',
            'personal' => [
                'full_name' => 'John Doe',
                'birth_date' => 'invalid-date',
                'gender' => 'male',
                'region_code' => 'RC123',
                'township_code' => 'TC456',
                'citizenship' => 'Myanmar',
                'serial_number' => 'SN789',
            ],
        ]);

        $response = $this->postJson($this->endpoint, $payload);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['hire_date', 'personal.birth_date']);
    }

    public function test_creates_teacher_and_personal_when_personal_does_not_exist()
    {
        $payload = $this->validPayload();

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(201)
                 ->assertJson([
                    'message' => 'Teacher created successfully.',
                    'data' => [
                        'teacher_code' => $payload['teacher_code'],
                        'email' => $payload['email'],
                        'phone' => $payload['phone'],
                        'address' => $payload['address'],
                        'qualification' => $payload['qualification'],
                        'subject' => $payload['subject'],
                        'experience_years' => $payload['experience_years'],
                        'salary' => $payload['salary'],
                        'hire_date' => $payload['hire_date'],
                        'status' => $payload['status'],
                        'employment_type' => $payload['employment_type'],
                    ],
                 ]);

        $this->assertDatabaseHas('personals', [
            'full_name' => $payload['personal']['full_name'],
            'region_code' => $payload['personal']['region_code'],
            'township_code' => $payload['personal']['township_code'],
            'serial_number' => $payload['personal']['serial_number'],
            'citizenship' => $payload['personal']['citizenship'],
        ]);

        $this->assertDatabaseHas('teachers', [
            'teacher_code' => $payload['teacher_code'],
            'email' => $payload['email'],
        ]);
    }

    public function test_creates_teacher_with_existing_personal()
    {
        $personal = Personal::factory()->create([
            'region_code' => 'RC123',
            'township_code' => 'TC456',
            'serial_number' => 'SN789',
            'citizenship' => 'Myanmar',
        ]);

        $payload = $this->validPayload([
            'personal' => [
                'full_name' => 'Ignored Name', // should link existing personal, so name ignored
                'region_code' => $personal->region_code,
                'township_code' => $personal->township_code,
                'serial_number' => $personal->serial_number,
                'citizenship' => $personal->citizenship,
                'gender' => 'male',
            ]
        ]);

        $response = $this->postJson($this->endpoint, $payload);
        $response->assertStatus(201);

        $this->assertDatabaseHas('teachers', [
            'teacher_code' => $payload['teacher_code'],
            'personal_slug' => $personal->slug,
        ]);
    }
}

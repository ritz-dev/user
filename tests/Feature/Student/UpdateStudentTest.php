<?php

namespace Tests\Feature\Student;

use Tests\TestCase;
use App\Models\Student;
use App\Models\PersonalUpdate;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateStudentTest extends TestCase
{
    use RefreshDatabase;

    protected string $endpoint = 'gateway/students/update';

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'slug' => '', // required, override in tests
            'student_number' => 'STU-001',
            'registration_number' => 'REG-001',
            'school_name' => 'Green Valley High',
            'school_code' => 'GVH-2025',
            'email' => 'student@example.com',
            'phone' => '0912345678',
            'address' => 'Yangon',
            'status' => 'enrolled',
            'graduation_date' => '2030-03-10',
            'admission_date' => '2025-06-01',
            'personal' => [
                'full_name' => 'Aung Aung',
                'gender' => 'male',
                'birth_date' => '2010-01-01',
                'region_code' => '12',
                'township_code' => '06',
                'serial_number' => '123456',
                'nationality' => 'Burmese',
                'citizenship' => 'Myanmar',
                'religion' => 'Buddhism',
                'blood_type' => 'O+',
            ],
            'guardians' => [
                [
                    'full_name' => 'Daw Mya Mya',
                    'birth_date' => '1980-05-15',
                    'region_code' => '12',
                    'township_code' => '06',
                    'serial_number' => '654321',
                    'citizenship' => 'Myanmar',
                    'relation' => 'Mother',
                    'occupation' => 'Teacher',
                    'phone' => '09987654321',
                ]
            ],
        ], $overrides);
    }

    public function test_student_successfully(): void
    {
        $student = Student::factory()->create();

        $payload = $this->validPayload([
            'slug' => $student->slug,
            'student_number' => 'STU-002',
            'school_name' => 'Updated School',
        ]);

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => [
                    'slug',
                    'student_name',
                    'student_number',
                    'registration_number',
                    'school_name',
                    'school_code',
                    'email',
                    'phone',
                    'address',
                    'status',
                    'graduation_date',
                    'admission_date',
                    'personal',
                    'guardians',
                ],
            ]);

        $this->assertDatabaseHas('students', [
            'slug' => $student->slug,
            'student_number' => 'STU-002',
            'school_name' => 'Updated School',
        ]);
    }

    public function test_requires_student_slug(): void
    {
        $payload = $this->validPayload();
        unset($payload['slug']);

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    }

    public function test_update_fails_if_student_slug_is_invalid(): void
    {
        $payload = $this->validPayload(['slug' => 'invalid-slug-xyz']);

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    }

    public function test_update_rejects_invalid_email(): void
    {
        $student = Student::factory()->create();

        $payload = $this->validPayload([
            'slug' => $student->slug,
            'email' => 'not-an-email',
        ]);

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_update_fails_with_duplicate_student_number(): void
    {
        $existing = Student::factory()->create(['student_number' => 'STU-001']);
        $student = Student::factory()->create();

        $payload = $this->validPayload([
            'slug' => $student->slug,
            'student_number' => 'STU-001',
        ]);

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['student_number']);
    }

    public function test_update_accepts_no_guardians(): void
    {
        $student = Student::factory()->create();

        $payload = $this->validPayload([
            'slug' => $student->slug,
        ]);

        // Remove guardians
        unset($payload['guardians']);

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertOk()
            ->assertJsonFragment(['slug' => $student->slug]);
    }

    public function test_update_ignores_unexpected_fields(): void
    {
        $student = Student::factory()->create();

        $payload = $this->validPayload([
            'slug' => $student->slug,
            'unexpected_field' => 'unexpected value',
            'personal' => array_merge($this->validPayload()['personal'], ['extra_personal' => 'value']),
        ]);

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertOk()
            ->assertJsonFragment(['slug' => $student->slug])
            ->assertJsonMissing(['unexpected_field']);
    }

    public function test_update_returns_updated_structure(): void
    {
        $student = Student::factory()->create();

        $payload = $this->validPayload([
            'slug' => $student->slug,
            'student_number' => 'STU-003',
        ]);

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => [
                    'slug',
                    'student_name',
                    'student_number',
                    'registration_number',
                    'school_name',
                    'school_code',
                    'email',
                    'phone',
                    'address',
                    'status',
                    'graduation_date',
                    'admission_date',
                    'personal',
                    'guardians',
                ],
            ]);
    }
}

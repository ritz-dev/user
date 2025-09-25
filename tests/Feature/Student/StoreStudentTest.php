<?php

namespace Tests\Feature\Student;

use Tests\TestCase;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StoreStudentTest extends TestCase
{
    use RefreshDatabase;

    protected string $endpoint = 'gateway/students/store';

    public function test_store_creates_student_successfully(): void
    {
        $payload = [
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
                    'relation' => 'mother',
                    'occupation' => 'Teacher',
                    'phone' => '09987654321',
                ]
            ],
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertCreated()
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
                     ]
                 ]);

        $this->assertDatabaseHas('students', [
            'student_number' => 'STU-001',
            'school_name' => 'Green Valley High',
        ]);

        $this->assertDatabaseHas('personals', [
            'full_name' => 'Aung Aung',
            'gender' => 'male',
        ]);

        $this->assertDatabaseHas('guardians', [
            'name' => 'Daw Mya Mya',
            'relation' => 'Mother',
        ]);
    }

    public function test_store_requires_required_fields(): void
    {
        $response = $this->postJson($this->endpoint, []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'student_number',
                'school_name',
                'status',
                'personal.full_name',
                'personal.gender',
                'personal.region_code',
                'personal.township_code',
                'personal.serial_number',
                'personal.nationality',
                'personal.citizenship',
            ]);
    }

    public function test_store_rejects_invalid_email(): void
    {
        $payload = $this->validPayload(['email' => 'invalid-email']);

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_store_accepts_no_guardians(): void
    {
        $payload = $this->validPayload();
        unset($payload['guardians']);

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['guardians']);
    }

    public function test_store_fails_with_duplicate_student_number(): void
    {
        Student::factory()->create([
            'student_number' => 'STU-001',
        ]);

        $payload = $this->validPayload(['student_number' => 'STU-001']);

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['student_number']);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
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
            ]
        ], $overrides);
    }
}

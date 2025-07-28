<?php

namespace Tests\Feature\Teacher;

use Tests\TestCase;
use App\Models\Teacher;
use App\Models\Personal;
use App\Models\PersonalUpdate;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShowTeacherTest extends TestCase
{
    use RefreshDatabase;

    protected string $endpoint = 'gateway/teachers/show';

    public function test_show_teacher_details_success(): void
    {
        $personal = Personal::factory()->create([
            'full_name' => 'John Doe',
            'birth_date' => '1980-06-15',
            'gender' => 'male',
            'region_code' => '01',
            'township_code' => '02',
            'citizenship' => 'CountryX',
            'serial_number' => 'A123456',
            'nationality' => 'NationalityX',
            'religion' => 'ReligionX',
            'blood_type' => 'O+',
        ]);

        $teacher = Teacher::factory()->create([
            'slug' => 'teacher-slug-123',
            'teacher_name' => 'John Doe',
            'teacher_code' => 'T-001',
            'email' => 'john@example.com',
            'phone' => '0912345678',
            'address' => 'Some Address',
            'qualification' => 'MSc',
            'subject' => 'Math',
            'experience_years' => 10,
            'salary' => 1500,
            'hire_date' => '2015-01-10',
            'status' => 'active',
            'employment_type' => 'fulltime',
            'personal_slug' => $personal->slug,
        ]);

        $response = $this->postJson($this->endpoint, ['slug' => $teacher->slug]);

        $response->assertOk()
            ->assertJson([
                'slug' => $teacher->slug,
                'teacher_name' => 'John Doe',
                'teacher_code' => 'T-001',
                'email' => 'john@example.com',
                'phone' => '0912345678',
                'address' => 'Some Address',
                'qualification' => 'MSc',
                'subject' => 'Math',
                'experience_years' => 10,
                'salary' => '1500.00',
                'hire_date' => '2015-01-10',
                'status' => 'active',
                'employment_type' => 'fulltime',
                'personal' => [
                    'full_name' => 'John Doe',
                    'birth_date' => '1980-06-15',
                    'gender' => 'male',
                    'region_code' => '01',
                    'township_code' => '02',
                    'citizenship' => 'CountryX',
                    'serial_number' => 'A123456',
                    'nationality' => 'NationalityX',
                    'religion' => 'ReligionX',
                    'blood_type' => 'O+',
                ],
            ]);
    }

    public function test_validation_slug_is_required(): void
    {
        $response = $this->postJson($this->endpoint, []);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    }

    public function test_validation_slug_must_exist(): void
    {
        $response = $this->postJson($this->endpoint, ['slug' => 'non-existing-slug']);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    }

    public function test_returns_404_if_teacher_not_found(): void
    {
        // Bypass validation by creating a teacher then deleting it to simulate missing record
        $teacher = Teacher::factory()->create();
        $slug = $teacher->slug;
        $teacher->delete();

        // To skip validation, forcibly post the slug (if you want to test 404 explicitly)
        $response = $this->postJson($this->endpoint, ['slug' => $slug]);
        $response->assertStatus(404)
            ->assertJson([
                'status' => 'Not Found',
                'message' => 'Teacher not found',
            ]);
    }

    public function test_personal_update_override(): void
    {
        $personal = Personal::factory()->create([
            'full_name' => 'Old Name',
            'birth_date' => '1980-06-15',
            'gender' => 'male',
            'region_code' => '01',
            'township_code' => '02',
            'citizenship' => 'CountryX',
            'serial_number' => 'A123456',
            'nationality' => 'NationalityX',
            'religion' => 'ReligionX',
            'blood_type' => 'O+',
        ]);

        $teacher = Teacher::factory()->create([
            'personal_slug' => $personal->slug,
            'slug' => 'teacher-uuid-1',
        ]);

        $update = PersonalUpdate::factory()->create([
            'updatable_type' => Teacher::class,
            'updatable_slug' => $teacher->slug,
            'personal_slug' => $personal->slug,
            'full_name' => 'Updated Name',
            'birth_date' => '1985-01-01',
            'gender' => 'male',
            'region_code' => '01',
            'township_code' => '02',
            'citizenship' => 'CountryX',
            'serial_number' => 'A123456',
            'nationality' => 'NationalityX',
            'religion' => 'Updated Religion',
            'blood_type' => 'A+',
        ]);

        $response = $this->postJson($this->endpoint, ['slug' => $teacher->slug]);
        $response->assertOk();
        $this->assertEquals('Updated Name', $response['personal']['full_name']);
        $this->assertEquals('1985-01-01', $response['personal']['birth_date']);
        $this->assertEquals('Updated Religion', $response['personal']['religion']);
        $this->assertEquals('A+', $response['personal']['blood_type']);
    }

    public function test_date_formats_in_response(): void
    {
        $personal = Personal::factory()->create(['birth_date' => '1980-06-15']);
        $teacher = Teacher::factory()->create([
            'personal_slug' => $personal->slug,
            'hire_date' => '2010-05-20 15:30:00',
        ]);

        $response = $this->postJson($this->endpoint, ['slug' => $teacher->slug]);
        $response->assertOk();


    }

    public function test_salary_formatting(): void
    {
        $personal = Personal::factory()->create();
        $teacher = Teacher::factory()->create([
            'personal_slug' => $personal->slug,
            'salary' => 1234.5,
        ]);

        $response = $this->postJson($this->endpoint, ['slug' => $teacher->slug]);
        $response->assertOk();
        $this->assertEquals('1234.50', $response['salary']);
    }

    public function test_personal_fields_are_present(): void
    {
        $personal = Personal::factory()->create();
        $teacher = Teacher::factory()->create([
            'personal_slug' => $personal->slug,
        ]);

        $response = $this->postJson($this->endpoint, ['slug' => $teacher->slug]);
        $response->assertOk();

        $expectedKeys = [
            'full_name',
            'birth_date',
            'gender',
            'region_code',
            'township_code',
            'citizenship',
            'serial_number',
            'nationality',
            'religion',
            'blood_type',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $response['personal']);
        }
    }
}

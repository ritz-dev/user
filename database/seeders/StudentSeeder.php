<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Guardian;
use App\Models\Personal;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $studentPersonals = Personal::take(20)->get();
        $guardianPersonals = Personal::skip(20)->take(20)->get();

        foreach ($studentPersonals as $index => $personal) {
            $customId = generateCustomId($index);

            $student = Student::create([
                'slug' => $customId, // Custom slug based on index
                'personal_slug' => $personal->slug,
                'student_name' => $personal->full_name,
                'student_number' => 'STU' . str_pad($index + 1, 5, '0', STR_PAD_LEFT),
                'registration_number' => 'REG2024' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'school_name' => 'Demo High School',
                'school_code' => 'DHS001',
                'email' => 'student' . ($index + 1) . '@example.com',
                'phone' => '+959100000' . ($index + 1),
                'address' => 'Yangon Region',
                'status' => 'enrolled',
                'admission_date' => '2020-06-01',
                'graduation_date' => null,
            ]);

            // Assign one guardian to each student
            $guardianPersonal = $guardianPersonals[$index];

            Guardian::create([
                'slug' => $customId, // Custom slug for guardian
                'student_slug' => $student->slug,
                'personal_slug' => $guardianPersonal->slug,
                'relation' => 'father', // or 'mother', 'guardian'
                'name' => $guardianPersonal->full_name,
                'occupation' => 'Government Staff',
                'phone' => '09200000' . ($index + 1),
                'email' => 'guardian' . ($index + 1) . '@example.com',
            ]);
        }

    }
}

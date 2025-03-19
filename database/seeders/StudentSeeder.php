<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Personal;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $personals = Personal::skip(2)->take(1)->get();

        // Student::create([
        //     'slug' => Str::uuid(),
        //     'personal_id' => $personals[0]->id,
        //     'name' => $personals[0]->name,
        //     'student_code' => $personals[0]->state. '/' . $personals[0]->district . '(N)' . $personals[0]->register_code,
        //     'address' => 'adcawdcad',
        //     'email' => 'data@gmail.com',
        //     'phonenumber' => '09983212354',
        //     'pob' => 'YGN',
        //     'nationality' => 'Myanmar',
        //     'religion' => 'Burma',
        //     'blood_type' => 'A',
        //     'status' => 'active',
        //     'academic_level' => 'Primary A',
        //     'academic_year' => '2022-2023',
        //     'enrollment_date' => '2024-12-16',
        //     'graduation_date' => null
        // ]);

        Student::factory()->count(30)->create();

    }
}

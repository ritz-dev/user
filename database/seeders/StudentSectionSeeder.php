<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Support\Str;
use App\Models\StudentSection;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StudentSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = Student::select('id')->get();

        foreach($students as $student){
            $student_section = new StudentSection;
            $student_section->slug = Str::uuid();
            $student_section->student_id = $student->id;
            $student_section->section_id = "915c85ef-7781-4358-88fd-b65494980a2a";
            $student_section->save();
        }
    }
}

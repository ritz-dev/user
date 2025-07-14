<?php

namespace Database\Seeders;

use App\Models\Teacher;
use App\Models\Personal;
use Illuminate\Database\Seeder;


class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teacherPersonals = Personal::skip(45)->take(5)->get();
        
        foreach ($teacherPersonals as $index => $personal) {
            $customId = generateCustomId($index);

            $teacher = Teacher::create([
                'slug' => $customId,
                'personal_slug' => $personal->slug,
                'teacher_name' => $personal->full_name,
                'teacher_code' => 'TCH' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'email' => 'teacher' . ($index + 1) . '@example.com',
                'phone' => '+959100000' . ($index + 1),
                'address' => 'Yangon Region',
                'qualification' => 'B.Ed',
                'subject' => 'English',
                'experience_years' => 3,
                'salary' => 30000.00,
                'hire_date' => now(),
                'status' => 'active',
                'employment_type' => 'fulltime',
            ]);
        }
    }
}

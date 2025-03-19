<?php

namespace Database\Seeders;

use App\Models\Teacher;
use App\Models\Employee;
use App\Models\Personal;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $personal = Personal::skip(1)->take(1)->first();

        if (!$personal) {
            throw new \Exception("Personal record not found at the specified index.");
        }

        Teacher::create([
            'slug' => '915c85ef-7781-4358-88fd-b65494980a2a',
            'personal_id' => 1,
            'teacher_code' => 'TCH-001',
            'email' => 'testing@gmail.com',
            'phonenumber' => '09799123123',
            'department' => "English",
            "salary" => 1500000,
            "hire_date" => "2025-03-17",
            "status" => "active",
            "employment_type" => "full-time",
            'specialization' => 'Eng',
            'designation' => 'Teacher',
        ]);

        // Teacher::factory()->count(9)->create();
    }
}

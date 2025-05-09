<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Personal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = [
            [
                'full_name' => 'Aung Aung',
                'gender' => 'male',
                'birth_date' => '2005-01-10',
                'student_number' => 'STU00001',
                'email' => 'aung.aung@example.com',
            ],
            [
                'full_name' => 'Ma Ma',
                'gender' => 'female',
                'birth_date' => '2006-02-15',
                'student_number' => 'STU00002',
                'email' => 'ma.ma@example.com',
            ],
            [
                'full_name' => 'Hla Hla',
                'gender' => 'female',
                'birth_date' => '2004-07-22',
                'student_number' => 'STU00003',
                'email' => 'hla.hla@example.com',
            ],
            [
                'full_name' => 'Zaw Zaw',
                'gender' => 'male',
                'birth_date' => '2003-11-03',
                'student_number' => 'STU00004',
                'email' => 'zaw.zaw@example.com',
            ],
            [
                'full_name' => 'Mya Mya',
                'gender' => 'female',
                'birth_date' => '2007-08-30',
                'student_number' => 'STU00005',
                'email' => 'mya.mya@example.com',
            ],
            [
                'full_name' => 'Ko Ko',
                'gender' => 'male',
                'birth_date' => '2002-12-25',
                'student_number' => 'STU00006',
                'email' => 'ko.ko@example.com',
            ],
            [
                'full_name' => 'Nay Nay',
                'gender' => 'male',
                'birth_date' => '2005-06-18',
                'student_number' => 'STU00007',
                'email' => 'nay.nay@example.com',
            ],
            [
                'full_name' => 'Su Su',
                'gender' => 'female',
                'birth_date' => '2006-09-12',
                'student_number' => 'STU00008',
                'email' => 'su.su@example.com',
            ],
            [
                'full_name' => 'Tun Tun',
                'gender' => 'male',
                'birth_date' => '2004-05-05',
                'student_number' => 'STU00009',
                'email' => 'tun.tun@example.com',
            ],
            [
                'full_name' => 'Hnin Hnin',
                'gender' => 'female',
                'birth_date' => '2003-03-17',
                'student_number' => 'STU00010',
                'email' => 'hnin.hnin@example.com',
            ],
        ];

        foreach ($students as $index => $data) {
            $serial = str_pad((string)($index + 1), 10, '0', STR_PAD_LEFT);

            $personal = Personal::create([
                'slug' => Str::uuid(),
                'full_name' => $data['full_name'],
                'birth_date' => $data['birth_date'],
                'gender' => $data['gender'],
                'region_code' => 'YGN',
                'township_code' => 'TSP001',
                'citizenship' => 'MMR',
                'serial_number' => $serial,
                'nationality' => 'Myanmar',
                'religion' => 'Buddhism',
                'blood_type' => 'O+',
            ]);

            Student::create([
                'slug' => Str::uuid(),
                'personal_id' => $personal->id,
                'student_number' => $data['student_number'],
                'registration_number' => 'REG2024' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'school_name' => 'Yangon High School',
                'school_code' => 'YHS001',
                'email' => $data['email'],
                'phone' => '09123456' . str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT),
                'address' => 'Yangon Region',
                'status' => 'enrolled',
                'admission_date' => '2020-06-01',
                'graduation_date' => null,
            ]);
        }

    }
}

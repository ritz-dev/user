<?php

namespace Database\Seeders;

use App\Models\Personal;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;


class PersonalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Personal::create([
            'slug' => Str::uuid(),  // Use UUID for personal record
            'name' => 'Aung',
            'gender' => 'male',
            'dob' => '1994-05-22',
            'address' => "Ygn",
            'state' => "12",
            'district' => "THAGAKA",
            'register_code' => "174505",
        ]);

        Personal::create([
            'slug' => Str::uuid(),  // Use UUID for personal record
            'name' => 'Kyaw',
            'gender' => 'male',
            'dob' => '1997-05-22',
            'address' => "Ygn",
            'state' => "12",
            'district' => "THAGAKA",
            'register_code' => "145567",
        ]);

        Personal::create([
            'slug' => Str::uuid(),  // Use UUID for personal record
            'name' => 'Thu',
            'gender' => 'male',
            'dob' => '2000-05-22',
            'address' => "Ygn",
            'state' => "12",
            'district' => "THAGAKA",
            'register_code' => "135564",
        ]);

        Personal::create([
            'slug' => Str::uuid(),  // Use UUID for personal record
            'name' => 'Mya',
            'gender' => 'female',
            'dob' => '2000-05-22',
            'address' => "Ygn",
            'state' => "12",
            'district' => "AAA",
            'register_code' => "123456",
        ]);

        Personal::create([
            'slug' => Str::uuid(),  // Use UUID for personal record
            'name' => 'Aye',
            'gender' => 'female',
            'dob' => '2000-05-22',
            'address' => "Ygn",
            'state' => "12",
            'district' => "BBB",
            'register_code' => "123455",
        ]);

        Personal::create([
            'slug' => Str::uuid(),  // Use UUID for personal record
            'name' => 'Thuzar',
            'gender' => 'female',
            'dob' => '2000-05-22',
            'address' => "Ygn",
            'state' => "12",
            'district' => "CCC",
            'register_code' => "123454",
        ]);

        Personal::create([
            'slug' => Str::uuid(),  // Use UUID for personal record
            'name' => 'Thuta',
            'gender' => 'male',
            'dob' => '2000-05-22',
            'address' => "Ygn",
            'state' => "12",
            'district' => "DDD",
            'register_code' => "123457",
        ]);

        Personal::create([
            'slug' => Str::uuid(),  // Use UUID for personal record
            'name' => 'Thu Thu',
            'gender' => 'female',
            'dob' => '2000-05-22',
            'address' => "Ygn",
            'state' => "12",
            'district' => "THAGAKA",
            'register_code' => "123458",
        ]);

        Personal::create([
            'slug' => Str::uuid(),  // Use UUID for personal record
            'name' => 'Moe',
            'gender' => 'male',
            'dob' => '2000-05-22',
            'address' => "Ygn",
            'state' => "12",
            'district' => "EEE",
            'register_code' => "111122",
        ]);

        Personal::create([
            'slug' => Str::uuid(),  // Use UUID for personal record
            'name' => 'Moe Thu',
            'gender' => 'male',
            'dob' => '2000-05-22',
            'address' => "Ygn",
            'state' => "12",
            'district' => "FFF",
            'register_code' => "112211",
        ]);

        Personal::create([
            'slug' => Str::uuid(),  // Use UUID for personal record
            'name' => 'Hlaing',
            'gender' => 'female',
            'dob' => '2000-05-22',
            'address' => "Ygn",
            'state' => "12",
            'district' => "GGG",
            'register_code' => "123456",
        ]);

        Personal::create([
            'slug' => Str::uuid(),  // Use UUID for personal record
            'name' => 'Thar',
            'gender' => 'male',
            'dob' => '2000-05-22',
            'address' => "Ygn",
            'state' => "12",
            'district' => "HHH",
            'register_code' => "123456",
        ]);

    }
}

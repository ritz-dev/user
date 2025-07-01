<?php

namespace Database\Seeders;

use App\Models\Personal;
use Illuminate\Database\Seeder;


class PersonalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $people = [
            ['full_name' => 'Aung Aung',     'region_code' => '01', 'township_code' => 'THAGAKA', 'citizenship' => 'N', 'serial_number' => '100001', 'birth_date' => '1990-01-01', 'gender' => 'male', 'nationality' => 'American', 'religion' => 'Christian','blood_type' => 'O+'],
            ['full_name' => 'Mya Mya',       'region_code' => '02', 'township_code' => 'YGN',     'citizenship' => 'N', 'serial_number' => '100002', 'birth_date' => '1992-03-12', 'gender' => 'female', 'nationality' => 'American', 'religion' => 'Christian','blood_type' => 'O+'],
            ['full_name' => 'Ko Ko',         'region_code' => '01', 'township_code' => 'THAGAKA', 'citizenship' => 'N', 'serial_number' => '100003', 'birth_date' => '1985-07-20', 'gender' => 'male', 'nationality' => 'American', 'religion' => 'Christian','blood_type' => 'O+'],
            ['full_name' => 'Thuzar',        'region_code' => '03', 'township_code' => 'MDY',     'citizenship' => 'N', 'serial_number' => '100004', 'birth_date' => '1999-12-12', 'gender' => 'female', 'nationality' => 'American', 'religion' => 'Christian','blood_type' => 'O+'],
            ['full_name' => 'Htet Htet',     'region_code' => '02', 'township_code' => 'YGN',     'citizenship' => 'N', 'serial_number' => '100005', 'birth_date' => '2001-05-06', 'gender' => 'female', 'nationality' => 'American', 'religion' => 'Christian','blood_type' => 'O+'],
            ['full_name' => 'Zaw Zaw',       'region_code' => '01', 'township_code' => 'THAGAKA', 'citizenship' => 'N', 'serial_number' => '100006', 'birth_date' => '1994-08-08', 'gender' => 'male', 'nationality' => 'American', 'religion' => 'Christian','blood_type' => 'O+'],
            ['full_name' => 'Khin Khin',     'region_code' => '03', 'township_code' => 'MDY',     'citizenship' => 'N', 'serial_number' => '100007', 'birth_date' => '1997-09-18', 'gender' => 'female', 'nationality' => 'American', 'religion' => 'Christian','blood_type' => 'O+'],
            ['full_name' => 'Moe Moe',       'region_code' => '02', 'township_code' => 'YGN',     'citizenship' => 'N', 'serial_number' => '100008', 'birth_date' => '1988-04-30', 'gender' => 'female', 'nationality' => 'American', 'religion' => 'Christian','blood_type' => 'O+'],
            ['full_name' => 'Tun Tun',       'region_code' => '01', 'township_code' => 'THAGAKA', 'citizenship' => 'N', 'serial_number' => '100009', 'birth_date' => '1995-06-25', 'gender' => 'male', 'nationality' => 'American', 'religion' => 'Christian','blood_type' => 'O+'],
            ['full_name' => 'Nandar',        'region_code' => '03', 'township_code' => 'MDY',     'citizenship' => 'N', 'serial_number' => '100010', 'birth_date' => '2000-10-10', 'gender' => 'female', 'nationality' => 'American', 'religion' => 'Christian','blood_type' => 'O+'],
            ['full_name' => 'Su Su',         'region_code' => '02', 'township_code' => 'YGN',     'citizenship' => 'N', 'serial_number' => '100011', 'birth_date' => '1993-11-11', 'gender' => 'female', 'nationality' => 'American', 'religion' => 'Christian', 'blood_type' => 'A+'],
            ['full_name' => 'Min Min',       'region_code' => '03', 'township_code' => 'MDY',     'citizenship' => 'N', 'serial_number' => '100012', 'birth_date' => '1990-02-22', 'gender' => 'male', 'nationality' => 'American', 'religion' => 'Christian', 'blood_type' => 'B+'],
            ['full_name' => 'Cherry',        'region_code' => '02', 'township_code' => 'YGN',     'citizenship' => 'N', 'serial_number' => '100013', 'birth_date' => '1996-03-03', 'gender' => 'female', 'nationality' => 'American', 'religion' => 'Christian', 'blood_type' => 'AB+'],
            ['full_name' => 'Kyaw Kyaw',     'region_code' => '01', 'township_code' => 'THAGAKA', 'citizenship' => 'N', 'serial_number' => '100014', 'birth_date' => '1987-06-15', 'gender' => 'male', 'nationality' => 'American', 'religion' => 'Christian', 'blood_type' => 'O-'],
            ['full_name' => 'Hla Hla',       'region_code' => '03', 'township_code' => 'MDY',     'citizenship' => 'N', 'serial_number' => '100015', 'birth_date' => '1991-08-19', 'gender' => 'female', 'nationality' => 'American', 'religion' => 'Christian', 'blood_type' => 'A-'],
            ['full_name' => 'Aye Aye',       'region_code' => '01', 'township_code' => 'THAGAKA', 'citizenship' => 'N', 'serial_number' => '100016', 'birth_date' => '1998-01-17', 'gender' => 'female', 'nationality' => 'American', 'religion' => 'Christian', 'blood_type' => 'B-'],
            ['full_name' => 'Win Win',       'region_code' => '02', 'township_code' => 'YGN',     'citizenship' => 'N', 'serial_number' => '100017', 'birth_date' => '1990-06-06', 'gender' => 'male', 'nationality' => 'American', 'religion' => 'Christian', 'blood_type' => 'O+'],
            ['full_name' => 'Nyein Nyein',   'region_code' => '03', 'township_code' => 'MDY',     'citizenship' => 'N', 'serial_number' => '100018', 'birth_date' => '1989-12-24', 'gender' => 'female', 'nationality' => 'American', 'religion' => 'Christian', 'blood_type' => 'AB-'],
            ['full_name' => 'Bo Bo',         'region_code' => '01', 'township_code' => 'THAGAKA', 'citizenship' => 'N', 'serial_number' => '100019', 'birth_date' => '1994-03-30', 'gender' => 'male', 'nationality' => 'American', 'religion' => 'Christian', 'blood_type' => 'A+'],
            ['full_name' => 'Sandi',         'region_code' => '02', 'township_code' => 'YGN',     'citizenship' => 'N', 'serial_number' => '100020', 'birth_date' => '1992-05-14', 'gender' => 'female', 'nationality' => 'American', 'religion' => 'Christian', 'blood_type' => 'B+'],
            ['full_name' => 'Aung Oo',      'region_code' => '01', 'township_code' => 'THAGAKA', 'citizenship' => 'N', 'serial_number' => '100021', 'birth_date' => '1990-01-01', 'gender' => 'male', 'nationality' => 'American', 'religion' => 'Christian','blood_type' => 'O+'],
            ['full_name' => 'Mya Oo',       'region_code' => '02', 'township_code' => 'YGN',     'citizenship' => 'N', 'serial_number' => '100022', 'birth_date' => '1992-03-12', 'gender' => 'female', 'nationality' => 'American', 'religion' => 'Christian','blood_type' => 'O+'],
            ['full_name' => 'Ko Oo',        'region_code' => '01', 'township_code' => 'THAGAKA', 'citizenship' => 'N', 'serial_number' => '100023', 'birth_date' => '1985-07-20', 'gender' => 'male', 'nationality' => 'American', 'religion' => 'Christian','blood_type' => 'B+'],
            ['full_name' => 'Thuzar Oo',    'region_code' => '03', 'township_code' => 'MDY',     'citizenship' => 'N', 'serial_number' => '100024', 'birth_date' => '1999-12-12', 'gender' => 'female', 'nationality' => 'American', 'religion' => 'Christian','blood_type' => 'O+'],
            ['full_name' => 'Htet Oo',      'region_code' => '02', 'township_code' => 'YGN',     'citizenship' => 'N', 'serial_number' => '100025', 'birth_date' => '2001-05-06', 'gender' => 'female', 'nationality' => 'American', 'religion' => 'Christian','blood_type' => 'O+'],
            ['full_name' => 'Zaw Oo',       'region_code' => '01', 'township_code' => 'THAGAKA', 'citizenship' => 'N', 'serial_number' => '100026', 'birth_date' => '1994-08-08', 'gender' => 'male', 'nationality' => 'American', 'religion' => 'Christian','blood_type' => 'A+'],
            ['full_name' => 'Khin Oo',      'region_code' => '03', 'township_code' => 'MDY',     'citizenship' => 'N', 'serial_number' => '100027', 'birth_date' => '1997-09-18', 'gender' => 'female', 'nationality' => 'American', 'religion' => 'Christian','blood_type' => 'O+'],
            ['full_name' => 'Moe Oo',       'region_code' => '02', 'township_code' => 'YGN',     'citizenship' => 'N', 'serial_number' => '100028', 'birth_date' => '1988-04-30', 'gender' => 'female', 'nationality' => 'American', 'religion' => 'Christian','blood_type' => 'O+'],
            ['full_name' => 'Tun Oo',       'region_code' => '01', 'township_code' => 'THAGAKA', 'citizenship' => 'N', 'serial_number' => '100029', 'birth_date' => '1995-06-25', 'gender' => 'male', 'nationality' => 'American', 'religion' => 'Christian','blood_type' => 'O+'],
            ['full_name' => 'Nandar Oo',    'region_code' => '03', 'township_code' => 'MDY',     'citizenship' => 'N', 'serial_number' => '100030', 'birth_date' => '2000-10-10', 'gender' => 'female', 'nationality' => 'American', 'religion' => 'Christian','blood_type' => 'O+'],
            ['full_name' => 'Soe Soe',      'region_code' => '02', 'township_code' => 'YGN',     'citizenship' => 'N', 'serial_number' => '100031', 'birth_date' => '1991-02-15', 'gender' => 'female', 'nationality' => 'American', 'religion' => 'Christian', 'blood_type' => 'A+'],
            ['full_name' => 'Hnin Hnin',    'region_code' => '03', 'township_code' => 'MDY',     'citizenship' => 'N', 'serial_number' => '100032', 'birth_date' => '1993-07-21', 'gender' => 'female', 'nationality' => 'American', 'religion' => 'Christian', 'blood_type' => 'B+'],
            ['full_name' => 'Swan Swan',    'region_code' => '01', 'township_code' => 'THAGAKA', 'citizenship' => 'N', 'serial_number' => '100033', 'birth_date' => '1986-11-11', 'gender' => 'male',   'nationality' => 'American', 'religion' => 'Christian', 'blood_type' => 'O-'],
            ['full_name' => 'Ei Mon',       'region_code' => '02', 'township_code' => 'YGN',     'citizenship' => 'N', 'serial_number' => '100034', 'birth_date' => '1995-03-05', 'gender' => 'female', 'nationality' => 'American', 'religion' => 'Christian', 'blood_type' => 'AB+'],
            ['full_name' => 'Aung Myint',   'region_code' => '03', 'township_code' => 'MDY',     'citizenship' => 'N', 'serial_number' => '100035', 'birth_date' => '1992-09-09', 'gender' => 'male',   'nationality' => 'American', 'religion' => 'Christian', 'blood_type' => 'O+'],
            ['full_name' => 'San San',      'region_code' => '01', 'township_code' => 'THAGAKA', 'citizenship' => 'N', 'serial_number' => '100036', 'birth_date' => '1989-12-01', 'gender' => 'female', 'nationality' => 'American', 'religion' => 'Christian', 'blood_type' => 'A-'],
            ['full_name' => 'Htet Naing',   'region_code' => '02', 'township_code' => 'YGN',     'citizenship' => 'N', 'serial_number' => '100037', 'birth_date' => '1997-08-18', 'gender' => 'male',   'nationality' => 'American', 'religion' => 'Christian', 'blood_type' => 'B-'],
            ['full_name' => 'May May',      'region_code' => '03', 'township_code' => 'MDY',     'citizenship' => 'N', 'serial_number' => '100038', 'birth_date' => '1996-04-22', 'gender' => 'female', 'nationality' => 'American', 'religion' => 'Christian', 'blood_type' => 'O+'],
            ['full_name' => 'Kyaw Min',     'region_code' => '01', 'township_code' => 'THAGAKA', 'citizenship' => 'N', 'serial_number' => '100039', 'birth_date' => '1988-06-14', 'gender' => 'male',   'nationality' => 'American', 'religion' => 'Christian', 'blood_type' => 'AB-'],
            ['full_name' => 'Su Mon',       'region_code' => '02', 'township_code' => 'YGN',     'citizenship' => 'N', 'serial_number' => '100040', 'birth_date' => '1994-10-28', 'gender' => 'female', 'nationality' => 'American', 'religion' => 'Christian', 'blood_type' => 'O+'],
            ['full_name' => 'Aye Chan',     'region_code' => '03', 'township_code' => 'MDY',     'citizenship' => 'N', 'serial_number' => '100041', 'birth_date' => '1993-02-02', 'gender' => 'male',   'nationality' => 'American', 'religion' => 'Christian', 'blood_type' => 'B+'],
            ['full_name' => 'Thandar',      'region_code' => '01', 'township_code' => 'THAGAKA', 'citizenship' => 'N', 'serial_number' => '100042', 'birth_date' => '1995-05-15', 'gender' => 'female', 'nationality' => 'American', 'religion' => 'Christian', 'blood_type' => 'A+'],
            ['full_name' => 'Soe Moe',      'region_code' => '02', 'township_code' => 'YGN',     'citizenship' => 'N', 'serial_number' => '100043', 'birth_date' => '1991-09-09', 'gender' => 'male',   'nationality' => 'American', 'religion' => 'Christian', 'blood_type' => 'O+'],
            ['full_name' => 'Nay Chi',      'region_code' => '03', 'township_code' => 'MDY',     'citizenship' => 'N', 'serial_number' => '100044', 'birth_date' => '1997-12-12', 'gender' => 'female', 'nationality' => 'American', 'religion' => 'Christian', 'blood_type' => 'AB+'],
            ['full_name' => 'Myint Myint',  'region_code' => '01', 'township_code' => 'THAGAKA', 'citizenship' => 'N', 'serial_number' => '100045', 'birth_date' => '1990-03-03', 'gender' => 'female', 'nationality' => 'American', 'religion' => 'Christian', 'blood_type' => 'O-'],
            ['full_name' => 'Hlaing Min',   'region_code' => '02', 'township_code' => 'YGN',     'citizenship' => 'N', 'serial_number' => '100046', 'birth_date' => '1989-07-07', 'gender' => 'male',   'nationality' => 'American', 'religion' => 'Christian', 'blood_type' => 'B-'],
            ['full_name' => 'Ei Ei',        'region_code' => '03', 'township_code' => 'MDY',     'citizenship' => 'N', 'serial_number' => '100047', 'birth_date' => '1996-11-11', 'gender' => 'female', 'nationality' => 'American', 'religion' => 'Christian', 'blood_type' => 'A-'],
            ['full_name' => 'Tun Lin',      'region_code' => '01', 'township_code' => 'THAGAKA', 'citizenship' => 'N', 'serial_number' => '100048', 'birth_date' => '1992-08-08', 'gender' => 'male',   'nationality' => 'American', 'religion' => 'Christian', 'blood_type' => 'O+'],
            ['full_name' => 'Shwe Zin',     'region_code' => '02', 'township_code' => 'YGN',     'citizenship' => 'N', 'serial_number' => '100049', 'birth_date' => '1994-04-04', 'gender' => 'female', 'nationality' => 'American', 'religion' => 'Christian', 'blood_type' => 'AB-'],
            ['full_name' => 'Aung Kyaw',    'region_code' => '03', 'township_code' => 'MDY',     'citizenship' => 'N', 'serial_number' => '100050', 'birth_date' => '1998-06-06', 'gender' => 'male',   'nationality' => 'American', 'religion' => 'Christian', 'blood_type' => 'O+'],
        ];

        foreach ($people as $index => $person) {
            $customId = generateCustomId($index);

            Personal::create(array_merge($person, [
                'slug' => $customId,
            ]));
        }
    }
}

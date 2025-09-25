<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Personal;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employeePersonals = Personal::skip(40)->take(5)->get();

        foreach ($employeePersonals as $index => $personal) {
            $customId = generateCustomId($index);

            $employee = Employee::create([
                'slug'             => $customId,  // Custom slug based on index
                'personal_slug'    => $personal->slug, // auto-create Personal if not already seeded
                'employee_name'    => $personal->full_name,  // Use the full name from Personal
                'employee_code'    => 'EMP' . str_pad($index + 1, 5, '0', STR_PAD_LEFT),
                'email'            => 'employee' . ($index + 1) . '@company.com',
                'phone'            => '091000000' . ($index + 1),  // Custom phone numbers
                'address'          => 'Yangon, Myanmar',  // Fixed address (you can change it as needed)
                'position'         => $this->getEmployeePosition($index),  // Custom function to alternate positions
                'department'       => $this->getEmployeeDepartment($index),  // Custom function to alternate departments
                'employment_type'  => $this->getEmploymentType($index),  // Custom function to alternate employment types
                'hire_date'        => '2021-05-01',  // Fixed hire date (you can change it as needed)
                'resign_date'      => null,
                'experience_years' => rand(1, 10),  // Random experience years between 1 and 10
                'salary'           => $this->getEmployeeSalary($index),  // Custom function for salary range
                'status'           => 'active',
            ]);
        }
    }
    // Custom functions to generate employee data
    private function getEmployeePosition($index)
    {
        $positions = ['Accountant', 'Cleaner', 'Security', 'Clerk'];
        return $positions[$index % count($positions)];  // Alternates positions
    }

    private function getEmployeeDepartment($index)
    {
        $departments = ['Finance', 'Maintenance', 'Admin', 'HR'];
        return $departments[$index % count($departments)];  // Alternates departments
    }

    private function getEmploymentType($index)
    {
        $employmentTypes = ['full-time', 'part-time', 'contract'];
        return $employmentTypes[$index % count($employmentTypes)];  // Alternates employment types
    }

    private function getEmployeeSalary($index)
    {
        $salaryRanges = [200000, 300000, 400000, 500000];  // Customize salary ranges
        return $salaryRanges[$index % count($salaryRanges)];  // Alternates salary
    }
}

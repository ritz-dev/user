<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'personalId' => $this->personal_id,
            'teacherCode' => $this->teacher_code,
            'email' => $this->email,
            'phonenumber' => $this->phonenumber,
            'department' => $this->department,
            'salary' => $this->salary,
            'hireDate' => $this->hire_date,
            'status' => $this->status,
            'employmentType' => $this->employment_type,
            'specialization' => $this->specialization,
            'designation' => $this->designation
        ];
    }

}

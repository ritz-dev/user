<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
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
            'name' => $this->name,
            'studentCode' => $this->student_code,
            'address' => $this->address,
            'email' => $this->email,
            'phonenumber' => $this->phonenumber,
            'pob' => $this->pob,
            'nationality' => $this->nationality,
            'religion' => $this->religion,
            'bloodType' => $this->blood_type,
            'status' => $this->status,
            'academicLevel' => $this->academic_level,
            'academicYear' => $this->academic_year,
            'enrollmentDate' => date('Y-m-d',strtotime($this->enrollment_date)),
            'graduationDate' => date('Y-m-d',strtotime($this->graduation_date)),
        ];
    }
}

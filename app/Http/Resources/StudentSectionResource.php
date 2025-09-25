<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentSectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "slug" => $this->slug,
            "studentId" => $this->student->id,
            "studentSlug" => $this->student->slug,
            "studentName" => $this->student->name,
            "studentCode" => $this->student->student_code,
            "sectionId" => $this->section_id
        ];
    }
}

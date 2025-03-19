<?php

namespace App\Models;

use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentSection extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = "student_sections";

    protected $fillable = [
        "slug",
        "student_id",
        "section_id"
    ];

    public function student()
    {
        return $this->belongsTo(Student::class,'student_id','id');
    }
}

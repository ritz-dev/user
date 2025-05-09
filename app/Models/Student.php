<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'slug',
        'personal_id',
        'student_number',
        'registration_number',
        'school_name',
        'school_code',
        'email',
        'phone',
        'address',
        'status',
        'admission_date',
        'graduation_date',
    ];

    protected $attributes = [
        'status' => 'enrolled',
    ];

    public function getIsGraduatedAttribute()
    {
        return $this->current_status === 'graduated';
    }

}

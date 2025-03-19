<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentInfo extends Model
{
    protected $table = 'parent_infos';

    protected $fillable = [
        'slug',
        'personal_id',
        'student_id',
        'name',
        'email',
        'phonenumber',
        'title',
    ];

    /**
     * Get the personal record associated with the parent info.
     */
    public function personal()
    {
        return $this->belongsTo(Personal::class, 'personal_id', 'id');
    }

    /**
     * Get the student record associated with the parent info.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }
}

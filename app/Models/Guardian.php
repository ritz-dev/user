<?php

namespace App\Models;

use Ramsey\Uuid\Guid\Guid;
use Illuminate\Database\Eloquent\Model;

class Guardian extends Model
{
    protected $fillable = [
        'slug',
        'student_id',
        'personal_id',
        'relation',
        'occupation',
        'phone',
        'email',
    ];

    protected $hidden = ["id","student_id","personal_id","created_at","updated_at","deleted_at"];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if(empty($model->slug)){
                $model->slug = (string) Guid::uuid4();
            }
        });
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function personal()
    {
        return $this->belongsTo(Personal::class);
    }
}

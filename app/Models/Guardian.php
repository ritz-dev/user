<?php

namespace App\Models;

use App\Models\Student;
use App\Models\Personal;
use Ramsey\Uuid\Guid\Guid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Guardian extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'slug',
        'student_slug',
        'personal_slug',
        'relation',
        'occupation',
        'name',
        'phone',
        'email',
    ];

    protected $hidden = ["id","student_slug","personal_slug","created_at","updated_at","deleted_at"];

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
        return $this->belongsTo(Student::class, 'student_slug', 'slug');
    }

    public function personal()
    {
        return $this->belongsTo(Personal::class, 'personal_slug', 'slug');
    }
}

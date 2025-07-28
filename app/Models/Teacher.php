<?php

namespace App\Models;

use Ramsey\Uuid\Guid\Guid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Teacher extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'slug',
        'personal_slug',
        'teacher_name',
        'teacher_code',
        'email',
        'phone',
        'address',
        'qualification',
        'subject',
        'experience_years',
        'salary',
        'hire_date',
        'status',
        'employment_type',
    ];

    protected $hidden = ["id","personal_slug","created_at","updated_at","deleted_at"];

    protected $casts = [
        'hire_date' => 'date',
        'salary' => 'decimal:2',
        'experience_years' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if(empty($model->slug)){
                $model->slug = (string) Guid::uuid4();
            }
        });
    }

    /**
     * Relationship to personal info.
     */
    public function personal()
    {
        return $this->belongsTo(Personal::class, 'personal_slug', 'slug');
    }
}

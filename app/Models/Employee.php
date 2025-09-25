<?php

namespace App\Models;

use App\Models\Personal;
use Ramsey\Uuid\Guid\Guid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Employee extends Authenticatable
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'slug',
        'personal_slug',
        'employee_name',
        'employee_code',
        'email',
        'phone',
        'address',
        'position',
        'department',
        'employment_type',
        'hire_date',
        'resign_date',
        'experience_years',
        'salary',
        'status',
    ];

    protected $hidden = ["id","personal_slug","created_at","updated_at","deleted_at"];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if(empty($model->slug)){
                $model->slug = (string) Guid::uuid4();
            }
        });
    }

    public function personal()
    {
        return $this->belongsTo(Personal::class, 'personal_slug', 'slug');
    }
}


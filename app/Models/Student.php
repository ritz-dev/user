<?php

namespace App\Models;

use App\Models\Personal;
use Ramsey\Uuid\Guid\Guid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'slug',
        'personal_slug',
        'student_name',
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

    protected $hidden = ["id","personal_slug","created_at","updated_at","deleted_at"];

    protected $attributes = [
        'status' => 'enrolled',
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

    public function personal()
    {
        return $this->belongsTo(Personal::class, 'personal_slug', 'slug');
    }

    public function guardians()
    {
        return $this->hasMany(Guardian::class, 'student_slug', 'slug');
    }

    public function getIsGraduatedAttribute()
    {
        return $this->current_status === 'graduated';
    }

    

}

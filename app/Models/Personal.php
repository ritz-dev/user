<?php

namespace App\Models;

use Ramsey\Uuid\Guid\Guid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Personal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'slug',
        'full_name',
        'birth_date',
        'gender',
        'region_code',
        'township_code',
        'citizenship',
        'serial_number',
        'nationality',
        'religion',
        'blood_type'
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

    protected $casts = [
        'deleted_at' => 'datetime',
    ];
}

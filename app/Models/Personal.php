<?php

namespace App\Models;

use Ramsey\Uuid\Guid\Guid;
use App\Models\PersonalUpdate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Personal extends Model
{
    use SoftDeletes, HasFactory;

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

    protected $hidden = ["id","created_at","updated_at","deleted_at"];

    protected $casts = ['deleted_at' => 'datetime'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if(empty($model->slug)){
                $model->slug = (string) Guid::uuid4();
            }
        });
    }

    public function updates()
    {
        return $this->hasMany(PersonalUpdate::class, 'personal_slug', 'slug');
    }

    public function latestUpdate()
    {
        return $this->hasOne(PersonalUpdate::class)->latestOfMany();
    }

    public function getEffectiveDataAttribute(): array
    {
        $update = $this->latestUpdate;

        return [
            'full_name'      => $update->full_name ?? $this->full_name,
            'birth_date'     => $update->birth_date ?? $this->birth_date,
            'gender'         => $update->gender ?? $this->gender,
            'region_code'    => $update->region_code ?? $this->region_code,
            'township_code'  => $update->township_code ?? $this->township_code,
            'citizenship'    => $update->citizenship ?? $this->citizenship,
            'serial_number'  => $update->serial_number ?? $this->serial_number,
            'nationality'    => $update->nationality ?? $this->nationality,
            'religion'       => $update->religion ?? $this->religion,
            'blood_type'     => $update->blood_type ?? $this->blood_type,
        ];
    }
        
}

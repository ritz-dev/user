<?php

namespace App\Models;

use App\Models\Personal;
use Workbench\App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PersonalUpdate extends Model
{
    protected $primaryKey = 'updated_id';

    protected $fillable = [
        'personal_id', 'full_name', 'birth_date', 'gender',
        'region_code', 'township_code', 'citizenship', 'serial_number',
        'nationality', 'religion', 'blood_type',
        'updatable_id', 'updatable_type'
    ];

    protected $hidden = ["updated_id","personal_id","updatable_id","updatable_type","created_at","updated_at","deleted_at"];

    public function personal()
    {
        return $this->belongsTo(Personal::class, 'personal_id');
    }

    public function updatable()
    {
        return $this->morphTo();
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

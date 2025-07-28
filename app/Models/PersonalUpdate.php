<?php

namespace App\Models;

use App\Models\Personal;
use Workbench\App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PersonalUpdate extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'updated_id';

    protected $fillable = [
        'personal_slug', 'full_name', 'birth_date', 'gender',
        'region_code', 'township_code', 'citizenship', 'serial_number',
        'nationality', 'religion', 'blood_type',
        'updatable_slug', 'updatable_type'
    ];

    protected $hidden = ["id","updated_id","updatable_slug","updatable_type","created_at","updated_at","deleted_at"];

    public function personal()
    {
        return $this->belongsTo(Personal::class, 'personal_slug');
    }

    public function updatable()
    {
        return $this->morphTo();
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'user_slug');
    }
}

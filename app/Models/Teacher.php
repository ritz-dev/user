<?php

namespace App\Models;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Teacher extends Model
{
    use HasFactory,SoftDeletes;

    // protected $table = "teachers";

     /**
     * The primary key type.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'slug',
        "personal_id",
        "teacher_code",
        "email",
        "phonenumber",
        "department",
        "salary",
        "hire_date",
        "status",
        "employment_type",
        "specialization",
        "designation",
    ];

    public function personal(){
        return $this->belongsTo(Personal::class,'personal_id','id');
    }
}

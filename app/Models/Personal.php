<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Personal extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = "personals";
    protected $fillable = [
        "slug",
        "name",
        "gender",
        "dob",
        "address",
        "state",
        "district",
        "register_code"
    ];
}

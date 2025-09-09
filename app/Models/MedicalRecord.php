<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    protected $fillable = [
        'user_id',
        'record_type',
        'name',
        'description',
        'recorded_at',
    ];
}

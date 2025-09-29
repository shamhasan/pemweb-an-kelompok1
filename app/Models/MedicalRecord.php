<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{

    use HasFactory;

    protected $fillable = [
        'user_id',
        'record_type',
        'name',
        'description',
        'recorded_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

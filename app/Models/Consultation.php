<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'nutritionist_id', 'status', 'started_at', 'ended_at'];

    // Konsultasi ini milik user mana
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Konsultasi ini dilayani oleh ahli gizi mana
    public function nutritionist()
    {
        return $this->belongsTo(User::class, 'nutritionist_id');
    }

    // Satu konsultasi memiliki banyak pesan
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}

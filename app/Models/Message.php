<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    protected $fillable = ['consultation_id', 'sender_id', 'content'];

    // Pesan ini milik konsultasi mana
    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    // Pesan ini dikirim oleh siapa
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}

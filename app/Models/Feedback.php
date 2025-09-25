<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    // Definisikan kolom yang boleh diisi
    protected $fillable = [
        'user_id',
        'rating',
        'comment',
    ];

    // Satu feedback dimiliki oleh satu user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

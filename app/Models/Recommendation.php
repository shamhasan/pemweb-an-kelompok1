<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'description',
        'related_url'
    ];

    // Mendefinisikan bahwa rekomendasi ini dimiliki oleh satu User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

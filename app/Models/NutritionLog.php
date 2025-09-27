<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NutritionLog extends Model
{
    use HasFactory;

    protected $table = 'nutrition_logs';

    // Kolom yang boleh diisi
    protected $fillable = [
        'user_id',
        'food_name',
        'calories',
        'protein_g',
        'carbs_g',
        'fat_g',
        'meal_type',
        'consumed_at'
    ];

    // Casting untuk memastikan 'consumed_at' selalu menjadi objek Carbon (tanggal/waktu)
    protected $casts = [
        'consumed_at' => 'datetime',
    ];

    // Mendefinisikan bahwa catatan ini dimiliki oleh satu User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

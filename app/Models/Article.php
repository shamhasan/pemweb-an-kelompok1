<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    // Mendefinisikan kolom mana saja yang boleh diisi secara massal
    protected $fillable = [
        'title', 'content', 'category_id', 'author_id', 'image_url', 'status'
    ];

    // Satu artikel dimiliki oleh satu kategori
    public function category()
    {
        return $this->belongsTo(ArticleCategory::class, 'category_id');
    }

    // Satu artikel dimiliki oleh satu penulis (User)
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
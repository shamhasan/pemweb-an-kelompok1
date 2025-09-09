<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleCategory extends Model
{
    use HasFactory;
    // Satu kategori bisa memiliki banyak artikel
    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}
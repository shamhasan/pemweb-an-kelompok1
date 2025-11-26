<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ArticleCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ArticleCategoryController extends Controller
{
    /**
     * Menampilkan semua kategori artikel.
     * Endpoint: GET /api/article-categories
     */
    public function index()
    {
        $categories = ArticleCategory::orderBy('name')->get();
        return response()->json($categories);
    }

    /**
     * (Admin) Menyimpan kategori artikel baru.
     * Endpoint: POST /api/admin/article-categories
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:article_categories,name',
        ]);

        $category = ArticleCategory::create($validated);

        return response()->json($category, Response::HTTP_CREATED);
    }

    /**
     * Menampilkan satu kategori artikel.
     * Endpoint: GET /api/article-categories/{articleCategory}
     */
    public function show(ArticleCategory $articleCategory)
    {
        // Anda bisa memuat artikel terkait jika perlu
        // $articleCategory->load('articles');
        return response()->json($articleCategory);
    }

    /**
     * (Admin) Mengupdate kategori artikel.
     * Endpoint: PUT /api/admin/article-categories/{articleCategory}
     */
    public function update(Request $request, ArticleCategory $articleCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:article_categories,name,' . $articleCategory->id,
        ]);

        $articleCategory->update($validated);

        return response()->json($articleCategory);
    }

    /**
     * (Admin) Menghapus kategori artikel.
     * Endpoint: DELETE /api/admin/article-categories/{articleCategory}
     */
    public function destroy(ArticleCategory $articleCategory)
    {
        // Periksa apakah ada artikel yang menggunakan kategori ini
        if ($articleCategory->articles()->exists()) {
            return response()->json([
                'message' => 'Kategori tidak dapat dihapus karena masih digunakan oleh artikel.'
            ], Response::HTTP_CONFLICT); // 409 Conflict
        }

        $articleCategory->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}


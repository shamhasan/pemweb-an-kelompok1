<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    // ENDPOINT UNTUK PENGGUNA UMUM

    // Menampilkan semua artikel yang sudah publish
    public function index()
    {
        $articles = Article::where('status', 'published')
            ->with(['category', 'author']) // Eager loading untuk relasi
            ->latest() // Urutkan dari yang terbaru
            ->paginate(10); // Paginasi
        return response()->json($articles);
    }

    // Menampilkan detail satu artikel
    public function show(Article $article)
    {
        // Pastikan hanya artikel yang sudah publish yang bisa dilihat
        if ($article->status !== 'published') {
            return response()->json(['message' => 'Artikel tidak ditemukan'], 404);
        }
        return response()->json($article->load(['category', 'author']));
    }

    // Menampilkan semua kategori
    public function getCategories()
    {
        return response()->json(ArticleCategory::all());
    }

    // ENDPOINT KHUSUS UNTUK ADMIN

    // Membuat artikel baru
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'required|exists:article_categories,id',
            'image_url' => 'nullable|url',
            'status' => 'required|in:published,draft',
        ]);

        $article = Article::create(array_merge($validatedData, [
            'author_id' => $request->user()->id, // Set author dari user yang login
        ]));

        return response()->json($article, 201);
    }

    // Mengupdate artikel
    public function update(Request $request, Article $article)
    {
        $validatedData = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'category_id' => 'sometimes|required|exists:article_categories,id',
            'image_url' => 'nullable|url',
            'status' => 'sometimes|required|in:published,draft',
        ]);

        $article->update($validatedData);
        return response()->json($article);
    }

    // Menghapus artikel
    public function destroy(Request $request, Article $article)
    {
        $article->delete();
        return response()->json(['message' => 'Artikel berhasil dihapus'], 200);
    }
}

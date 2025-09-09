<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Api\NutritionLogController;
use App\Http\Controllers\Api\RecommendationController; // Ditambahkan

// =======================================================
// == RUTE PUBLIK (BISA DIAKSES TANPA LOGIN)
// =======================================================

// Autentikasi
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Artikel (Publik)
Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{article}', [ArticleController::class, 'show']);
Route::get('/article-categories', [ArticleController::class, 'getCategories']);


// =======================================================
// == RUTE TERPROTEKSI (WAJIB LOGIN & PAKAI TOKEN)
// =======================================================

Route::middleware('auth:sanctum')->group(function () {

    // Profil Pengguna & Logout
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // Artikel (Admin)
    Route::post('/admin/articles', [ArticleController::class, 'store']);
    Route::put('/admin/articles/{article}', [ArticleController::class, 'update']);
    Route::delete('/admin/articles/{article}', [ArticleController::class, 'destroy']);

    // Progress Nutrisi (User)
    Route::apiResource('nutrition-logs', NutritionLogController::class);

    // Rekomendasi (User) 
    Route::get('/recommendations', [RecommendationController::class, 'index']);
});

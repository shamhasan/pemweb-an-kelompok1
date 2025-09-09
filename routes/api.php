<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Api\ConsultationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Endpoint publik (tidak butuh token)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

    
// Endpoint yang butuh autentikasi (contoh)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// --- Rute Artikel ---
// Rute publik untuk semua pengguna
Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{article}', [ArticleController::class, 'show']);
Route::get('/article-categories', [ArticleController::class, 'getCategories']);

// Rute yang memerlukan autentikasi (khusus admin)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/consultations', [ConsultationController::class, 'index']); // Daftar konsultasi user
    Route::post('/consultations', [ConsultationController::class, 'store']); // Memulai konsultasi baru
    Route::get('/consultations/{consultation}', [ConsultationController::class, 'show']); // Lihat detail & pesan dalam 1 konsultasi
    Route::post('/consultations/{consultation}/messages', [ConsultationController::class, 'sendMessage']); // Kirim pesan
});
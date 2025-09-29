<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\NutritionLogController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\MedicalRecordController;
use App\Http\Controllers\Api\ConsultationController;
use App\Http\Controllers\Api\MessageController;

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
    Route::post('/feedback', [FeedbackController::class, 'store']);

    // Endpoint khusus untuk admin
    Route::get('/admin/feedback', [FeedbackController::class, 'index']);
    Route::delete('/admin/feedback/{feedback}', [FeedbackController::class, 'destroy']);

    // Progress Nutrisi (User)
    Route::apiResource('nutrition-logs', NutritionLogController::class);

    // Rekomendasi (User) 
    Route::get('/recommendations', [RecommendationController::class, 'index']);

    Route::apiResource('medical-records', MedicalRecordController::class);

    // Konsultasi ChatBot
    Route::get('/consultations', [ConsultationController::class, 'index']);
    Route::post('/consultations', [ConsultationController::class, 'store']);
    Route::get('/consultations/active', [ConsultationController::class, 'activeConsultations']);
    Route::get('/consultations/{consultation}', [ConsultationController::class, 'show']);
    Route::put('/consultations/{consultation}', [ConsultationController::class, 'update']);
    Route::patch('/consultations/{consultation}', [ConsultationController::class, 'update']);
    Route::delete('/consultations/{consultation}', [ConsultationController::class, 'destroy']);

    Route::get('/messages', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::get('/messages/{message}', [MessageController::class, 'show']);
    Route::patch('/messages/{message}', [MessageController::class, 'update']);
    Route::put('/messages/{message}', [MessageController::class, 'update']);
    Route::delete('/messages/{message}', [MessageController::class, 'destroy']);
});

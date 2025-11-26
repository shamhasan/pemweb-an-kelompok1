<?php

use App\Http\Controllers\Api\ConsultationController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\MedicalRecordController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\NutritionLogController;
use App\Http\Controllers\Api\RecommendationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Publik (tidak butuh token)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{article}', [ArticleController::class, 'show']);

// FEEDBACK (Tambahan Fix)
Route::get('/feedbacks', [FeedbackController::class, 'index']); // GET semua feedback (public)

// Endpoint yang butuh autentikasi
Route::middleware('auth:api')->group(function () {

    // User profile
    Route::get('/profile', [UserController::class, 'getprofile']);
    Route::post('/profile/update', [UserController::class, 'updateProfile']);

    // Rekomendasi
    Route::get('/recommendation/calories', [RecommendationController::class, 'getCalorieRecommendation']);

    // Medical record
    Route::post('/medical-records', [MedicalRecordController::class, 'store']);
    Route::get('/medical-records', [MedicalRecordController::class, 'index']);
    Route::put('/medical-records/{id}', [MedicalRecordController::class, 'update']);
    Route::delete('/medical-records/{id}', [MedicalRecordController::class, 'destroy']);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Progress Nutrisi
    Route::get('/nutrition-logs', [NutritionLogController::class, 'index']);
    Route::post('/nutrition-logs', [NutritionLogController::class, 'store']);
    Route::put('/nutrition-logs/{id}', [NutritionLogController::class, 'update']);
    Route::delete('/nutrition-logs/{id}', [NutritionLogController::class, 'destroy']);

    // Konsultasi
    Route::post('/consultations', [ConsultationController::class, 'store']);
    Route::patch('/consultations/{consultation}', [ConsultationController::class, 'update']);
    Route::get('/consultations/{consultation}', [ConsultationController::class, 'show']);
    Route::get('/consultations/me/active', [ConsultationController::class, 'activeForUser']);

    Route::post('/messages', [MessageController::class, 'store']);
    Route::patch('/messages/{message}', [MessageController::class, 'update']);

    // FEEDBACK (user create)
    Route::post('/feedbacks', [FeedbackController::class, 'store']);
});

// ADMIN ROUTE
Route::group(['middleware' => ['auth:api', 'admin'], 'prefix' => 'admin'], function () {

    // Artikel
    Route::post('/articles', [ArticleController::class, 'store']);
    Route::put('/articles/{article}', [ArticleController::class, 'update']);
    Route::delete('/articles/{article}', [ArticleController::class, 'destroy']);

    // Konsultasi
    Route::get('/consultations', [ConsultationController::class, 'index']);
    Route::put('/consultations/{consultation}', [ConsultationController::class, 'update']);
    Route::get('/consultations/active', [ConsultationController::class, 'activeConsultations']);
    Route::delete('/consultations/{consultation}', [ConsultationController::class, 'destroy']);

    // Messages
    Route::get('/messages', [MessageController::class, 'index']);
    Route::get('/messages/{message}', [MessageController::class, 'show']);
    Route::put('/messages/{message}', [MessageController::class, 'update']);
    Route::delete('/messages/{message}', [MessageController::class, 'destroy']);

    // Feedback Admin
    Route::get('/feedbacks', [FeedbackController::class, 'index']);
    Route::delete('/feedbacks/{id}', [FeedbackController::class, 'destroy']);
    Route::delete('/feedbacks', [FeedbackController::class, 'destroyAll']);
});

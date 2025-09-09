<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\RecommendationController; // Pastikan ini ada
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Endpoint publik (tidak butuh token)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Endpoint yang butuh autentikasi
Route::middleware('auth:sanctum')->group(function () {
    // Route user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Rute untuk Fitur Rekomendasi
    Route::get('/recommendations', [RecommendationController::class, 'index']);
});
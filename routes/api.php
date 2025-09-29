<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\MedicalRecordController;
use App\Http\Controllers\Api\FeedbackController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Endpoint publik (tidak butuh token)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Endpoint publik(gaperlu login)
Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{article}', [ArticleController::class, 'show']);

// Endpoint yang butuh autentikasi
// Group route yang memerlukan JWT
Route::middleware('auth:api')->group(function () {
    // Medical record   
    Route::post('/medical-records', [MedicalRecordController::class, 'store']);
    
    
    
    
    
    
    Route::post('/logout', [AuthController::class, 'logout']);
});

<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\MedicalRecordController;
use App\Http\Controllers\Api\FeedbackController;
<<<<<<< HEAD
use App\Http\Controllers\Api\UserController;
=======
use App\Http\Controllers\Api\NutritionLogController;

>>>>>>> 59a41e8 (fitur progres nutrisi)

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
    //User Profile
    Route::get('/profile', [UserController::class, 'getprofile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);


    // Medical record   
    Route::post('/medical-records', [MedicalRecordController::class, 'store']);    
<<<<<<< HEAD
    Route::get('/medical-records', [MedicalRecordController::class, 'index']);
    Route::put('/medical-records/{id}', [MedicalRecordController::class, 'update']);
    Route::delete('/medical-records/{id}', [MedicalRecordController::class, 'destroy']);

    // Logout
=======
>>>>>>> 59a41e8 (fitur progres nutrisi)
    Route::post('/logout', [AuthController::class, 'logout']);

    // Progres Nutrisi
    Route::get('nutrition-logs', [NutritionLogController::class, 'index']);
    Route::post('nutrition-logs', [NutritionLogController::class, 'store']);
    Route::put('nutrition-logs/{id}', [NutritionLogController::class, 'update']);
    Route::delete('nutrition-logs/{id}', [NutritionLogController::class, 'destroy']);
    });

// Endpoint khusus admin
Route::group(['middleware' => ['auth:api', 'admin'], 'prefix' => 'admin'], function() {
    Route::post('/articles', [ArticleController::class, 'store']);
    Route::put('/articles/{article}', [ArticleController::class, 'update']);
    Route::delete('/articles/{article}', [ArticleController::class, 'destroy']);
});
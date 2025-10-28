<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// No need for Api\AuthController here unless used for other API endpoints later
// use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MakeupController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|--------------------------------------------------------------------------
*/

// --- TEMPORARILY MAKE /analyze PUBLIC ---
// This route is now accessible without login for testing purposes.
Route::post('/analyze', [MakeupController::class, 'analyze'])->name('api.analyze');
// --- END TEMPORARY CHANGE ---


// Use 'auth:web' middleware for web session authentication for other routes later
Route::middleware('auth:web')->group(function () {
    // Add other API routes that require web login here (e.g., save look)
    // Route::post('/save-look', [SavedLookController::class, 'store'])->name('api.save-look');
});

// Routes for potential future mobile app using Sanctum tokens remain separate
// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/user', [AuthController::class, 'user']);
//     Route::post('/logout', [AuthController::class, 'logout']);
// });
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MakeupController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Here is where you can register API routes for your application.
|--------------------------------------------------------------------------
*/

// --- TEMPORARILY MAKE /analyze PUBLIC ---
// This route is accessible without login for testing purposes.
Route::post('/analyze', [MakeupController::class, 'analyze'])->name('api.analyze');
// --- END TEMPORARY CHANGE ---


// Use 'auth:web' middleware for web session authentication
Route::middleware('auth:web')->group(function () {
    
    // --- ADD THIS NEW ROUTE FOR VIRTUAL TRY-ON ---
    // This route will require the user to be logged in
    Route::post('/virtual-tryon', [MakeupController::class, 'virtualTryOn'])->name('api.tryon');
    // --- END ADD ---

    // Add other API routes that require web login here (e.g., save look)
    // Route::post('/save-look', [SavedLookController::class, 'store'])->name('api.save-look');
});

// Routes for potential future mobile app using Sanctum tokens remain separate
// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/user', [AuthController::class, 'user']);
//     Route::post('/logout', [AuthController::class, 'logout']);
// });
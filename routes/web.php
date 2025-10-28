<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ViewController;
use App\Http\Controllers\Auth\GoogleLoginController; // Use the correct web login controller

// --- Web Page Routes ---
Route::get('/', [ViewController::class, 'welcome'])->name('welcome');
Route::get('/analyze', [ViewController::class, 'analysis'])->name('analyze');
Route::get('/saved-looks', [ViewController::class, 'savedLooks'])->name('saved-looks'); // ->middleware('auth');
Route::get('/tutorials', [ViewController::class, 'tutorials'])->name('tutorials');

// --- Web Authentication Routes (using Socialite for web session) ---
Route::get('/login', [GoogleLoginController::class, 'redirectToGoogle'])->name('login'); // Redirect to Google
Route::get('/auth/google/callback', [GoogleLoginController::class, 'handleGoogleCallback']); // Handle callback
Route::post('/logout', [GoogleLoginController::class, 'logout'])->name('logout'); // ->middleware('auth');
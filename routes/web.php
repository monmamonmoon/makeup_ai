<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ViewController; // Controller for showing pages
use App\Http\Controllers\Auth\GoogleLoginController; // Controller for web login/logout

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| These routes handle the browser requests for your web application pages
| and the web-based Google authentication flow.
*/

// --- Web Page Routes ---
// When a user visits the base URL '/', show the welcome page
Route::get('/', [ViewController::class, 'welcome'])->name('welcome');

// When a user visits '/analyze', show the analysis page
Route::get('/analyze', [ViewController::class, 'analysis'])->name('analyze');

// When a user visits '/saved-looks', show the saved looks page
// We will add authentication middleware later: ->middleware('auth')
Route::get('/saved-looks', [ViewController::class, 'savedLooks'])->name('saved-looks');

// When a user visits '/tutorials', show the tutorials page
Route::get('/tutorials', [ViewController::class, 'tutorials'])->name('tutorials');


// --- Web Authentication Routes ---
// When a user visits '/login', redirect them to Google to sign in
Route::get('/login', [GoogleLoginController::class, 'redirectToGoogle'])->name('login');

// When Google sends the user back after login, handle the callback
Route::get('/auth/google/callback', [GoogleLoginController::class, 'handleGoogleCallback']);

// When a logged-in user submits a POST request to '/logout', log them out
// We will add authentication middleware later: ->middleware('auth')
Route::post('/logout', [GoogleLoginController::class, 'logout'])->name('logout');

// --- TEMPORARY /list-models route REMOVED ---
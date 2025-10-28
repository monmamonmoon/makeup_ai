<?php

namespace App\Http\Controllers\Auth; // Correct namespace

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Use Laravel's main Auth facade
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\RedirectResponse; // For redirect return types

class GoogleLoginController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     * This is called by the /login route.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        Log::info('Web login: Redirecting to Google with prompt=select_account...');

        // Add ->with(["prompt" => "select_account"]) to force account selection
        return Socialite::driver('google')
            ->with(["prompt" => "select_account"])
            ->redirect();
    }

    /**
     * Obtain the user information from Google.
     * This is called by the /auth/google/callback route.
     */
    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        Log::info('Web login: Handling Google callback...');
        try {
            // Get user info from Google
            $googleUser = Socialite::driver('google')->user();

            // Find user in our database by email
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // User exists, update details if needed
                $user->update([
                    'google_id' => $user->google_id ?? $googleUser->getId(),
                    'name' => $googleUser->getName(),
                ]);
                Log::info('Web login: Existing user logged in.', ['user_id' => $user->id]);
            } else {
                // User doesn't exist, create them
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => bcrypt('password_placeholder_' . now()) // Placeholder
                ]);
                Log::info('Web login: New user created.', ['user_id' => $user->id]);
            }

            // --- THIS IS THE KEY ---
            // Log the user into the application's web session
            Auth::login($user, true); // 'true' = remember me
            // --- END KEY ---

            Log::info('Web login: Session created, redirecting to welcome.');
            return redirect()->route('welcome'); // Redirect to the main welcome page

        } catch (\Exception $e) {
            Log::error('Google Web Login Failed: ' . $e->getMessage());
            return redirect()->route('welcome')->with('error', 'Login failed. Please try again.');
        }
    }

    /**
     * Log the user out of the application.
     * This is called by the /logout route.
     */
    public function logout(Request $request): RedirectResponse
    {
        Log::info('Web login: User logging out.', ['user_id' => Auth::id()]);
        Auth::logout(); // Logs out of the web session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('welcome'); // Redirect to welcome page after logout
    }
}
<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function handleGoogleCallback() {
        Log::info('TEMP TOKEN: Handling Google callback...');
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $user = User::updateOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'google_id' => $googleUser->getId(),
                    'password' => bcrypt('temp_pw_'.now())
                ]
            );
            $token = $user->createToken('temp_token_for_blade')->plainTextToken;
            Log::info('TEMP TOKEN: Token generated: ' . $token);
            // Return the view that displays the token
            return view('auth.callback', ['token' => $token, 'user' => $user]);
        } catch (\Exception $e) {
            Log::error('TEMP TOKEN - Callback Failed: ' . $e->getMessage());
            return response('Error getting token: ' . $e->getMessage(), 500);
        }
    }

    public function redirectToGoogle() {
         Log::info('TEMP TOKEN: Redirecting to Google...');
         // Make sure redirect() uses the URI from config/services.php (which reads .env)
         return Socialite::driver('google')->stateless()->redirect();
    }
    // Add placeholders if routes/api.php still defines them
    public function user(Request $request) { return response()->json([]); }
    public function logout(Request $request) { return response()->json([]); }
}
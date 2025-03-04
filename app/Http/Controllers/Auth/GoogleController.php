<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the callback from Google.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Check if user already exists
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // Create a new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => Hash::make(rand(1, 10000)),
                ]);
            } else {
                // Update the google_id if it's not set
                if (empty($user->google_id)) {
                    $user->google_id = $googleUser->getId();
                    $user->save();
                }
            }

            // Manual login instead of Auth facade
            auth()->login($user, true); // true = remember me

            // More secure session handling
            session()->regenerate();

            // Debug line to verify user is authenticated
            if (auth()->check()) {
                return redirect('/dashboard');
            } else {
                // This will help you see if authentication isn't working
                return redirect('/login')->withErrors('Authentication failed after Google login');
            }
        } catch (Exception $e) {
            return redirect('/login')->withErrors('Google authentication failed: ' . $e->getMessage());
        }
    }
}

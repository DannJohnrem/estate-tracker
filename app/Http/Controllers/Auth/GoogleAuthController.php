<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect to the Google OAuth consent screen.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the callback after authorizing with Google.
     */
    public function callback(): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->user();

        $user = User::where('email', $googleUser->getEmail())->first();

        if (! $user) {
            $user = new User();
            $user->fill([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'email_verified_at' => now(), // already verified since it came from Google
                'status' => 'pending',
            ]);
            $user->save();
        } elseif (! $user->google_id) {
            $user->google_id = $googleUser->getId();
            $user->avatar = $user->avatar ?? $googleUser->getAvatar();
            $user->save();
        }

        if (! $user->isApproved()) {
            return redirect()->route('login')->with('status', match ($user->status) {
                'pending' => 'Your account is still pending approval from an administrator.',
                'rejected' => 'Your access request has been rejected. Please contact an administrator.',
                default => 'You are not able to log in at this time.',
            });
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard'));
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->status !== 'approved') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = match ($user->status) {
                'pending' => 'Your account is still pending approval from an administrator.',
                'rejected' => 'Your access request has been rejected. Please contact an administrator.',
                default => 'You are not able to log in at this time.',
            };

            return redirect()->route('login')->with('status', $message);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureProfileIsCompleteAndApproved
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if (!$user->phone_number || !$user->bio || !$user->profile_picture) {
            return redirect()->route('profile.edit')->with('warning', 'Please complete your profile.');
        }

        if (!$user->is_approved) {
            return redirect()->route('profile.edit')->with('warning', 'Your profile is pending admin approval.');
        }

        return $next($request);
    }
}

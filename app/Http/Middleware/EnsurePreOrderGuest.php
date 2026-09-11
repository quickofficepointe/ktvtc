<?php
// app/Http/Middleware/EnsurePreOrderGuest.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePreOrderGuest
{
    /**
     * Prevent already-authenticated pre-order users from visiting
     * login/setup/forgot pages. Bounce them to the home page instead.
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->session()->has(EnsurePreOrderAuthenticated::SESSION_KEY)) {
            return redirect()->route('pre-order.home');
        }

        return $next($request);
    }
}
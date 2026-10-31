<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allows any of the listed roles, e.g. 'role.any:6,7' for cafeteria or finance.
 */
class CheckAnyRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (Auth::check() && in_array((int) Auth::user()->role, array_map('intval', $roles), true)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return redirect('/home')->with('error', 'Unauthorized access.');
    }
}

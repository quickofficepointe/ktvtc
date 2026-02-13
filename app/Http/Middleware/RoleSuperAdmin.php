<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Auth;
class RoleSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || $user->role != 0) { // Role ID 0 for super admin
            abort(403, 'Unauthorized access. Super admin privileges required.');
        }

        return $next($request);
    }
}

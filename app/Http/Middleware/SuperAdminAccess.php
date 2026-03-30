<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SuperAdminAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Allow access to profile.edit for ALL users
        if ($request->routeIs('profile.edit')) {
            return $next($request);
        }

        // If user is NOT super admin (role 0), redirect to their dashboard
        if ($user->role != 0) {
            Log::warning('Non-super admin attempted to access super admin route', [
                'user_id' => $user->id,
                'role' => $user->role,
                'path' => $request->path()
            ]);

            return redirect()->route($this->getDashboardRoute($user->role));
        }

        $request->attributes->add(['is_super_admin' => true]);
        return $next($request);
    }

    private function getDashboardRoute($role)
    {
        // If not approved, go to student dashboard
        if (!Auth::user()->is_approved) {
            return 'student.dashboard';
        }

        $routes = [
            1 => 'mschool.dashboard',
            2 => 'admin.dashboard',
            3 => 'scholarship.dashboard',
            4 => 'library.dashboard',
            5 => 'student.dashboard',
            6 => 'cafeteria.dashboard',
            7 => 'finance.dashboard',
            8 => 'trainers.dashboard',
            9 => 'website.dashboard',
        ];

        return $routes[$role] ?? 'student.dashboard';
    }
}

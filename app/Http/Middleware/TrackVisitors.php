<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Visitor;

class TrackVisitors
{
    public function handle(Request $request, Closure $next)
    {
        // Skip tracking for certain paths (admin, dashboard, etc.)
        if (!$this->shouldTrack($request)) {
            return $next($request);
        }

        // Check if this IP has visited recently (last 30 minutes)
        $recentVisit = Visitor::where('ip_address', $request->ip())
            ->where('created_at', '>', now()->subMinutes(30))
            ->exists();

        if (!$recentVisit) {
            Visitor::create([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'path' => $request->path(),
                'referrer' => $request->header('referer'),
            ]);
        }

        return $next($request);
    }

    private function shouldTrack(Request $request)
    {
        $excludedPaths = [
            'admin',
            'dashboard',
            'api',
            'login',
            'register',
        ];

        foreach ($excludedPaths as $path) {
            if (str_contains($request->path(), $path)) {
                return false;
            }
        }

        return true;
    }
}

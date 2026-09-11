<?php
// app/Http/Middleware/EnsurePreOrderAuthenticated.php

namespace App\Http\Middleware;

use App\Models\PreOrderAccount;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsurePreOrderAuthenticated
{
    /**
     * The session key we use to remember which pre-order account is logged in.
     */
    public const SESSION_KEY = 'preorder_account_id';

    public function handle(Request $request, Closure $next)
    {
        $accountId = $request->session()->get(self::SESSION_KEY);

        if (!$accountId) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Not authenticated'], 401);
            }

            return redirect()
                ->route('pre-order.login')
                ->with('error', 'Please log in to continue.');
        }

        $account = PreOrderAccount::find($accountId);

        if (!$account || !$account->is_active) {
            $request->session()->forget(self::SESSION_KEY);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Account inactive'], 403);
            }

            return redirect()
                ->route('pre-order.login')
                ->with('error', 'Your account is inactive. Contact the cafeteria.');
        }

        // Share the authenticated account with all downstream code
        $request->attributes->set('preorder_account', $account);
        view()->share('preorderAccount', $account);

        return $next($request);
    }
}

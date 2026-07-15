<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register your custom middleware aliases
        $middleware->alias([
            'role.mschool' => \App\Http\Middleware\CheckMschoolRole::class,
            'role.admin' => \App\Http\Middleware\CheckAdminRole::class,
            'role.scholarship' => \App\Http\Middleware\CheckScholarshipRole::class,
            'role.library' => \App\Http\Middleware\CheckLibraryRole::class,
            'role.student' => \App\Http\Middleware\CheckStudentRole::class,
            'role.cafeteria' => \App\Http\Middleware\CheckCafeteriaRole::class,
            'role.finance' => \App\Http\Middleware\CheckFinanceRole::class,
            'role.trainers' => \App\Http\Middleware\CheckTrainersRole::class,
            'role.website' => \App\Http\Middleware\CheckWebsiteRole::class,
            'profile.complete' => \App\Http\Middleware\EnsureProfileIsCompleteAndApproved::class,
            'role.super-admin' => \App\Http\Middleware\RoleSuperAdmin::class,
            'super.admin.access' => \App\Http\Middleware\SuperAdminAccess::class, // For global access
        ]);

        // Modify the VerifyCsrfToken middleware to exclude payment callbacks
        $middleware->validateCsrfTokens(except: [
            // Event payment callbacks
            'events/payment/callback',
            '/events/payment/callback',

            // Cafeteria payment routes
            '/ktvtc/cafeteria/check-payment-status',
            'ktvtc/cafeteria/check-payment-status',

            // KCB sales callback
            '/api/kcb/sales/callback',
            'api/kcb/sales/callback',

            // KCB IPN - School Fees (Till 7664166)
            '/api/kcb/ipn',
            'api/kcb/ipn',
            '/api/kcb/ipn/payment-notification',
            'api/kcb/ipn/payment-notification',

            // KCB IPN - Card Funding (Till 7722609)
            '/api/kcb/card/ipn',
            'api/kcb/card/ipn',

            // KCB STK Callback - Card Funding
            '/api/kcb/card/funding/callback',
            'api/kcb/card/funding/callback',

            // KCB Status Check - Card Funding
            '/api/kcb/card/funding/status',
            'api/kcb/card/funding/status',

            // M-Pesa general callback
            '/mpesa/callback',
            'mpesa/callback',

            // Application payment routes
            '/application-payment/callback',
            'application-payment/callback',
            '/application-payment/*',
            'application-payment/*',

            // Add wildcard for any payment-related callbacks
            '*/payment/callback',
            '*/callback',

            // Add any other payment provider callbacks here
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Custom exception handling can go here
    })->create();

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

        // Modify the VerifyCsrfToken middleware to exclude payment callback
        $middleware->validateCsrfTokens(except: [
            'events/payment/callback',
            // Add other routes that should be excluded from CSRF protection
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Custom exception handling can go here
    })->create();

<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\Department;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Share departments with all views using app.blade.php
        View::composer('layouts.app', function ($view) {
            $view->with('departments', Department::where('is_active', true)->orderBy('name', 'asc')->get());
        });
    }
}

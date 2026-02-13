<?php

namespace App\Providers;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
           View::composer('ktvtc.cafeteria.layout.cafeterialayout', function ($view) {
        $view->with('categories', ProductCategory::all());
    });
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use App\Models\KoordinatorProdi;

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
        // Explicit route model binding untuk koordinator
        Route::model('koordinator', KoordinatorProdi::class);
    }
}

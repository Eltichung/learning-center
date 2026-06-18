<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        // Render terminate HTTPS ở proxy nên ép sinh URL https khi chạy production
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}

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
        // Render terminate HTTPS ở proxy nên ép sinh URL https khi:
        //  - chạy production, HOẶC
        //  - APP_URL khai báo https, HOẶC
        //  - request đang đi qua proxy với X-Forwarded-Proto=https
        $appUrlIsHttps = str_starts_with((string) config('app.url'), 'https://');
        $forwardedHttps = strtolower((string) request()->server('HTTP_X_FORWARDED_PROTO')) === 'https';
        if (app()->environment('production') || $appUrlIsHttps || $forwardedHttps) {
            URL::forceScheme('https');
        }
    }
}

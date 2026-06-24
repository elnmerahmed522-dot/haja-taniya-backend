<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // إجبار لارافل على استخدام HTTPS في الـ Production لمنع حلقة الـ Redirects
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        $host = request()->getHost();
        $isLocalHost = in_array($host, ['localhost', '127.0.0.1'], true);

        // Force HTTPS in real production, but keep Laravel's local server on HTTP.
        if (!$isLocalHost && app()->environment('production') && Str::startsWith(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
    }
}

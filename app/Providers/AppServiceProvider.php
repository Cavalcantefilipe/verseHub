<?php

namespace App\Providers;

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
        // Fix: Railway passes PORT as string, Laravel ServeCommand expects int
        if (isset($_ENV['PORT'])) {
            $_ENV['SERVER_PORT'] = (int) $_ENV['PORT'];
            $_SERVER['SERVER_PORT'] = (int) $_ENV['PORT'];
        }
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class MiddlewareServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register custom middleware aliases to router
        $this->app['router']->aliasMiddleware('set.perusahaan', \App\Http\Middleware\SetPerusahaanFromUrl::class);
        $this->app['router']->aliasMiddleware('upload.limits', \App\Http\Middleware\SetUploadLimits::class);
    }
}

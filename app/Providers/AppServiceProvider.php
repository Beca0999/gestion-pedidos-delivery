<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use App\Http\Middleware\LogActivity;

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
        // Register activity logging middleware on web and api groups so all
        // requests are recorded for the pizarrón activity feed.
        try {
            $router = $this->app->make(Router::class);
            $router->pushMiddlewareToGroup('web', LogActivity::class);
            $router->pushMiddlewareToGroup('api', LogActivity::class);
        } catch (\Throwable $e) {
            // If router isn't available during some early bootstrap steps, ignore.
        }
    }
}

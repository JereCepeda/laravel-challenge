<?php

namespace App\Providers;

use App\Http\Middleware\Authenticate;
use App\Services\AuthService;
use App\Services\UserValidationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AuthService::class);
        $this->app->singleton(Authenticate::class);
        $this->app->singleton(UserValidationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}

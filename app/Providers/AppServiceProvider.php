<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Debug\ExceptionHandler;
use App\Repositories\Contracts\AuthRepositoryInterface;
use App\Repositories\Contracts\DemoRepositoryInterface;
use App\Repositories\Eloquent\AuthRepository as EloquentAuthRepository;
use App\Repositories\Eloquent\DemoRepository as EloquentDemoRepository;
use App\Services\Contracts\AuthServiceInterface;
use App\Services\Contracts\DemoServiceInterface;
use App\Services\AuthService;
use App\Services\DemoService;
use App\Exceptions\Handler;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(AuthRepositoryInterface::class, EloquentAuthRepository::class);
        $this->app->bind(DemoRepositoryInterface::class, EloquentDemoRepository::class);
        
        // Service bindings
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(DemoServiceInterface::class, DemoService::class);
        
        // Register custom exception handler
        $this->app->singleton(ExceptionHandler::class, Handler::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

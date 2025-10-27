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
use App\Services\Contracts\BookingServiceInterface;
use App\Services\Contracts\ServiceServiceInterface;
use App\Services\Contracts\BranchServiceInterface;
use App\Services\AuthService;
use App\Services\DemoService;
use App\Services\BookingService;
use App\Services\ServiceService;
use App\Services\BranchService;
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
        
        // New repository bindings
        $this->app->bind(
            \App\Repositories\Contracts\ServiceCategoryRepositoryInterface::class,
            \App\Repositories\Eloquent\ServiceCategoryRepository::class
        );
        
        $this->app->bind(
            \App\Repositories\Contracts\ServiceRepositoryInterface::class,
            \App\Repositories\Eloquent\ServiceRepository::class
        );
        
        $this->app->bind(
            \App\Repositories\Contracts\BranchRepositoryInterface::class,
            \App\Repositories\Eloquent\BranchRepository::class
        );
        
        $this->app->bind(
            \App\Repositories\Contracts\StaffRepositoryInterface::class,
            \App\Repositories\Eloquent\StaffRepository::class
        );
        
        $this->app->bind(
            \App\Repositories\Contracts\BookingRepositoryInterface::class,
            \App\Repositories\Eloquent\BookingRepository::class
        );
        
        $this->app->bind(
            \App\Repositories\Contracts\ReviewRepositoryInterface::class,
            \App\Repositories\Eloquent\ReviewRepository::class
        );
        
        $this->app->bind(
            \App\Repositories\Contracts\PromotionRepositoryInterface::class,
            \App\Repositories\Eloquent\PromotionRepository::class
        );
        
        $this->app->bind(
            \App\Repositories\Contracts\PostRepositoryInterface::class,
            \App\Repositories\Eloquent\PostRepository::class
        );
        
        $this->app->bind(
            \App\Repositories\Contracts\ContactRepositoryInterface::class,
            \App\Repositories\Eloquent\ContactRepository::class
        );
        
        $this->app->bind(
            \App\Repositories\Contracts\ChatRepositoryInterface::class,
            \App\Repositories\Eloquent\ChatRepository::class
        );
        
        $this->app->bind(
            \App\Repositories\Contracts\UserRepositoryInterface::class,
            \App\Repositories\Eloquent\UserRepository::class
        );
        
        // Service bindings
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(DemoServiceInterface::class, DemoService::class);
        $this->app->bind(BookingServiceInterface::class, BookingService::class);
        $this->app->bind(ServiceServiceInterface::class, ServiceService::class);
        $this->app->bind(BranchServiceInterface::class, BranchService::class);
        
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

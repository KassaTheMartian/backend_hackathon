<?php

namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Debug\ExceptionHandler;

use App\Exceptions\Handler;

use App\Repositories\Contracts\AuthRepositoryInterface;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\BranchRepositoryInterface;
use App\Repositories\Contracts\ContactRepositoryInterface;
// use App\Repositories\Contracts\DemoRepositoryInterface;
use App\Repositories\Contracts\OtpRepositoryInterface;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Repositories\Contracts\PromotionRepositoryInterface;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use App\Repositories\Contracts\ServiceCategoryRepositoryInterface;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use App\Repositories\Contracts\StaffRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;

use App\Repositories\Eloquent\AuthRepository as EloquentAuthRepository;
use App\Repositories\Eloquent\BookingRepository;
use App\Repositories\Eloquent\BranchRepository;
use App\Repositories\Eloquent\ContactRepository;
// use App\Repositories\Eloquent\DemoRepository as EloquentDemoRepository;
use App\Repositories\Eloquent\OtpRepository;
use App\Repositories\Eloquent\PaymentRepository;
use App\Repositories\Eloquent\PostRepository;
use App\Repositories\Eloquent\PromotionRepository;
use App\Repositories\Eloquent\ReviewRepository;
use App\Repositories\Eloquent\ServiceCategoryRepository;
use App\Repositories\Eloquent\ServiceRepository;
use App\Repositories\Eloquent\StaffRepository;
use App\Repositories\Eloquent\UserRepository;

use App\Services\Contracts\AuthServiceInterface;
use App\Services\Contracts\BookingServiceInterface;
use App\Services\Contracts\BranchServiceInterface;
use App\Services\Contracts\ChatbotServiceInterface;
use App\Services\Contracts\ChatRealTimeServiceInterface;
use App\Services\Contracts\ContactServiceInterface;
use App\Services\Contracts\DemoServiceInterface;
use App\Services\Contracts\PaymentServiceInterface;
use App\Services\Contracts\PostServiceInterface;
use App\Services\Contracts\ProfileServiceInterface;
use App\Services\Contracts\ReviewServiceInterface;
use App\Services\Contracts\ServiceServiceInterface;
use App\Services\Contracts\StaffServiceInterface;

use App\Services\AuthService;
use App\Services\BookingService;
use App\Services\BranchService;
use App\Services\ChatbotService;
use App\Services\ChatRealTimeService;
use App\Services\ContactService;
use App\Services\DemoService;
use App\Services\PaymentService;
use App\Services\PostService;
use App\Services\ProfileService;
use App\Services\ReviewService;
use App\Services\ServiceService;
use App\Services\StaffService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(AuthRepositoryInterface::class, EloquentAuthRepository::class);
        // Demo bindings removed
        $this->app->bind(ServiceCategoryRepositoryInterface::class, ServiceCategoryRepository::class);
        $this->app->bind(ServiceRepositoryInterface::class, ServiceRepository::class);
        $this->app->bind(BranchRepositoryInterface::class, BranchRepository::class);
        $this->app->bind(StaffRepositoryInterface::class, StaffRepository::class);
        $this->app->bind(BookingRepositoryInterface::class, BookingRepository::class);
        $this->app->bind(ReviewRepositoryInterface::class, ReviewRepository::class);
        $this->app->bind(PromotionRepositoryInterface::class, PromotionRepository::class);
        $this->app->bind(PostRepositoryInterface::class, PostRepository::class);
        $this->app->bind(ContactRepositoryInterface::class, ContactRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(OtpRepositoryInterface::class, OtpRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
        
        // Service bindings
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        // Demo service binding removed
        $this->app->bind(BookingServiceInterface::class, BookingService::class);
        $this->app->bind(ServiceServiceInterface::class, ServiceService::class);
        $this->app->bind(StaffServiceInterface::class, StaffService::class);
        $this->app->bind(BranchServiceInterface::class, BranchService::class);
        $this->app->bind(ReviewServiceInterface::class, ReviewService::class);
        $this->app->bind(PostServiceInterface::class, PostService::class);
        $this->app->bind(ContactServiceInterface::class, ContactService::class);
        $this->app->bind(ProfileServiceInterface::class, ProfileService::class);
        $this->app->bind(ChatbotServiceInterface::class, ChatbotService::class);
        $this->app->bind(ChatRealTimeServiceInterface::class, ChatRealTimeService::class);
        $this->app->bind(PaymentServiceInterface::class, PaymentService::class);
        
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

<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register custom middleware
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'throttle.custom' => \App\Http\Middleware\ThrottleRequests::class,
            'log.api' => \App\Http\Middleware\LogApiRequests::class,
            'rate.limit' => \App\Http\Middleware\RateLimitMiddleware::class,
            'request.id' => \App\Http\Middleware\RequestId::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);

        // Add middleware to API group
        $middleware->appendToGroup('api', [
            \App\Http\Middleware\RequestId::class,
            \App\Http\Middleware\LogApiRequests::class,
            \App\Http\Middleware\AuthenticateApi::class,
        ]);
        
        // Configure stateful API (prevent redirect to login for API routes)
        $middleware->statefulApi();

    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Register custom exception handling
        $exceptions->dontReport([
            \App\Exceptions\BaseException::class,
            \App\Exceptions\BusinessException::class,
            \App\Exceptions\ResourceNotFoundException::class,
            \Illuminate\Auth\AuthenticationException::class,
            \Illuminate\Auth\Access\AuthorizationException::class,
            \Illuminate\Database\Eloquent\ModelNotFoundException::class,
            \Illuminate\Validation\ValidationException::class,
            \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
            \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class,
            \Illuminate\Http\Exceptions\ThrottleRequestsException::class,
        ]);
    })->create();

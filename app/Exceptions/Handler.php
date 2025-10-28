<?php

namespace App\Exceptions;

use App\Http\Responses\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e): Response
    {
        // Handle API requests with JSON responses
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Handle API exceptions with consistent JSON responses.
     */
    private function handleApiException(Request $request, Throwable $e): JsonResponse
    {
        // Custom application exceptions
        if ($e instanceof BaseException) {
            return ApiResponse::error(
                $e->getMessage(),
                $e->getTitle(),
                $e->getErrorCode(),
                $e->getStatusCode(),
                $request->headers->get('X-Request-Id')
            );
        }

        // Validation exceptions
        if ($e instanceof ValidationException) {
            return ApiResponse::validationError(
                $e->errors(),
                __('validation.failed'),
                'VALIDATION_FAILED'
            );
        }

        // Authentication exceptions
        if ($e instanceof AuthenticationException) {
            return ApiResponse::unauthorized(
                'Authentication required',
                'UNAUTHENTICATED'
            );
        }

        // Authorization exceptions
        if ($e instanceof AuthorizationException) {
            return ApiResponse::forbidden(
                'Access denied',
                'ACCESS_DENIED'
            );
        }

        if ($e instanceof AccessDeniedHttpException) {
            return ApiResponse::forbidden(
                'Access denied',
                'ACCESS_DENIED'
            );
        }

        // Model not found exceptions
        if ($e instanceof ModelNotFoundException) {
            return ApiResponse::notFound(
                'Resource not found',
                'RESOURCE_NOT_FOUND'
            );
        }

        // HTTP not found exceptions
        if ($e instanceof NotFoundHttpException) {
            return ApiResponse::notFound(
                'Endpoint not found',
                'ENDPOINT_NOT_FOUND'
            );
        }

        // Method not allowed exceptions
        if ($e instanceof MethodNotAllowedHttpException) {
            return ApiResponse::error(
                'Method not allowed',
                'Method Not Allowed',
                'METHOD_NOT_ALLOWED',
                405,
                $request->headers->get('X-Request-Id')
            );
        }

        // Rate limiting exceptions
        if ($e instanceof TooManyRequestsHttpException) {
            return ApiResponse::tooManyRequests(
                'Too many requests',
                'RATE_LIMIT_EXCEEDED'
            );
        }

        // Database connection exceptions
        if ($e instanceof \PDOException) {
            return ApiResponse::serverError(
                'Database connection error',
                'DATABASE_ERROR',
                $request->headers->get('X-Request-Id')
            );
        }

        // Generic server errors
        if ($e instanceof \Error || $e instanceof \ParseError) {
            return ApiResponse::serverError(
                'Internal server error',
                'INTERNAL_ERROR',
                $request->headers->get('X-Request-Id')
            );
        }

        // Log unexpected exceptions for debugging
        $this->logException($request, $e);

        // Default error response
        return ApiResponse::serverError(
            config('app.debug') ? $e->getMessage() : 'An unexpected error occurred',
            'INTERNAL_ERROR',
            $request->headers->get('X-Request-Id')
        );
    }

    /**
     * Log exceptions with context for debugging.
     */
    private function logException(Request $request, Throwable $e): void
    {
        $context = [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'request_id' => $request->headers->get('X-Request-Id'),
            'user_id' => $request->user()?->id,
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        // Log to different channels based on exception type
        if ($e instanceof \Error || $e instanceof \ParseError) {
            Log::channel('api')->critical('Critical error occurred', $context);
        } elseif ($e instanceof \PDOException) {
            Log::channel('api')->error('Database error occurred', $context);
        } else {
            Log::channel('api')->error('Unexpected error occurred', $context);
        }
    }

    /**
     * Determine if the exception should be reported.
     */
    public function shouldReport(Throwable $e): bool
    {
        // Don't report validation exceptions
        if ($e instanceof ValidationException) {
            return false;
        }

        // Don't report authentication exceptions
        if ($e instanceof AuthenticationException) {
            return false;
        }

        // Don't report authorization exceptions
        if ($e instanceof AuthorizationException) {
            return false;
        }

        if ($e instanceof AccessDeniedHttpException) {
            return false;
        }

        // Don't report not found exceptions
        if ($e instanceof NotFoundHttpException || $e instanceof ModelNotFoundException) {
            return false;
        }

        // Don't report method not allowed exceptions
        if ($e instanceof MethodNotAllowedHttpException) {
            return false;
        }

        // Don't report rate limiting exceptions
        if ($e instanceof TooManyRequestsHttpException) {
            return false;
        }

        // Don't report custom application exceptions (they're already handled)
        if ($e instanceof BaseException) {
            return false;
        }

        return parent::shouldReport($e);
    }
}

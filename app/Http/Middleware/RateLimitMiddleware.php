<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    private RateLimiter $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $key = 'api', int $maxAttempts = 60, int $decayMinutes = 1): Response
    {
        $key = $this->resolveRequestSignature($request, $key);
        
        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            return $this->buildResponse($key, $maxAttempts);
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        $response = $next($request);

        return $this->addHeaders(
            $response,
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }

    /**
     * Resolve request signature for rate limiting
     */
    protected function resolveRequestSignature(Request $request, string $key): string
    {
        $user = $request->user();
        $ip = $request->ip();
        
        if ($user) {
            return sha1($key . '|' . $user->id);
        }
        
        return sha1($key . '|' . $ip);
    }

    /**
     * Create a 'too many attempts' response
     */
    protected function buildResponse(string $key, int $maxAttempts): JsonResponse
    {
        $retryAfter = $this->limiter->availableIn($key);
        
        return ApiResponse::tooManyRequests(
            'Too many requests. Please try again later.',
            'RATE_LIMIT_EXCEEDED'
        )->header('Retry-After', $retryAfter)
         ->header('X-RateLimit-Limit', $maxAttempts)
         ->header('X-RateLimit-Remaining', 0);
    }

    /**
     * Add the limit header information to the given response
     */
    protected function addHeaders(Response $response, int $maxAttempts, int $remainingAttempts): Response
    {
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remainingAttempts,
        ]);

        return $response;
    }

    /**
     * Calculate the number of remaining attempts
     */
    protected function calculateRemainingAttempts(string $key, int $maxAttempts): int
    {
        return max(0, $maxAttempts - $this->limiter->attempts($key));
    }
}

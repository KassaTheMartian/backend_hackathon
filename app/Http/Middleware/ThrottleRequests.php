<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests as BaseThrottleRequests;
use Symfony\Component\HttpFoundation\Response;

class ThrottleRequests extends BaseThrottleRequests
{
    /**
     * Create a 'too many attempts' response.
     */
    protected function buildResponse(string $key, int $maxAttempts): Response
    {
        $retryAfter = $this->limiter->availableIn($key);

        $response = ApiResponse::tooManyRequests(
            'Too many requests. Please try again later.',
            'RATE_LIMIT_EXCEEDED'
        );

        return $this->addHeaders(
            $response,
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts),
            $retryAfter
        );
    }

    /**
     * Add the limit header information to the given response.
     */
    protected function addHeaders(Response $response, $maxAttempts, $remainingAttempts, $retryAfter = null): Response
    {
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remainingAttempts,
        ]);

        if (!is_null($retryAfter)) {
            $response->headers->add([
                'Retry-After' => $retryAfter,
            ]);
        }

        return $response;
    }
}

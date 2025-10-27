<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class RequestId
{
    /**
     * Ensure every request has a stable X-Request-Id and echo it back on the response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $request->headers->get('X-Request-Id')
            ?? $request->headers->get('X-Correlation-Id')
            ?? (string) Str::uuid();
        $correlationId = $request->headers->get('X-Correlation-Id') ?? $requestId;

        // Normalize both headers for downstream consumers
        $request->headers->set('X-Request-Id', $requestId);
        $request->headers->set('X-Correlation-Id', $correlationId);

        /** @var Response $response */
        $response = $next($request);
        $response->headers->set('X-Request-Id', $requestId);
        $response->headers->set('X-Correlation-Id', $correlationId);

        return $response;
    }
}



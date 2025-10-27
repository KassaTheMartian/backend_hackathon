<?php

namespace App\Http\Middleware;

use App\Services\LoggingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequests
{
    private LoggingService $loggingService;
    private float $startTime;

    public function __construct(LoggingService $loggingService)
    {
        $this->loggingService = $loggingService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->startTime = microtime(true);
        
        // Log the incoming request
        $this->loggingService->logRequest($request, $this->startTime);

        // Process the request
        $response = $next($request);

        // Log the response
        $this->loggingService->logResponse($request, $response, $this->startTime);

        return $response;
    }
}

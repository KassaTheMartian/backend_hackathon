<?php

namespace App\Services;

use App\Services\Contracts\LoggingServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * Service for handling application logging.
 *
 * Manages API request/response logging, business events, security events, and performance metrics.
 */
class LoggingService implements LoggingServiceInterface
{
    /**
     * Log API request
     */
    public function logRequest(Request $request, float $startTime): void
    {
        $context = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => Auth::user(),
            'request_id' => $request->headers->get('X-Request-Id'),
            'correlation_id' => $request->headers->get('X-Correlation-Id'),
            'start_time' => $startTime,
            'headers' => $this->sanitizeHeaders($request->headers->all()),
        ];

        // Log request body for non-GET requests (excluding sensitive data)
        if ($request->method() !== 'GET') {
            $context['body'] = $this->sanitizeRequestBody($request->all());
        }

        Log::channel('api')->info('API Request', $context);
    }

    /**
     * Log API response
     */
    public function logResponse(Request $request, $response, float $startTime): void
    {
        $duration = microtime(true) - $startTime;
        
        $context = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status_code' => $response->getStatusCode(),
            'duration_ms' => round($duration * 1000, 2),
            'user_id' => Auth::user(),
            'request_id' => $request->headers->get('X-Request-Id'),
            'correlation_id' => $request->headers->get('X-Correlation-Id'),
        ];

        // Log response body for errors
        if ($response->getStatusCode() >= 400) {
            $context['response_body'] = $response->getContent();
        }

        $logLevel = $this->getLogLevel($response->getStatusCode());
        Log::channel('api')->$logLevel('API Response', $context);
    }

    /**
     * Log business events
     */
    public function logBusinessEvent(string $event, array $data = [], ?int $userId = null): void
    {
        $context = [
            'event' => $event,
            'user_id' => $userId ?? Auth::id(),
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ];

        Log::channel('business')->info("Business Event: {$event}", $context);
    }

    /**
     * Log security events
     */
    public function logSecurityEvent(string $event, array $data = [], ?int $userId = null): void
    {
        $context = [
            'event' => $event,
            'user_id' => $userId ?? Auth::id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ];

        Log::channel('security')->warning("Security Event: {$event}", $context);
    }

    /**
     * Log performance metrics
     */
    public function logPerformance(string $operation, float $duration, array $metadata = []): void
    {
        $context = [
            'operation' => $operation,
            'duration_ms' => round($duration * 1000, 2),
            'metadata' => $metadata,
            'timestamp' => now()->toISOString(),
        ];

        Log::channel('performance')->info("Performance: {$operation}", $context);
    }

    /**
     * Sanitize headers to remove sensitive information
     */
    private function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = ['authorization', 'cookie', 'x-api-key', 'x-auth-token'];
        
        foreach ($sensitiveHeaders as $header) {
            if (isset($headers[$header])) {
                $headers[$header] = ['***'];
            }
        }

        return $headers;
    }

    /**
     * Sanitize request body to remove sensitive information
     */
    private function sanitizeRequestBody(array $body): array
    {
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'api_key', 'secret'];
        
        foreach ($sensitiveFields as $field) {
            if (isset($body[$field])) {
                $body[$field] = '***';
            }
        }

        return $body;
    }

    /**
     * Get appropriate log level based on status code
     */
    private function getLogLevel(int $statusCode): string
    {
        if ($statusCode >= 500) {
            return 'error';
        } elseif ($statusCode >= 400) {
            return 'warning';
        } else {
            return 'info';
        }
    }
}

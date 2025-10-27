<?php

namespace App\Services\Contracts;

use Illuminate\Http\Request;

interface LoggingServiceInterface
{
    /**
     * Log API request
     */
    public function logRequest(Request $request, float $startTime): void;

    /**
     * Log API response
     */
    public function logResponse(Request $request, $response, float $startTime): void;

    /**
     * Log business events
     */
    public function logBusinessEvent(string $event, array $data = [], ?int $userId = null): void;

    /**
     * Log security events
     */
    public function logSecurityEvent(string $event, array $data = [], ?int $userId = null): void;

    /**
     * Log performance metrics
     */
    public function logPerformance(string $operation, float $duration, array $metadata = []): void;
}

<?php

namespace App\Services\Contracts;

use Illuminate\Http\Request;

interface LoggingServiceInterface
{
    /**
     * Log API request.
     *
     * @param Request $request The HTTP request.
     * @param float $startTime The start time.
     * @return void
     */
    public function logRequest(Request $request, float $startTime): void;

    /**
     * Log API response.
     *
     * @param Request $request The HTTP request.
     * @param mixed $response The response.
     * @param float $startTime The start time.
     * @return void
     */
    public function logResponse(Request $request, $response, float $startTime): void;

    /**
     * Log business events.
     *
     * @param string $event The event name.
     * @param array $data The event data.
     * @param int|null $userId The user ID.
     * @return void
     */
    public function logBusinessEvent(string $event, array $data = [], ?int $userId = null): void;

    /**
     * Log security events.
     *
     * @param string $event The event name.
     * @param array $data The event data.
     * @param int|null $userId The user ID.
     * @return void
     */
    public function logSecurityEvent(string $event, array $data = [], ?int $userId = null): void;

    /**
     * Log performance metrics.
     *
     * @param string $operation The operation name.
     * @param float $duration The duration.
     * @param array $metadata The metadata.
     * @return void
     */
    public function logPerformance(string $operation, float $duration, array $metadata = []): void;
}

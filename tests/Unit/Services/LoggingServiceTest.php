<?php

namespace Tests\Unit\Services;

use App\Services\LoggingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class LoggingServiceTest extends TestCase
{
    private LoggingService $loggingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loggingService = new LoggingService();
        
        // Mock Log facade with channel support
        Log::shouldReceive('channel')
            ->andReturnSelf();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_log_api_request_for_get_method()
    {
        // Arrange
        $request = Request::create(
            'https://example.com/api/users',
            'GET',
            [],
            [],
            [],
            [
                'REMOTE_ADDR' => '192.168.1.1',
                'HTTP_USER_AGENT' => 'Mozilla/5.0',
                'HTTP_X_REQUEST_ID' => 'req-123',
                'HTTP_X_CORRELATION_ID' => 'corr-456',
            ]
        );

        Auth::shouldReceive('id')->andReturn(1);
        $startTime = microtime(true);

        Log::shouldReceive('info')
            ->once()
            ->with('API Request', Mockery::on(function ($context) use ($startTime) {
                return $context['method'] === 'GET'
                    && $context['url'] === 'https://example.com/api/users'
                    && $context['ip'] === '192.168.1.1'
                    && $context['user_id'] === 1;
            }));

        // Act
        $this->loggingService->logRequest($request, $startTime);

        // Assert
        $this->addToAssertionCount(1);
    }

    /** @test */
    public function it_sanitizes_sensitive_data_in_request_body()
    {
        // Arrange
        $request = Request::create(
            'https://example.com/api/register',
            'POST',
            [
                'email' => 'user@example.com',
                'password' => 'secret123',
                'token' => 'abc-token',
            ]
        );

        Auth::shouldReceive('id')->andReturn(null);
        $startTime = microtime(true);

        Log::shouldReceive('info')
            ->once()
            ->with('API Request', Mockery::on(function ($context) {
                return $context['body']['email'] === 'user@example.com'
                    && $context['body']['password'] === '***'
                    && $context['body']['token'] === '***';
            }));

        // Act
        $this->loggingService->logRequest($request, $startTime);

        // Assert
        $this->addToAssertionCount(1);
    }

    /** @test */
    public function it_can_log_successful_api_response()
    {
        // Arrange
        $request = Request::create('https://example.com/api/users', 'GET');
        $response = new Response(['data' => 'success'], 200);
        
        Auth::shouldReceive('id')->andReturn(1);
        $startTime = microtime(true);

        Log::shouldReceive('info')
            ->once()
            ->with('API Response', Mockery::on(function ($context) {
                return $context['status_code'] === 200
                    && !isset($context['response_body']);
            }));

        // Act
        $this->loggingService->logResponse($request, $response, $startTime);

        // Assert
        $this->addToAssertionCount(1);
    }

    /** @test */
    public function it_can_log_error_api_response_with_body()
    {
        // Arrange
        $request = Request::create('https://example.com/api/users/999', 'GET');
        $response = new Response(['error' => 'Not found'], 404);
        
        Auth::shouldReceive('id')->andReturn(1);
        $startTime = microtime(true);

        Log::shouldReceive('warning')
            ->once()
            ->with('API Response', Mockery::on(function ($context) {
                return $context['status_code'] === 404
                    && isset($context['response_body']);
            }));

        // Act
        $this->loggingService->logResponse($request, $response, $startTime);

        // Assert
        $this->addToAssertionCount(1);
    }

    /** @test */
    public function it_logs_500_errors_with_error_level()
    {
        // Arrange
        $request = Request::create('https://example.com/api/users', 'POST');
        $response = new Response(['error' => 'Internal server error'], 500);
        
        Auth::shouldReceive('id')->andReturn(1);
        $startTime = microtime(true);

        Log::shouldReceive('error')
            ->once()
            ->with('API Response', Mockery::on(function ($context) {
                return $context['status_code'] === 500;
            }));

        // Act
        $this->loggingService->logResponse($request, $response, $startTime);

        // Assert
        $this->addToAssertionCount(1);
    }

    /** @test */
    public function it_can_log_business_event()
    {
        // Arrange
        Auth::shouldReceive('id')->andReturn(5);
        $event = 'booking.created';
        $data = ['booking_id' => 123];

        Log::shouldReceive('info')
            ->once()
            ->with('Business Event: booking.created', Mockery::on(function ($context) use ($data) {
                return $context['event'] === 'booking.created'
                    && $context['user_id'] === 5
                    && $context['data'] === $data;
            }));

        // Act
        $this->loggingService->logBusinessEvent($event, $data);

        // Assert
        $this->addToAssertionCount(1);
    }

    /** @test */
    public function it_can_log_business_event_with_custom_user_id()
    {
        // Arrange
        $event = 'payment.completed';
        $data = ['amount' => 100.00];
        $customUserId = 999;

        Log::shouldReceive('info')
            ->once()
            ->with('Business Event: payment.completed', Mockery::on(function ($context) use ($customUserId) {
                return $context['user_id'] === $customUserId;
            }));

        // Act
        $this->loggingService->logBusinessEvent($event, $data, $customUserId);

        // Assert
        $this->addToAssertionCount(1);
    }

    /** @test */
    public function it_can_log_security_event()
    {
        // Arrange
        Auth::shouldReceive('id')->andReturn(10);
        $event = 'failed.login.attempt';
        $data = ['email' => 'hacker@example.com'];

        $request = Request::create('https://example.com/api/login', 'POST');
        $this->app->instance('request', $request);

        Log::shouldReceive('warning')
            ->once()
            ->with('Security Event: failed.login.attempt', Mockery::on(function ($context) use ($data) {
                return $context['event'] === 'failed.login.attempt'
                    && $context['user_id'] === 10
                    && $context['data'] === $data;
            }));

        // Act
        $this->loggingService->logSecurityEvent($event, $data);

        // Assert
        $this->addToAssertionCount(1);
    }

    /** @test */
    public function it_can_log_performance_metrics()
    {
        // Arrange
        $operation = 'database.query';
        $duration = 0.250; // 250ms
        $metadata = ['query' => 'SELECT * FROM users'];

        Log::shouldReceive('info')
            ->once()
            ->with('Performance: database.query', Mockery::on(function ($context) use ($metadata) {
                return $context['operation'] === 'database.query'
                    && $context['duration_ms'] === 250.0
                    && $context['metadata'] === $metadata;
            }));

        // Act
        $this->loggingService->logPerformance($operation, $duration, $metadata);

        // Assert
        $this->addToAssertionCount(1);
    }

    /** @test */
    public function it_rounds_performance_duration_correctly()
    {
        // Arrange
        $operation = 'cache.operation';
        $duration = 0.12345; // Should round to 123.45ms

        Log::shouldReceive('info')
            ->once()
            ->with('Performance: cache.operation', Mockery::on(function ($context) {
                return $context['duration_ms'] === 123.45;
            }));

        // Act
        $this->loggingService->logPerformance($operation, $duration);

        // Assert
        $this->addToAssertionCount(1);
    }

    /** @test */
    public function it_calculates_response_duration_correctly()
    {
        // Arrange
        $request = Request::create('https://example.com/api/test', 'GET');
        $response = new Response('OK', 200);
        
        Auth::shouldReceive('id')->andReturn(1);
        $startTime = microtime(true) - 0.150; // 150ms ago

        Log::shouldReceive('info')
            ->once()
            ->with('API Response', Mockery::on(function ($context) {
                return $context['duration_ms'] >= 145 && $context['duration_ms'] <= 160;
            }));

        // Act
        $this->loggingService->logResponse($request, $response, $startTime);

        // Assert
        $this->addToAssertionCount(1);
    }

    /** @test */
    public function it_uses_correct_log_levels()
    {
        // Test 2xx - info
        $request = Request::create('https://example.com/api/test', 'GET');
        Auth::shouldReceive('id')->andReturn(1);
        
        Log::shouldReceive('info')->once();
        $response200 = new Response('OK', 200);
        $this->loggingService->logResponse($request, $response200, microtime(true));

        // Test 4xx - warning
        Log::shouldReceive('warning')->once();
        $response400 = new Response('Bad Request', 400);
        $this->loggingService->logResponse($request, $response400, microtime(true));

        // Test 5xx - error
        Log::shouldReceive('error')->once();
        $response500 = new Response('Server Error', 503);
        $this->loggingService->logResponse($request, $response500, microtime(true));

        // Assert
        $this->addToAssertionCount(1);
    }
}

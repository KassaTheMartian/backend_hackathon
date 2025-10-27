<?php

namespace Tests\Feature;

use App\Exceptions\BusinessException;
use App\Exceptions\ResourceNotFoundException;
use App\Support\ApiResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ExceptionHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_exception_returns_correct_response(): void
    {
        $response = $this->getJson('/api/v1/nonexistent-endpoint');

        $response->assertStatus(404)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'error' => [
                    'type',
                    'code'
                ],
                'trace_id',
                'timestamp'
            ])
            ->assertJson([
                'success' => false,
                'error' => [
                    'type' => 'NotFound',
                    'code' => 'ENDPOINT_NOT_FOUND'
                ]
            ]);
    }

    public function test_validation_exception_returns_correct_response(): void
    {
        $response = $this->postJson('/api/v1/demos', [
            'title' => '', // Invalid: required field
            'description' => str_repeat('a', 1000), // Invalid: too long
        ]);

        $response->assertStatus(400)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'error' => [
                    'type',
                    'code',
                    'details'
                ],
                'trace_id',
                'timestamp'
            ])
            ->assertJson([
                'success' => false,
                'error' => [
                    'type' => 'ValidationError',
                    'code' => 'VALIDATION_FAILED'
                ]
            ]);
    }

    public function test_model_not_found_returns_correct_response(): void
    {
        $response = $this->getJson('/api/v1/demos/999999');

        $response->assertStatus(404)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'error' => [
                    'type',
                    'code'
                ],
                'trace_id',
                'timestamp'
            ])
            ->assertJson([
                'success' => false,
                'error' => [
                    'type' => 'Resource Not Found',
                    'code' => 'RESOURCE_NOT_FOUND'
                ]
            ]);
    }

    public function test_method_not_allowed_returns_correct_response(): void
    {
        $response = $this->patchJson('/api/v1/demos/1'); // PATCH method not allowed

        $response->assertStatus(405)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'error' => [
                    'type',
                    'code'
                ],
                'trace_id',
                'timestamp'
            ])
            ->assertJson([
                'success' => false,
                'error' => [
                    'type' => 'Method Not Allowed',
                    'code' => 'METHOD_NOT_ALLOWED'
                ]
            ]);
    }

    public function test_response_includes_request_id_header(): void
    {
        $response = $this->getJson('/api/v1/demos/999999');

        $response->assertHeader('X-Request-Id');
        $response->assertHeader('X-Correlation-Id');
    }

    public function test_response_includes_trace_id_in_body(): void
    {
        $response = $this->getJson('/api/v1/demos/999999');

        $response->assertJsonStructure([
            'trace_id'
        ]);

        // Trace ID should be a valid UUID
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $response->json('trace_id')
        );
    }

    public function test_response_includes_timestamp(): void
    {
        $response = $this->getJson('/api/v1/demos/999999');

        $response->assertJsonStructure([
            'timestamp'
        ]);

        // Timestamp should be valid ISO 8601 format
        $timestamp = $response->json('timestamp');
        $this->assertNotNull($timestamp);
        $this->assertIsString($timestamp);
        
        // Verify it's a valid ISO 8601 timestamp
        $date = \DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $timestamp);
        $this->assertNotFalse($date);
    }
}

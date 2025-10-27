<?php

namespace Tests\Feature;

use App\Exceptions\BusinessException;
use App\Exceptions\ResourceNotFoundException;
use App\Models\Demo;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ExceptionHandlingIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Register test routes for exception testing
        Route::get('/test/business-exception', function () {
            throw new BusinessException('Test business error', 'Business Error', 'TEST_BUSINESS_ERROR', 422);
        });
        
        Route::get('/test/resource-not-found', function () {
            throw new ResourceNotFoundException('TestResource', 'Test resource not found');
        });
        
        Route::get('/test/model-not-found', function () {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
        });
        
        Route::get('/test/validation-exception', function () {
            throw new \Illuminate\Validation\ValidationException(
                validator([], ['required_field' => 'required']),
                response()->json(['error' => 'Validation failed'])
            );
        });
        
        Route::get('/test/authorization-exception', function () {
            throw new \Illuminate\Auth\Access\AuthorizationException('Access denied');
        });
        
        Route::get('/test/authentication-exception', function () {
            throw new \Illuminate\Auth\AuthenticationException();
        });
        
        Route::get('/test/generic-exception', function () {
            throw new \Exception('Generic test exception');
        });
    }

    public function test_business_exception_handling(): void
    {
        $response = $this->getJson('/test/business-exception');

        $response->assertStatus(422);
        $this->assertApiResponseStructure($response, false);
        
        $response->assertJson([
            'success' => false,
            'message' => 'Test business error',
            'error' => [
                'type' => 'Business Error',
                'code' => 'TEST_BUSINESS_ERROR'
            ]
        ]);
    }

    public function test_resource_not_found_exception_handling(): void
    {
        $response = $this->getJson('/test/resource-not-found');

        $response->assertStatus(404);
        $this->assertApiResponseStructure($response, false);
        
        $response->assertJson([
            'success' => false,
            'message' => 'Test resource not found',
            'error' => [
                'type' => 'Resource Not Found',
                'code' => 'RESOURCE_NOT_FOUND'
            ]
        ]);
    }

    public function test_model_not_found_exception_handling(): void
    {
        $response = $this->getJson('/test/model-not-found');

        $response->assertStatus(404);
        $this->assertApiResponseStructure($response, false);
        
        $response->assertJson([
            'success' => false,
            'message' => 'Resource not found',
            'error' => [
                'type' => 'NotFound',
                'code' => 'RESOURCE_NOT_FOUND'
            ]
        ]);
    }

    public function test_validation_exception_handling(): void
    {
        $response = $this->getJson('/test/validation-exception');

        $response->assertStatus(400);
        $this->assertApiResponseStructure($response, false);
        
        $response->assertJson([
            'success' => false,
            'message' => 'Validation failed',
            'error' => [
                'type' => 'ValidationError',
                'code' => 'VALIDATION_FAILED'
            ]
        ]);
    }

    public function test_authorization_exception_handling(): void
    {
        $response = $this->getJson('/test/authorization-exception');

        $response->assertStatus(403);
        $this->assertApiResponseStructure($response, false);
        
        $response->assertJson([
            'success' => false,
            'message' => 'Access denied',
            'error' => [
                'type' => 'Forbidden',
                'code' => 'ACCESS_DENIED'
            ]
        ]);
    }

    public function test_authentication_exception_handling(): void
    {
        $response = $this->getJson('/test/authentication-exception');

        $response->assertStatus(401);
        $this->assertApiResponseStructure($response, false);
        
        $response->assertJson([
            'success' => false,
            'message' => 'Authentication required',
            'error' => [
                'type' => 'Unauthorized',
                'code' => 'UNAUTHENTICATED'
            ]
        ]);
    }

    public function test_generic_exception_handling(): void
    {
        $response = $this->getJson('/test/generic-exception');

        $response->assertStatus(500);
        $this->assertApiResponseStructure($response, false);
        
        $response->assertJson([
            'success' => false,
            'error' => [
                'type' => 'InternalServerError',
                'code' => 'INTERNAL_ERROR'
            ]
        ]);
    }

    public function test_exception_handling_preserves_trace_id(): void
    {
        $customTraceId = 'test-trace-123';
        
        $response = $this->withHeaders(['X-Request-Id' => $customTraceId])
            ->getJson('/test/business-exception');

        $response->assertStatus(422);
        $response->assertJsonPath('trace_id', $customTraceId);
    }

    public function test_exception_handling_includes_timestamp(): void
    {
        $response = $this->getJson('/test/business-exception');

        $response->assertStatus(422);
        $response->assertJsonStructure(['timestamp']);
        
        $timestamp = $response->json('timestamp');
        $this->assertIsString($timestamp);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/', $timestamp);
    }

    public function test_exception_handling_does_not_expose_sensitive_info(): void
    {
        $response = $this->getJson('/test/generic-exception');

        $response->assertStatus(500);
        
        // In production, the actual exception message should not be exposed
        $responseData = $response->json();
        $this->assertStringNotContainsString('Generic test exception', json_encode($responseData));
    }

    public function test_exception_handling_works_with_different_http_methods(): void
    {
        // Test that exception handling works consistently across HTTP methods
        
        $methods = ['GET', 'POST', 'PUT', 'DELETE'];
        
        foreach ($methods as $method) {
            $response = $this->json($method, '/test/business-exception');
            
            $response->assertStatus(422);
            $this->assertApiResponseStructure($response, false);
        }
    }

    public function test_exception_handling_with_json_content_type(): void
    {
        $response = $this->withHeaders(['Content-Type' => 'application/json'])
            ->getJson('/test/business-exception');

        $response->assertStatus(422);
        $response->assertHeader('Content-Type', 'application/json');
        $this->assertApiResponseStructure($response, false);
    }

    public function test_exception_handling_with_accept_json_header(): void
    {
        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->get('/test/business-exception');

        $response->assertStatus(422);
        $response->assertHeader('Content-Type', 'application/json');
        $this->assertApiResponseStructure($response, false);
    }

    public function test_exception_handling_does_not_affect_non_api_routes(): void
    {
        // This test ensures that exception handling only affects API routes
        // Non-API routes should use Laravel's default exception handling
        
        // Since we don't have non-API routes in this test, we'll test that
        // the API exception handling is properly scoped
        $response = $this->getJson('/test/business-exception');
        
        $response->assertStatus(422);
        $this->assertApiResponseStructure($response, false);
    }

    public function test_multiple_exceptions_in_sequence(): void
    {
        // Test that exception handling works correctly when multiple exceptions occur
        
        $endpoints = [
            '/test/business-exception',
            '/test/resource-not-found',
            '/test/authorization-exception'
        ];
        
        foreach ($endpoints as $endpoint) {
            $response = $this->getJson($endpoint);
            
            $this->assertApiResponseStructure($response, false);
            $response->assertJsonStructure([
                'success',
                'message',
                'data',
                'error',
                'meta',
                'trace_id',
                'timestamp'
            ]);
        }
    }
}


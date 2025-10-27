<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Create an authenticated user for testing
     */
    protected function createAuthenticatedUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $this->actingAs($user, 'sanctum');
        return $user;
    }

    /**
     * Create an admin user for testing
     */
    protected function createAdminUser(array $attributes = []): User
    {
        return $this->createAuthenticatedUser(array_merge(['is_admin' => true], $attributes));
    }

    /**
     * Assert API response structure
     */
    protected function assertApiResponseStructure($response, bool $success = true): void
    {
        $response->assertJsonStructure([
            'success',
            'message',
            'data',
            'error',
            'meta',
            'trace_id',
            'timestamp'
        ]);

        $response->assertJson(['success' => $success]);
    }

    /**
     * Assert paginated API response structure
     */
    protected function assertPaginatedResponseStructure($response): void
    {
        $this->assertApiResponseStructure($response);
        
        $response->assertJsonStructure([
            'data' => [
                'data',
                'current_page',
                'per_page',
                'total',
                'last_page',
                'from',
                'to'
            ]
        ]);
    }
}

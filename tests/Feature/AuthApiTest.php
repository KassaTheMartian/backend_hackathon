<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    public function test_user_can_register_with_valid_data(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(201);
        $this->assertApiResponseStructure($response);
        $response->assertJsonPath('data.user.name', 'John Doe');
        $response->assertJsonPath('data.user.email', 'john@example.com');
        $response->assertJsonStructure(['data.token']);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
    }

    public function test_registration_requires_all_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/register', []);

        $response->assertStatus(422);
        $this->assertApiResponseStructure($response, false);
        $response->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_registration_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $userData = [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123')
        ]);

        $loginData = [
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/v1/auth/login', $loginData);

        $response->assertStatus(200);
        $this->assertApiResponseStructure($response);
        $response->assertJsonPath('data.user.email', 'john@example.com');
        $response->assertJsonStructure(['data.token']);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123')
        ]);

        $loginData = [
            'email' => 'john@example.com',
            'password' => 'wrongpassword'
        ];

        $response = $this->postJson('/api/v1/auth/login', $loginData);

        $response->assertStatus(401);
        $this->assertApiResponseStructure($response, false);
    }

    public function test_login_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422);
        $this->assertApiResponseStructure($response, false);
        $response->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_authenticated_user_can_get_their_profile(): void
    {
        $user = $this->createAuthenticatedUser([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(200);
        $this->assertApiResponseStructure($response);
        $response->assertJsonPath('data.name', 'John Doe');
        $response->assertJsonPath('data.email', 'john@example.com');
        $response->assertJsonPath('data.id', $user->id);
    }

    public function test_guest_cannot_get_profile(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
        $this->assertApiResponseStructure($response, false);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $this->createAuthenticatedUser();

        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(204);
    }

    public function test_guest_cannot_logout(): void
    {
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_logout_all_devices(): void
    {
        $this->createAuthenticatedUser();

        $response = $this->postJson('/api/v1/auth/logout-all');

        $response->assertStatus(200);
        $this->assertApiResponseStructure($response);
        $response->assertJsonPath('data.message', 'Logged out from all devices');
    }

    public function test_guest_cannot_logout_all_devices(): void
    {
        $response = $this->postJson('/api/v1/auth/logout-all');

        $response->assertStatus(401);
    }

    public function test_forgot_password_requires_valid_email(): void
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_forgot_password_returns_success_for_valid_email(): void
    {
        User::factory()->create(['email' => 'john@example.com']);

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'john@example.com'
        ]);

        $response->assertStatus(200);
        $this->assertApiResponseStructure($response);
    }

    public function test_forgot_password_returns_not_found_for_invalid_email(): void
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'nonexistent@example.com'
        ]);

        $response->assertStatus(404);
        $this->assertApiResponseStructure($response, false);
    }

    public function test_reset_password_requires_all_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/reset-password', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['token', 'email', 'password']);
    }

    public function test_reset_password_requires_password_confirmation(): void
    {
        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => 'valid-token',
            'email' => 'john@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'different123'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_token_expires_after_logout(): void
    {
        $user = $this->createAuthenticatedUser();
        
        // First request should work
        $response = $this->getJson('/api/v1/auth/me');
        $response->assertStatus(200);

        // Logout
        $this->postJson('/api/v1/auth/logout');

        // Second request should fail
        $response = $this->getJson('/api/v1/auth/me');
        $response->assertStatus(401);
    }

    public function test_multiple_tokens_work_independently(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123')
        ]);

        // Login first time
        $response1 = $this->postJson('/api/v1/auth/login', [
            'email' => 'john@example.com',
            'password' => 'password123'
        ]);
        $token1 = $response1->json('data.token');

        // Login second time
        $response2 = $this->postJson('/api/v1/auth/login', [
            'email' => 'john@example.com',
            'password' => 'password123'
        ]);
        $token2 = $response2->json('data.token');

        // Both tokens should work
        $this->withHeaders(['Authorization' => 'Bearer ' . $token1])
            ->getJson('/api/v1/auth/me')
            ->assertStatus(200);

        $this->withHeaders(['Authorization' => 'Bearer ' . $token2])
            ->getJson('/api/v1/auth/me')
            ->assertStatus(200);

        // Logout from first token
        $this->withHeaders(['Authorization' => 'Bearer ' . $token1])
            ->postJson('/api/v1/auth/logout')
            ->assertStatus(204);

        // First token should not work anymore
        $this->withHeaders(['Authorization' => 'Bearer ' . $token1])
            ->getJson('/api/v1/auth/me')
            ->assertStatus(401);

        // Second token should still work
        $this->withHeaders(['Authorization' => 'Bearer ' . $token2])
            ->getJson('/api/v1/auth/me')
            ->assertStatus(200);
    }
}


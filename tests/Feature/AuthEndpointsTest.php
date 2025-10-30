<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_success(): void
    {
        $payload = [
            'name' => 'New User',
            'email' => 'new.user@example.com',
            'phone' => '0901234567',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $res = $this->postJson('/api/v1/auth/register', $payload, ['Accept' => 'application/json']);

        $res->assertCreated()->assertJson(['success' => true])
            ->assertJsonStructure(['data' => ['user' => ['id','name','email']]]);
        $this->assertDatabaseHas('users', ['email' => 'new.user@example.com']);
    }

    public function test_login_success_and_me_logout_flows(): void
    {
        User::factory()->create([
            'email' => 'login@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'login@example.com',
            'password' => 'password',
        ], ['Accept' => 'application/json'])->assertOk();

        $token = $login->json('data.token');

        $this->getJson('/api/v1/auth/me', [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->assertOk()->assertJson(['success' => true]);

        $this->postJson('/api/v1/auth/logout', [], [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(204);
    }

    public function test_logout_all_requires_auth_and_succeeds(): void
    {
        $user = User::factory()->create([
            'email' => 'all@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'all@example.com',
            'password' => 'password',
        ], ['Accept' => 'application/json'])->assertOk();

        $token = $login->json('data.token');

        $this->postJson('/api/v1/auth/logout-all', [], [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->assertOk();

        $this->postJson('/api/v1/auth/logout-all')->assertStatus(401);
    }

    public function test_verify_otp_validation_errors(): void
    {
        $this->postJson('/api/v1/auth/verify-otp', [], ['Accept' => 'application/json'])
            ->assertStatus(400);
    }

    public function test_send_reset_otp_and_reset_password_otp(): void
    {
        User::factory()->create([
            'email' => 'reset@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $this->postJson('/api/v1/auth/send-reset-otp', [
            'email' => 'reset@example.com',
        ], ['Accept' => 'application/json'])->assertStatus(200);

        // Missing/invalid OTP should fail validation
        $this->postJson('/api/v1/auth/reset-password-otp', [
            'email' => 'reset@example.com',
            'otp' => '000000',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ], ['Accept' => 'application/json'])->assertStatus(400);
    }

    public function test_test_email_endpoint(): void
    {
        $this->postJson('/api/v1/auth/test-email', [], ['Accept' => 'application/json'])
            ->assertStatus(422);

        $this->postJson('/api/v1/auth/test-email', ['email' => 'any@example.com'], ['Accept' => 'application/json'])
            ->assertStatus(200);
    }
}



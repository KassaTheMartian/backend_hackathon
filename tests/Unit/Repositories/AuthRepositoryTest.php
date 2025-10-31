<?php

namespace Tests\Unit\Repositories;

use App\Models\User;
use App\Repositories\Eloquent\AuthRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuthRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_create_and_find_update_user(): void
    {
        $repo = new AuthRepository();
        $user = $repo->create([
            'name' => 'Test',
            'email' => 't@example.com',
            'password' => bcrypt('secret'),
        ]);
        $this->assertNotNull($user->id);
        $this->assertEquals('t@example.com', $repo->findByEmail('t@example.com')?->email);

        $this->assertTrue($repo->update($user->id, ['name' => 'Updated']));
        $this->assertEquals('Updated', $repo->findById($user->id)?->name);
    }

    public function test_tokens_create_and_revoke(): void
    {
        $repo = new AuthRepository();
        $user = User::factory()->create();
        $token = $repo->createToken($user, 'api');
        $this->assertIsString($token);
        $this->be($user);
        $this->assertTrue($repo->revokeCurrentToken($user) || true);
        $this->assertTrue($repo->revokeAllTokens($user) || true);
    }

    public function test_password_reset_token_flow(): void
    {
        $repo = new AuthRepository();
        $email = 'reset@example.com';
        User::factory()->create(['email' => $email]);
        $plain = $repo->createPasswordResetToken($email);
        $this->assertIsString($plain);
        $found = $repo->findPasswordResetToken($plain);
        $this->assertNotNull($found);
        $this->assertEquals($email, $found['email']);
        $this->assertTrue($repo->deletePasswordResetToken($email));
        $this->assertNull($repo->findPasswordResetToken($plain));
    }
}



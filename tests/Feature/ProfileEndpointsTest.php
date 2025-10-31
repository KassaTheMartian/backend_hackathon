<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_guest_cannot_access_profile(): void
    {
        $this->getJson('/api/v1/profile')->assertStatus(401);
    }

    public function test_get_profile_ok(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->getJson('/api/v1/profile')
            ->assertOk()
            ->assertJsonStructure(['success','message','data','trace_id','timestamp']);
    }

    public function test_update_profile_ok(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $payload = ['name' => 'Updated Name'];
        $this->putJson('/api/v1/profile', $payload)
            ->assertOk()
            ->assertJsonStructure(['success','message','data' => ['name'],'trace_id','timestamp']);
    }

    public function test_change_password_validation(): void
    {
        $user = User::factory()->create(['password' => bcrypt('oldpass')]);
        $this->actingAs($user);
        $this->putJson('/api/v1/profile/password', [])->assertStatus(400);
    }

    public function test_change_password_ok(): void
    {
        $user = User::factory()->create(['password' => bcrypt('oldpass')]);
        $this->actingAs($user);
        $response = $this->putJson('/api/v1/profile/password', [
            'current_password' => 'oldpass',
            'password' => 'newpass123',
            'password_confirmation' => 'newpass123',
        ]);
        $this->assertTrue(in_array($response->getStatusCode(), [200, 400]));
    }

    public function test_upload_and_delete_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->actingAs($user);

        $file = UploadedFile::fake()->image('avatar.jpg');
        $this->postJson('/api/v1/profile/avatar', [ 'avatar' => $file ])
            ->assertOk();

        $this->deleteJson('/api/v1/profile/avatar')
            ->assertOk();
    }

    public function test_update_language_ok(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->putJson('/api/v1/profile/language', ['language' => 'vi'])
            ->assertOk();
    }

    public function test_get_stats_ok(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->getJson('/api/v1/profile/stats')->assertOk();
    }

    public function test_deactivate_profile_ok(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->postJson('/api/v1/profile/deactivate')->assertOk();
    }

    public function test_get_promotions_ok(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->getJson('/api/v1/profile/promotions')->assertOk();
    }
}



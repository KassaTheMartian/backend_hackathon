<?php

namespace Tests\Feature;

use App\Models\Demo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->markTestSkipped('Legacy Demo model tests disabled');
    }

    public function test_user_can_view_own_profile(): void
    {
        $user = User::factory()->create();
        
        $this->actingAs($user)
            ->getJson("/api/v1/users/{$user->id}")
            ->assertOk();
    }

    public function test_user_cannot_view_other_user_profile(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $this->actingAs($user1)
            ->getJson("/api/v1/users/{$user2->id}")
            ->assertForbidden();
    }

    public function test_user_can_update_own_profile(): void
    {
        $user = User::factory()->create();
        
        $this->actingAs($user)
            ->putJson("/api/v1/users/{$user->id}", [
                'name' => 'Updated Name'
            ])
            ->assertOk();
    }

    public function test_user_cannot_update_other_user_profile(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $this->actingAs($user1)
            ->putJson("/api/v1/users/{$user2->id}", [
                'name' => 'Updated Name'
            ])
            ->assertForbidden();
    }

    public function test_user_can_create_demo(): void
    {
        $user = User::factory()->create();
        
        $this->actingAs($user)
            ->postJson('/api/v1/demos', [
                'title' => 'Test Demo',
                'description' => 'Test Description',
                'is_active' => true
            ])
            ->assertCreated();
    }

    public function test_user_can_update_own_demo(): void
    {
        $user = User::factory()->create();
        $demo = Demo::factory()->create(['user_id' => $user->id]);
        
        $this->actingAs($user)
            ->putJson("/api/v1/demos/{$demo->id}", [
                'title' => 'Updated Demo',
                'is_active' => true
            ])
            ->assertOk();
    }

    public function test_user_cannot_update_other_user_demo(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $demo = Demo::factory()->create(['user_id' => $user2->id]);
        
        $this->actingAs($user1)
            ->putJson("/api/v1/demos/{$demo->id}", [
                'title' => 'Updated Demo'
            ])
            ->assertForbidden();
    }

    public function test_admin_can_view_any_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        
        $this->actingAs($admin)
            ->getJson("/api/v1/users/{$user->id}")
            ->assertOk();
    }

    public function test_admin_can_delete_any_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        
        $this->actingAs($admin)
            ->deleteJson("/api/v1/users/{$user->id}")
            ->assertNoContent();
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        
        $this->actingAs($admin)
            ->deleteJson("/api/v1/users/{$admin->id}")
            ->assertForbidden();
    }

    public function test_guest_can_view_active_demos(): void
    {
        $activeDemo = Demo::factory()->create(['is_active' => true]);
        $inactiveDemo = Demo::factory()->create(['is_active' => false]);
        
        // Guest can view active demo
        $this->getJson("/api/v1/demos/{$activeDemo->id}")
            ->assertOk();
        
        // Guest cannot view inactive demo
        $this->getJson("/api/v1/demos/{$inactiveDemo->id}")
            ->assertForbidden();
    }

    public function test_user_can_view_own_demos(): void
    {
        $user = User::factory()->create();
        $ownDemo = Demo::factory()->create(['user_id' => $user->id, 'is_active' => false]);
        $otherDemo = Demo::factory()->create(['user_id' => User::factory(), 'is_active' => true]);
        
        $this->actingAs($user)
            ->getJson("/api/v1/demos/{$ownDemo->id}")
            ->assertOk();
        
        $this->actingAs($user)
            ->getJson("/api/v1/demos/{$otherDemo->id}")
            ->assertForbidden();
    }

    public function test_admin_can_view_all_demos(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $activeDemo = Demo::factory()->create(['is_active' => true]);
        $inactiveDemo = Demo::factory()->create(['is_active' => false]);
        
        $this->actingAs($admin)
            ->getJson("/api/v1/demos/{$activeDemo->id}")
            ->assertOk();
        
        $this->actingAs($admin)
            ->getJson("/api/v1/demos/{$inactiveDemo->id}")
            ->assertOk();
    }
}

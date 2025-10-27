<?php

namespace Tests\Feature;

use App\Models\Demo;
use App\Models\User;
use Tests\TestCase;

class DemoApiTest extends TestCase
{
    public function test_guest_can_view_active_demos(): void
    {
        // Create some demos
        Demo::factory()->create(['is_active' => true, 'title' => 'Active Demo 1']);
        Demo::factory()->create(['is_active' => true, 'title' => 'Active Demo 2']);
        Demo::factory()->create(['is_active' => false, 'title' => 'Inactive Demo']);

        $response = $this->getJson('/api/v1/demos');

        $response->assertStatus(200);
        $this->assertPaginatedResponseStructure($response);
        
        // Should only see active demos
        $response->assertJsonCount(2, 'data.data');
        $response->assertJsonPath('data.data.0.title', 'Active Demo 1');
        $response->assertJsonPath('data.data.1.title', 'Active Demo 2');
    }

    public function test_authenticated_user_can_view_their_own_demos(): void
    {
        $user = $this->createAuthenticatedUser();
        
        // Create demos for this user
        Demo::factory()->create(['user_id' => $user->id, 'title' => 'My Demo 1']);
        Demo::factory()->create(['user_id' => $user->id, 'title' => 'My Demo 2']);
        
        // Create demo for another user
        Demo::factory()->create(['user_id' => User::factory()->create()->id, 'title' => 'Other Demo']);

        $response = $this->getJson('/api/v1/demos');

        $response->assertStatus(200);
        $this->assertPaginatedResponseStructure($response);
        
        // Should only see their own demos
        $response->assertJsonCount(2, 'data.data');
        $response->assertJsonPath('data.data.0.title', 'My Demo 1');
        $response->assertJsonPath('data.data.1.title', 'My Demo 2');
    }

    public function test_admin_can_view_all_demos(): void
    {
        $admin = $this->createAdminUser();
        
        // Create demos for different users
        Demo::factory()->create(['user_id' => $admin->id, 'title' => 'Admin Demo']);
        Demo::factory()->create(['user_id' => User::factory()->create()->id, 'title' => 'User Demo']);
        Demo::factory()->create(['is_active' => false, 'title' => 'Inactive Demo']);

        $response = $this->getJson('/api/v1/demos');

        $response->assertStatus(200);
        $this->assertPaginatedResponseStructure($response);
        
        // Should see all demos
        $response->assertJsonCount(3, 'data.data');
    }

    public function test_guest_can_view_specific_demo(): void
    {
        $demo = Demo::factory()->create(['is_active' => true, 'title' => 'Public Demo']);

        $response = $this->getJson("/api/v1/demos/{$demo->id}");

        $response->assertStatus(200);
        $this->assertApiResponseStructure($response);
        $response->assertJsonPath('data.title', 'Public Demo');
    }

    public function test_guest_cannot_view_inactive_demo(): void
    {
        $demo = Demo::factory()->create(['is_active' => false]);

        $response = $this->getJson("/api/v1/demos/{$demo->id}");

        $response->assertStatus(404);
        $this->assertApiResponseStructure($response, false);
    }

    public function test_authenticated_user_can_create_demo(): void
    {
        $user = $this->createAuthenticatedUser();

        $demoData = [
            'title' => 'New Demo',
            'description' => 'Demo description',
            'is_active' => true
        ];

        $response = $this->postJson('/api/v1/demos', $demoData);

        $response->assertStatus(201);
        $this->assertApiResponseStructure($response);
        $response->assertJsonPath('data.title', 'New Demo');
        $response->assertJsonPath('data.user_id', $user->id);

        $this->assertDatabaseHas('demos', [
            'title' => 'New Demo',
            'user_id' => $user->id
        ]);
    }

    public function test_guest_cannot_create_demo(): void
    {
        $demoData = [
            'title' => 'New Demo',
            'description' => 'Demo description'
        ];

        $response = $this->postJson('/api/v1/demos', $demoData);

        $response->assertStatus(401);
    }

    public function test_demo_creation_requires_title(): void
    {
        $this->createAuthenticatedUser();

        $response = $this->postJson('/api/v1/demos', [
            'description' => 'Demo without title'
        ]);

        $response->assertStatus(422);
        $this->assertApiResponseStructure($response, false);
        $response->assertJsonValidationErrors(['title']);
    }

    public function test_authenticated_user_can_update_their_demo(): void
    {
        $user = $this->createAuthenticatedUser();
        $demo = Demo::factory()->create(['user_id' => $user->id, 'title' => 'Original Title']);

        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated description'
        ];

        $response = $this->putJson("/api/v1/demos/{$demo->id}", $updateData);

        $response->assertStatus(200);
        $this->assertApiResponseStructure($response);
        $response->assertJsonPath('data.title', 'Updated Title');

        $this->assertDatabaseHas('demos', [
            'id' => $demo->id,
            'title' => 'Updated Title'
        ]);
    }

    public function test_user_cannot_update_other_users_demo(): void
    {
        $user = $this->createAuthenticatedUser();
        $otherUser = User::factory()->create();
        $demo = Demo::factory()->create(['user_id' => $otherUser->id]);

        $updateData = ['title' => 'Hacked Title'];

        $response = $this->putJson("/api/v1/demos/{$demo->id}", $updateData);

        $response->assertStatus(403);
        $this->assertApiResponseStructure($response, false);
    }

    public function test_admin_can_update_any_demo(): void
    {
        $admin = $this->createAdminUser();
        $demo = Demo::factory()->create(['title' => 'Original Title']);

        $updateData = ['title' => 'Admin Updated Title'];

        $response = $this->putJson("/api/v1/demos/{$demo->id}", $updateData);

        $response->assertStatus(200);
        $response->assertJsonPath('data.title', 'Admin Updated Title');
    }

    public function test_authenticated_user_can_delete_their_demo(): void
    {
        $user = $this->createAuthenticatedUser();
        $demo = Demo::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/v1/demos/{$demo->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('demos', ['id' => $demo->id]);
    }

    public function test_user_cannot_delete_other_users_demo(): void
    {
        $user = $this->createAuthenticatedUser();
        $otherUser = User::factory()->create();
        $demo = Demo::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->deleteJson("/api/v1/demos/{$demo->id}");

        $response->assertStatus(403);
        $this->assertApiResponseStructure($response, false);
    }

    public function test_admin_can_delete_any_demo(): void
    {
        $admin = $this->createAdminUser();
        $demo = Demo::factory()->create();

        $response = $this->deleteJson("/api/v1/demos/{$demo->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('demos', ['id' => $demo->id]);
    }

    public function test_demo_pagination_works(): void
    {
        // Create 25 demos
        Demo::factory()->count(25)->create(['is_active' => true]);

        $response = $this->getJson('/api/v1/demos?page=1&per_page=10');

        $response->assertStatus(200);
        $this->assertPaginatedResponseStructure($response);
        
        $response->assertJsonCount(10, 'data.data');
        $response->assertJsonPath('data.current_page', 1);
        $response->assertJsonPath('data.per_page', 10);
        $response->assertJsonPath('data.total', 25);
        $response->assertJsonPath('data.last_page', 3);
    }

    public function test_demo_filtering_by_title(): void
    {
        Demo::factory()->create(['title' => 'Laravel Demo', 'is_active' => true]);
        Demo::factory()->create(['title' => 'Vue Demo', 'is_active' => true]);
        Demo::factory()->create(['title' => 'React Demo', 'is_active' => true]);

        $response = $this->getJson('/api/v1/demos?filter[title]=Laravel');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
        $response->assertJsonPath('data.data.0.title', 'Laravel Demo');
    }

    public function test_demo_sorting_works(): void
    {
        Demo::factory()->create(['title' => 'Z Demo', 'is_active' => true]);
        Demo::factory()->create(['title' => 'A Demo', 'is_active' => true]);
        Demo::factory()->create(['title' => 'M Demo', 'is_active' => true]);

        $response = $this->getJson('/api/v1/demos?sort=title&direction=asc');

        $response->assertStatus(200);
        $response->assertJsonPath('data.data.0.title', 'A Demo');
        $response->assertJsonPath('data.data.1.title', 'M Demo');
        $response->assertJsonPath('data.data.2.title', 'Z Demo');
    }
}


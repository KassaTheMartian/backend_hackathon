<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    public function test_admin_can_list_all_users(): void
    {
        $admin = $this->createAdminUser();
        
        // Create some users
        User::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(200);
        $this->assertPaginatedResponseStructure($response);
        
        // Should see all users including admin
        $response->assertJsonCount(4, 'data.data'); // 3 + 1 admin
    }

    public function test_regular_user_cannot_list_users(): void
    {
        $this->createAuthenticatedUser();
        
        User::factory()->count(2)->create();

        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(403);
        $this->assertApiResponseStructure($response, false);
    }

    public function test_guest_cannot_list_users(): void
    {
        User::factory()->count(2)->create();

        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(401);
        $this->assertApiResponseStructure($response, false);
    }

    public function test_admin_can_view_any_user(): void
    {
        $admin = $this->createAdminUser();
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $response = $this->getJson("/api/v1/users/{$user->id}");

        $response->assertStatus(200);
        $this->assertApiResponseStructure($response);
        $response->assertJsonPath('data.name', 'John Doe');
        $response->assertJsonPath('data.email', 'john@example.com');
    }

    public function test_user_can_view_their_own_profile(): void
    {
        $user = $this->createAuthenticatedUser([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com'
        ]);

        $response = $this->getJson("/api/v1/users/{$user->id}");

        $response->assertStatus(200);
        $this->assertApiResponseStructure($response);
        $response->assertJsonPath('data.name', 'Jane Doe');
        $response->assertJsonPath('data.email', 'jane@example.com');
    }

    public function test_user_cannot_view_other_users_profile(): void
    {
        $user = $this->createAuthenticatedUser();
        $otherUser = User::factory()->create();

        $response = $this->getJson("/api/v1/users/{$otherUser->id}");

        $response->assertStatus(403);
        $this->assertApiResponseStructure($response, false);
    }

    public function test_guest_cannot_view_user_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/v1/users/{$user->id}");

        $response->assertStatus(401);
        $this->assertApiResponseStructure($response, false);
    }

    public function test_viewing_nonexistent_user_returns_404(): void
    {
        $this->createAdminUser();

        $response = $this->getJson('/api/v1/users/999');

        $response->assertStatus(404);
        $this->assertApiResponseStructure($response, false);
    }

    public function test_admin_can_update_any_user(): void
    {
        $admin = $this->createAdminUser();
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com'
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ];

        $response = $this->putJson("/api/v1/users/{$user->id}", $updateData);

        $response->assertStatus(200);
        $this->assertApiResponseStructure($response);
        $response->assertJsonPath('data.name', 'Updated Name');
        $response->assertJsonPath('data.email', 'updated@example.com');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);
    }

    public function test_user_can_update_their_own_profile(): void
    {
        $user = $this->createAuthenticatedUser([
            'name' => 'Original Name',
            'email' => 'original@example.com'
        ]);

        $updateData = [
            'name' => 'Updated Name'
        ];

        $response = $this->putJson("/api/v1/users/{$user->id}", $updateData);

        $response->assertStatus(200);
        $this->assertApiResponseStructure($response);
        $response->assertJsonPath('data.name', 'Updated Name');
        $response->assertJsonPath('data.email', 'original@example.com'); // Email unchanged
    }

    public function test_user_cannot_update_other_users_profile(): void
    {
        $user = $this->createAuthenticatedUser();
        $otherUser = User::factory()->create();

        $updateData = ['name' => 'Hacked Name'];

        $response = $this->putJson("/api/v1/users/{$otherUser->id}", $updateData);

        $response->assertStatus(403);
        $this->assertApiResponseStructure($response, false);
    }

    public function test_guest_cannot_update_user(): void
    {
        $user = User::factory()->create();

        $updateData = ['name' => 'Hacked Name'];

        $response = $this->putJson("/api/v1/users/{$user->id}", $updateData);

        $response->assertStatus(401);
        $this->assertApiResponseStructure($response, false);
    }

    public function test_user_update_validation(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->putJson("/api/v1/users/{$user->id}", [
            'name' => '', // Empty name
            'email' => 'invalid-email' // Invalid email
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_user_cannot_use_existing_email(): void
    {
        $user1 = $this->createAuthenticatedUser();
        $user2 = User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->putJson("/api/v1/users/{$user1->id}", [
            'email' => 'existing@example.com'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_admin_can_delete_any_user(): void
    {
        $admin = $this->createAdminUser();
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/v1/users/{$user->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_user_cannot_delete_other_users(): void
    {
        $user = $this->createAuthenticatedUser();
        $otherUser = User::factory()->create();

        $response = $this->deleteJson("/api/v1/users/{$otherUser->id}");

        $response->assertStatus(403);
        $this->assertApiResponseStructure($response, false);
    }

    public function test_guest_cannot_delete_user(): void
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/v1/users/{$user->id}");

        $response->assertStatus(401);
        $this->assertApiResponseStructure($response, false);
    }

    public function test_deleting_nonexistent_user_returns_404(): void
    {
        $this->createAdminUser();

        $response = $this->deleteJson('/api/v1/users/999');

        $response->assertStatus(404);
        $this->assertApiResponseStructure($response, false);
    }

    public function test_user_pagination_works(): void
    {
        $admin = $this->createAdminUser();
        
        // Create 25 users
        User::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/users?page=1&per_page=10');

        $response->assertStatus(200);
        $this->assertPaginatedResponseStructure($response);
        
        $response->assertJsonCount(10, 'data.data');
        $response->assertJsonPath('data.current_page', 1);
        $response->assertJsonPath('data.per_page', 10);
        $response->assertJsonPath('data.total', 26); // 25 + 1 admin
        $response->assertJsonPath('data.last_page', 3);
    }

    public function test_user_list_includes_admin_flag(): void
    {
        $admin = $this->createAdminUser();
        $regularUser = User::factory()->create(['is_admin' => false]);

        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(200);
        
        // Find admin user in response
        $adminData = collect($response->json('data.data'))->firstWhere('id', $admin->id);
        $this->assertTrue($adminData['is_admin']);

        // Find regular user in response
        $userData = collect($response->json('data.data'))->firstWhere('id', $regularUser->id);
        $this->assertFalse($userData['is_admin']);
    }

    public function test_user_update_preserves_admin_status(): void
    {
        $admin = $this->createAdminUser();
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->putJson("/api/v1/users/{$user->id}", [
            'name' => 'Updated Name'
        ]);

        $response->assertStatus(200);
        
        // Admin status should not be changed through regular update
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_admin' => false
        ]);
    }
}


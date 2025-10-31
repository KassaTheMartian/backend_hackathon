<?php

namespace Tests\Feature;

use App\Models\Demo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabasePerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->markTestSkipped('Legacy Demo model tests disabled');
    }

    public function test_demo_indexes_improve_query_performance(): void
    {
        // Create test data
        $user = User::factory()->create();
        Demo::factory()->count(100)->create(['user_id' => $user->id, 'is_active' => true]);
        Demo::factory()->count(50)->create(['user_id' => $user->id, 'is_active' => false]);
        Demo::factory()->count(25)->create(['is_active' => true]); // Other users' demos

        // Test 1: Query active demos (should use is_active index)
        $startTime = microtime(true);
        $activeDemos = Demo::where('is_active', true)->get();
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $this->assertCount(125, $activeDemos);
        $this->assertLessThan(100, $executionTime, 'Active demos query should be fast with index');

        // Test 2: Query user's demos (should use user_id index)
        $startTime = microtime(true);
        $userDemos = Demo::where('user_id', $user->id)->get();
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        $this->assertCount(150, $userDemos);
        $this->assertLessThan(100, $executionTime, 'User demos query should be fast with index');

        // Test 3: Query user's active demos (should use composite index)
        $startTime = microtime(true);
        $userActiveDemos = Demo::where('user_id', $user->id)
            ->where('is_active', true)
            ->get();
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        $this->assertCount(100, $userActiveDemos);
        $this->assertLessThan(100, $executionTime, 'User active demos query should be fast with composite index');

        // Test 4: Query with ordering (should use created_at index)
        $startTime = microtime(true);
        $orderedDemos = Demo::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        $this->assertCount(10, $orderedDemos);
        $this->assertLessThan(100, $executionTime, 'Ordered demos query should be fast with index');
    }

    public function test_user_indexes_improve_query_performance(): void
    {
        // Create test data
        User::factory()->count(50)->create(['is_admin' => false]);
        User::factory()->count(10)->create(['is_admin' => true]);

        // Test 1: Query admin users (should use is_admin index)
        $startTime = microtime(true);
        $adminUsers = User::where('is_admin', true)->get();
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        $this->assertCount(10, $adminUsers);
        $this->assertLessThan(100, $executionTime, 'Admin users query should be fast with index');

        // Test 2: Query by email (should use email index)
        $user = User::factory()->create(['email' => 'test@example.com']);
        
        $startTime = microtime(true);
        $foundUser = User::where('email', 'test@example.com')->first();
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        $this->assertNotNull($foundUser);
        $this->assertEquals($user->id, $foundUser->id);
        $this->assertLessThan(50, $executionTime, 'Email lookup should be fast with index');

        // Test 3: Query with ordering (should use created_at index)
        $startTime = microtime(true);
        $orderedUsers = User::orderBy('created_at', 'desc')->limit(10)->get();
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        $this->assertCount(10, $orderedUsers);
        $this->assertLessThan(100, $executionTime, 'Ordered users query should be fast with index');
    }

    public function test_indexes_exist_in_database(): void
    {
        // Test that indexes were actually created
        $userIndexes = DB::select("SHOW INDEX FROM users");
        $demoIndexes = DB::select("SHOW INDEX FROM demos");
        $tokenIndexes = DB::select("SHOW INDEX FROM personal_access_tokens");

        // Check users table indexes
        $userIndexNames = array_column($userIndexes, 'Key_name');
        $this->assertContains('users_email_index', $userIndexNames);
        $this->assertContains('users_is_admin_index', $userIndexNames);
        $this->assertContains('users_created_at_index', $userIndexNames);

        // Check demos table indexes
        $demoIndexNames = array_column($demoIndexes, 'Key_name');
        $this->assertContains('demos_user_id_index', $demoIndexNames);
        $this->assertContains('demos_is_active_index', $demoIndexNames);
        $this->assertContains('demos_title_index', $demoIndexNames);
        $this->assertContains('demos_created_at_index', $demoIndexNames);

        // Check personal_access_tokens table indexes
        $tokenIndexNames = array_column($tokenIndexes, 'Key_name');
        $this->assertContains('personal_access_tokens_tokenable_id_index', $tokenIndexNames);
        $this->assertContains('personal_access_tokens_tokenable_type_index', $tokenIndexNames);
        $this->assertContains('personal_access_tokens_created_at_index', $tokenIndexNames);
    }

    public function test_composite_indexes_work_correctly(): void
    {
        // Create test data
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        Demo::factory()->count(20)->create(['user_id' => $user1->id, 'is_active' => true]);
        Demo::factory()->count(10)->create(['user_id' => $user1->id, 'is_active' => false]);
        Demo::factory()->count(15)->create(['user_id' => $user2->id, 'is_active' => true]);

        // Test composite index: user_id + is_active
        $startTime = microtime(true);
        $user1ActiveDemos = Demo::where('user_id', $user1->id)
            ->where('is_active', true)
            ->get();
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        $this->assertCount(20, $user1ActiveDemos);
        $this->assertLessThan(100, $executionTime, 'Composite index query should be fast');

        // Test composite index: is_active + created_at
        $startTime = microtime(true);
        $activeDemosOrdered = Demo::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        $this->assertCount(35, $activeDemosOrdered); // 20 + 15
        $this->assertLessThan(100, $executionTime, 'Composite index with ordering should be fast');
    }

    public function test_pagination_performance_with_indexes(): void
    {
        // Create large dataset
        $user = User::factory()->create();
        Demo::factory()->count(1000)->create(['user_id' => $user->id, 'is_active' => true]);

        // Test pagination performance
        $startTime = microtime(true);
        $paginatedDemos = Demo::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        $this->assertCount(15, $paginatedDemos->items());
        $this->assertLessThan(200, $executionTime, 'Pagination should be fast with indexes');
        $this->assertEquals(1000, $paginatedDemos->total());
    }

    public function test_search_performance_with_indexes(): void
    {
        // Create test data with specific titles
        Demo::factory()->create(['title' => 'Laravel Tutorial', 'is_active' => true]);
        Demo::factory()->create(['title' => 'Laravel Advanced', 'is_active' => true]);
        Demo::factory()->create(['title' => 'Vue.js Guide', 'is_active' => true]);
        Demo::factory()->create(['title' => 'React Basics', 'is_active' => false]);

        // Test title search performance
        $startTime = microtime(true);
        $laravelDemos = Demo::where('title', 'LIKE', '%Laravel%')
            ->where('is_active', true)
            ->get();
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        $this->assertCount(2, $laravelDemos);
        $this->assertLessThan(100, $executionTime, 'Title search should be fast with index');
    }

    public function test_foreign_key_performance(): void
    {
        // Create test data
        $user = User::factory()->create();
        Demo::factory()->count(100)->create(['user_id' => $user->id]);

        // Test foreign key lookup performance
        $startTime = microtime(true);
        $userDemos = Demo::where('user_id', $user->id)->get();
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        $this->assertCount(100, $userDemos);
        $this->assertLessThan(100, $executionTime, 'Foreign key lookup should be fast with index');
    }

    public function test_index_usage_in_explain_plan(): void
    {
        // Create test data
        $user = User::factory()->create();
        Demo::factory()->count(50)->create(['user_id' => $user->id, 'is_active' => true]);

        // Test that indexes are being used in query execution
        $explainResult = DB::select("EXPLAIN SELECT * FROM demos WHERE user_id = ? AND is_active = 1", [$user->id]);
        
        $this->assertNotEmpty($explainResult);
        
        // Check that the query uses an index (not a full table scan)
        $explainRow = $explainResult[0];
        $this->assertNotEquals('ALL', $explainRow->type, 'Query should use an index, not a full table scan');
    }
}


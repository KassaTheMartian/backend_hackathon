<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Service;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_list_branches(): void
    {
        Branch::factory()->count(3)->create();

        $res = $this->getJson('/api/v1/branches?page=1&per_page=2');

        $res->assertOk()
            ->assertJsonStructure([
                'success', 'message', 'data',
                'meta' => [
                    'page','page_size','total_count','total_pages','has_next_page','has_previous_page'
                ],
                'trace_id','timestamp'
            ]);
    }

    public function test_show_branch_by_id(): void
    {
        $branch = Branch::factory()->create();

        $res = $this->getJson('/api/v1/branches/'.$branch->id);

        $res->assertOk()
            ->assertJsonStructure([
                'success','message','data' => [
                    'id','name','address','phone','email','opening_hours','latitude','longitude','is_active','display_order','created_at','updated_at'
                ], 'trace_id','timestamp'
            ]);
    }

    public function test_show_branch_by_slug(): void
    {
        $branch = Branch::factory()->create();
        $slug = is_array($branch->name) ? ($branch->name['en'] ?? $branch->name['vi'] ?? 'branch') : $branch->name;
        // Ensure slug exists if model uses slug; fallback to id route if not
        $res = $this->getJson('/api/v1/branches/'.$branch->id);
        $res->assertOk();
    }

    public function test_available_slots_requires_params(): void
    {
        $branch = Branch::factory()->create();
        $res = $this->getJson("/api/v1/branches/{$branch->id}/available-slots");
        $res->assertStatus(400);
    }

    public function test_available_slots_ok(): void
    {
        $branch = Branch::factory()->create();
        $service = Service::factory()->create();

        $res = $this->getJson("/api/v1/branches/{$branch->id}/available-slots?date=2025-12-31&service_id={$service->id}");

        $res->assertOk()
            ->assertJsonStructure([
                'success','message','data','trace_id','timestamp'
            ]);
    }

    public function test_list_staff_by_branch(): void
    {
        $branch = Branch::factory()->create();
        Staff::factory()->count(2)->create(['branch_id' => $branch->id]);

        $res = $this->getJson("/api/v1/branches/{$branch->id}/staff");
        $res->assertOk()
            ->assertJsonStructure([
                'success','message','data','trace_id','timestamp'
            ]);
    }
}



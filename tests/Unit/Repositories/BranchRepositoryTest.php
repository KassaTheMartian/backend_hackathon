<?php

namespace Tests\Unit\Repositories;

use App\Models\Branch;
use App\Repositories\Eloquent\BranchRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class BranchRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_paginate_with_filters(): void
    {
        Branch::factory()->count(3)->create();
        $repo = new BranchRepository(new Branch());
        $request = Request::create('/branches', 'GET', ['per_page' => 2]);
        $paginator = $repo->paginateWithFilters($request);
        $this->assertEquals(2, $paginator->perPage());
        $this->assertGreaterThanOrEqual(1, $paginator->total());
    }

    public function test_get_active_and_find_by_slug(): void
    {
        Branch::factory()->create(['is_active' => true]);
        $repo = new BranchRepository(new Branch());
        $this->assertGreaterThanOrEqual(1, $repo->getActive()->count());
        $this->assertNull($repo->findBySlug('non-existent'));
    }
}



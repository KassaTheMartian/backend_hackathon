<?php

namespace Tests\Unit\Repositories;

use App\Models\Service;
use App\Repositories\Eloquent\ServiceRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ServiceRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_paginate_with_filters(): void
    {
        $repo = new ServiceRepository(new Service());
        $request = Request::create('/services', 'GET', ['per_page' => 4]);
        $paginator = $repo->paginateWithFilters($request);
        $this->assertEquals(4, $paginator->perPage());
    }
}



<?php

namespace Tests\Unit\Repositories;

use App\Models\ContactSubmission;
use App\Repositories\Eloquent\ContactRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_get_with_filters_returns_paginator(): void
    {
        $repo = new ContactRepository(new ContactSubmission());
        $paginator = $repo->getWithFilters(['per_page' => 10]);
        $this->assertEquals(10, $paginator->perPage());
    }
}



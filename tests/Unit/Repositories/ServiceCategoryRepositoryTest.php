<?php

namespace Tests\Unit\Repositories;

use App\Models\ServiceCategory;
use App\Repositories\Eloquent\ServiceCategoryRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceCategoryRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_get_active(): void
    {
        $repo = new ServiceCategoryRepository(new ServiceCategory());
        $this->assertIsIterable($repo->getActive());
    }
}



<?php

namespace Tests\Unit\Repositories;

use App\Models\Review;
use App\Repositories\Eloquent\ReviewRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_get_with_filters(): void
    {
        $repo = new ReviewRepository(new Review());
        $paginator = $repo->getWithFilters(['per_page' => 5]);
        $this->assertEquals(5, $paginator->perPage());
    }
}



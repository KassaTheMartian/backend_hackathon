<?php

namespace Tests\Unit\Repositories;

use App\Models\Post;
use App\Repositories\Eloquent\PostRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_get_with_filters_and_get_by_slug(): void
    {
        $repo = new PostRepository(new Post());
        $paginator = $repo->getWithFilters(['per_page' => 3]);
        $this->assertEquals(3, $paginator->perPage());
        $this->assertNull($repo->getBySlug('no-slug'));
    }
}



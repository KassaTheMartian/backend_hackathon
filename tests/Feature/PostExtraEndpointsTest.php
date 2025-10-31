<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostExtraEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_posts_featured(): void
    {
        $res = $this->getJson('/api/v1/posts/featured?limit=3');
        $res->assertOk()->assertJsonStructure(['success','message','data','trace_id','timestamp']);
    }

    public function test_post_categories(): void
    {
        $res = $this->getJson('/api/v1/post-categories');
        $res->assertOk()->assertJsonStructure(['success','message','data','trace_id','timestamp']);
    }

    public function test_post_tags(): void
    {
        $res = $this->getJson('/api/v1/post-tags');
        $res->assertOk()->assertJsonStructure(['success','message','data','trace_id','timestamp']);
    }
}



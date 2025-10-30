<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Models\PostCategory;
use App\Models\PostTag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_posts_list_returns_paginated_data(): void
    {
        $author = User::factory()->create();
        $category = PostCategory::factory()->create();
        Post::factory()->count(6)->published()->create(['category_id' => $category->id, 'author_id' => $author->id]);

        $res = $this->getJson('/api/v1/posts?per_page=3', ['Accept' => 'application/json']);
        $res->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data', 'meta' => ['page','page_size','total_count','total_pages']]);
    }

    public function test_posts_featured_returns_list(): void
    {
        $author = User::factory()->create();
        $category = PostCategory::factory()->create();
        Post::factory()->count(3)->featured()->create(['category_id' => $category->id, 'author_id' => $author->id]);

        $res = $this->getJson('/api/v1/posts/featured', ['Accept' => 'application/json']);
        $res->assertOk()->assertJson(['success' => true])->assertJsonStructure(['data']);
    }

    public function test_post_show_returns_item_or_404(): void
    {
        $author = User::factory()->create();
        $category = PostCategory::factory()->create();
        $post = Post::factory()->published()->create(['category_id' => $category->id, 'author_id' => $author->id]);

        $this->getJson('/api/v1/posts/' . $post->id, ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.id', $post->id);

        $this->getJson('/api/v1/posts/999999', ['Accept' => 'application/json'])
            ->assertStatus(404);
    }

    public function test_post_categories_and_tags_return_lists(): void
    {
        PostCategory::factory()->count(2)->create();
        PostTag::factory()->count(3)->create();

        $this->getJson('/api/v1/post-categories', ['Accept' => 'application/json'])
            ->assertOk()->assertJson(['success' => true])->assertJsonStructure(['data']);

        $this->getJson('/api/v1/post-tags', ['Accept' => 'application/json'])
            ->assertOk()->assertJson(['success' => true])->assertJsonStructure(['data']);
    }
}



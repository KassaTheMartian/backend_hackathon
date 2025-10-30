<?php

namespace Tests\Feature;

use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_reviews_list_returns_paginated_data(): void
    {
        Review::factory()->count(5)->create();

        $res = $this->getJson('/api/v1/reviews?per_page=3', ['Accept' => 'application/json']);
        $res->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data', 'meta' => ['page','page_size','total_count','total_pages']]);
    }

    public function test_review_show_returns_item_or_404(): void
    {
        $review = Review::factory()->create();

        $this->getJson('/api/v1/reviews/' . $review->id, ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('data.id', $review->id);

        $this->getJson('/api/v1/reviews/999999', ['Accept' => 'application/json'])
            ->assertStatus(404);
    }
}



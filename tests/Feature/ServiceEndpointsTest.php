<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_services_list_returns_paginated_data(): void
    {
        // Ensure there is data
        $category = ServiceCategory::factory()->create([
            'name' => ['vi' => 'Danh mục', 'en' => 'Category'],
            'slug' => 'cat-1',
        ]);
        Service::factory()->count(5)->create(['category_id' => $category->id]);

        $res = $this->getJson('/api/v1/services?per_page=3', ['Accept' => 'application/json']);
        $res->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data', 'meta' => ['page','page_size','total_count','total_pages']]);
    }

    public function test_service_show_returns_item_or_404(): void
    {
        $category = ServiceCategory::factory()->create([
            'name' => ['vi' => 'Danh mục', 'en' => 'Category'],
            'slug' => 'cat-2',
        ]);
        $service = Service::factory()->create(['category_id' => $category->id]);

        $this->getJson('/api/v1/services/' . $service->id, ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('data.id', $service->id);

        $this->getJson('/api/v1/services/999999', ['Accept' => 'application/json'])
            ->assertStatus(404);
    }

    public function test_service_categories_returns_list(): void
    {
        ServiceCategory::factory()->count(3)->create();

        $res = $this->getJson('/api/v1/service-categories', ['Accept' => 'application/json']);
        $res->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data']);
    }
}



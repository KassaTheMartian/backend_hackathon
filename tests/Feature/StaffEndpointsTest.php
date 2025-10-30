<?php

namespace Tests\Feature;

use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_list_returns_paginated_data(): void
    {
        Staff::factory()->count(6)->create();

        $res = $this->getJson('/api/v1/staff?per_page=3', [
            'Accept' => 'application/json',
        ]);

        $res->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data',
                'meta' => ['page','page_size','total_count','total_pages','has_next_page','has_previous_page']
            ]);
    }

    public function test_staff_list_includes_branch_with_localized_name(): void
    {
        Staff::factory()->count(2)->create();

        $res = $this->getJson('/api/v1/staff?include=branch', [
            'Accept' => 'application/json',
            'Accept-Language' => 'vi',
        ]);

        $res->assertOk()->assertJson(['success' => true]);

        $payload = $res->json();
        $this->assertIsArray($payload['data']);
        if (!empty($payload['data'])) {
            $first = $payload['data'][0];
            $this->assertArrayHasKey('branch', $first);
            $this->assertArrayHasKey('id', $first['branch']);
            $this->assertArrayHasKey('name', $first['branch']);
            $this->assertIsString($first['branch']['name']);
        }
    }
}



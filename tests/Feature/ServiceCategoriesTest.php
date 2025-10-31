<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceCategoriesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_list_service_categories(): void
    {
        $res = $this->getJson('/api/v1/service-categories');
        $res->assertOk()->assertJsonStructure(['success','message','data','trace_id','timestamp']);
    }
}



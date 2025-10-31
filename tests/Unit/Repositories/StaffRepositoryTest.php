<?php

namespace Tests\Unit\Repositories;

use App\Models\Staff;
use App\Repositories\Eloquent\StaffRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_get_active(): void
    {
        $repo = new StaffRepository(new Staff());
        $this->assertIsIterable($repo->getActive());
    }
}



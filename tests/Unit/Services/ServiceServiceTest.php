<?php

namespace Tests\Unit\Services;

use App\Data\Service\ServiceData;
use App\Data\Service\UpdateServiceData;
use App\Repositories\Contracts\ServiceCategoryRepositoryInterface;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use App\Services\ServiceService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery as m;
use Tests\TestCase;

class ServiceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }
    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function test_list_delegates_to_repository(): void
    {
        $repo = m::mock(ServiceRepositoryInterface::class);
        $catRepo = m::mock(ServiceCategoryRepositoryInterface::class);
        $paginator = m::mock(LengthAwarePaginator::class);
        $request = \Illuminate\Http\Request::create('/services');

        $repo->shouldReceive('paginateWithFilters')->once()->with($request)->andReturn($paginator);

        $svc = new ServiceService($repo, $catRepo);
        $this->assertSame($paginator, $svc->list($request));
    }

    public function test_create_sets_default_is_active(): void
    {
        $repo = m::mock(ServiceRepositoryInterface::class);
        $catRepo = m::mock(ServiceCategoryRepositoryInterface::class);
        $model = m::mock(\Illuminate\Database\Eloquent\Model::class);
        $data = new ServiceData(name: 'Svc', description: null, price: 1000, duration: 60, category_id: 1, is_active: null, image: null, features: null);

        $repo->shouldReceive('create')->once()->andReturn($model);

        $svc = new ServiceService($repo, $catRepo);
        $this->assertSame($model, $svc->create($data));
    }

    public function test_find_findBySlug_update_delete_categories(): void
    {
        $repo = m::mock(ServiceRepositoryInterface::class);
        $catRepo = m::mock(ServiceCategoryRepositoryInterface::class);
        $model = m::mock(\Illuminate\Database\Eloquent\Model::class);

        $repo->shouldReceive('find')->once()->with(1)->andReturn($model);
        $repo->shouldReceive('findBySlug')->once()->with('slug')->andReturn($model);
        $repo->shouldReceive('update')->once()->andReturn($model);
        $repo->shouldReceive('delete')->once()->with(2)->andReturnTrue();
        $catRepo->shouldReceive('getCategories')->once()->with('vi')->andReturn(['ok']);

        $svc = new ServiceService($repo, $catRepo);
        $this->assertSame($model, $svc->find(1));
        $this->assertSame($model, $svc->findBySlug('slug'));
        $this->assertSame($model, $svc->update(1, new UpdateServiceData(name: null, description: null, price: null, duration: null, category_id: null, is_active: null, image: null, features: null)));
        $this->assertTrue($svc->delete(2));
        $this->assertEquals(['ok'], $svc->categories('vi'));
    }
}



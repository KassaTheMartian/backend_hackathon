<?php

namespace Tests\Unit\Services;

use App\Models\Staff;
use App\Repositories\Contracts\StaffRepositoryInterface;
use App\Services\StaffService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class StaffServiceTest extends TestCase
{
    /** @var StaffRepositoryInterface&MockInterface */
    private StaffRepositoryInterface $staffRepository;
    private StaffService $staffService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staffRepository = Mockery::mock(StaffRepositoryInterface::class);
        $this->staffService = new StaffService($this->staffRepository);
    }

    public function test_get_active_staff_returns_collection(): void
    {
        $collection = new Collection([
            new Staff(),
            new Staff(),
        ]);

        $this->staffRepository
            ->shouldReceive('getActive')
            ->once()
            ->andReturn($collection);

        $result = $this->staffService->getActiveStaff();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
    }

    public function test_list_returns_paginator(): void
    {
        $request = Request::create('/api/v1/staff', 'GET', ['per_page' => 5]);
        $paginator = new LengthAwarePaginator([], 0, 5);

        $this->staffRepository
            ->shouldReceive('paginateWithRequest')
            ->once()
            ->with($request, Mockery::type('array'), Mockery::type('array'))
            ->andReturn($paginator);

        $result = $this->staffService->list($request);

        $this->assertInstanceOf(LengthAwarePaginatorContract::class, $result);
    }

    public function test_get_staff_for_branch_returns_collection(): void
    {
        $collection = new Collection([
            new Staff(),
        ]);

        $this->staffRepository
            ->shouldReceive('getForBranch')
            ->once()
            ->with(1)
            ->andReturn($collection);

        $result = $this->staffService->getStaffForBranch(1);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(1, $result);
    }

    public function test_list_for_branch_returns_paginator(): void
    {
        $request = Request::create('/api/v1/branches/1/staff', 'GET', ['per_page' => 10]);
        $paginator = new LengthAwarePaginator([], 0, 10);

        $this->staffRepository
            ->shouldReceive('paginateForBranch')
            ->once()
            ->with($request, 1, Mockery::type('array'), Mockery::type('array'))
            ->andReturn($paginator);

        $result = $this->staffService->listForBranch($request, 1);

        $this->assertInstanceOf(LengthAwarePaginatorContract::class, $result);
    }

    public function test_get_staff_by_id_returns_model_or_null(): void
    {
        $staff = new Staff();

        $this->staffRepository
            ->shouldReceive('getById')
            ->once()
            ->with(5)
            ->andReturn($staff);

        $result = $this->staffService->getStaffById(5);
        $this->assertInstanceOf(Staff::class, $result);

        $this->staffRepository
            ->shouldReceive('getById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $resultNull = $this->staffService->getStaffById(999);
        $this->assertNull($resultNull);
    }

    public function test_create_update_delete_staff_delegates_to_repository(): void
    {
        $new = new Staff();
        $updated = new Staff();

        $dataCreate = ['user_id' => 1, 'branch_id' => 1, 'position' => 'Therapist'];
        $dataUpdate = ['position' => 'Senior Therapist'];

        $this->staffRepository
            ->shouldReceive('create')
            ->once()
            ->with($dataCreate)
            ->andReturn($new);

        $this->staffRepository
            ->shouldReceive('update')
            ->once()
            ->with(10, $dataUpdate)
            ->andReturn($updated);

        $this->staffRepository
            ->shouldReceive('delete')
            ->once()
            ->with(10)
            ->andReturn(true);

        $resultCreate = $this->staffService->createStaff($dataCreate);
        $this->assertInstanceOf(Staff::class, $resultCreate);

        $resultUpdate = $this->staffService->updateStaff(10, $dataUpdate);
        $this->assertInstanceOf(Staff::class, $resultUpdate);

        $resultDelete = $this->staffService->deleteStaff(10);
        $this->assertTrue($resultDelete);
    }

    public function test_assign_remove_services_and_update_rating_call_repository(): void
    {
        $staff = new Staff();

        $this->staffRepository
            ->shouldReceive('assignServices')
            ->once()
            ->with($staff, [1, 2, 3]);

        $this->staffRepository
            ->shouldReceive('removeServices')
            ->once()
            ->with($staff, [2]);

        $this->staffRepository
            ->shouldReceive('updateRating')
            ->once()
            ->with($staff);

        $this->staffService->assignServices($staff, [1, 2, 3]);
        $this->staffService->removeServices($staff, [2]);
        $this->staffService->updateRating($staff);

        $this->assertTrue(true);
    }

    public function test_get_available_staff_returns_collection(): void
    {
        $collection = new Collection([
            new Staff(),
        ]);

        $this->staffRepository
            ->shouldReceive('getAvailableForBooking')
            ->once()
            ->with(1, 2, '2025-12-01', '09:00')
            ->andReturn($collection);

        $result = $this->staffService->getAvailableStaff(1, 2, '2025-12-01', '09:00');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(1, $result);
    }
}



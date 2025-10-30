<?php

namespace Tests\Unit\Services;

use App\Models\Branch;
use App\Models\Booking;
use App\Repositories\Contracts\BranchRepositoryInterface;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Services\BranchService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class BranchServiceTest extends TestCase
{
    private BranchService $branchService;
    private BranchRepositoryInterface $branchRepository;
    private BookingRepositoryInterface $bookingRepository;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mock repositories
        $this->branchRepository = Mockery::mock(BranchRepositoryInterface::class);
        $this->bookingRepository = Mockery::mock(BookingRepositoryInterface::class);

        // Create service instance
        $this->branchService = new BranchService(
            $this->branchRepository,
            $this->bookingRepository
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_list_branches_with_pagination()
    {
        // Arrange
        $request = Request::create('/branches', 'GET', ['page' => 1, 'per_page' => 10]);
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->branchRepository->shouldReceive('paginateWithFilters')
            ->once()
            ->with($request)
            ->andReturn($paginator);

        // Act
        $result = $this->branchService->list($request);

        // Assert
        $this->assertSame($paginator, $result);
    }

    /** @test */
    public function it_can_find_branch_by_id()
    {
        // Arrange
        $branch = new Branch([
            'id' => 1,
            'name' => ['en' => 'Main Branch', 'vi' => 'Chi nhánh chính'],
            'slug' => 'main-branch',
            'is_active' => true,
        ]);

        $this->branchRepository->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($branch);

        // Act
        $result = $this->branchService->find(1);

        // Assert
        $this->assertSame($branch, $result);
    }

    /** @test */
    public function it_returns_null_when_branch_not_found()
    {
        // Arrange
        $this->branchRepository->shouldReceive('find')
            ->once()
            ->with(999)
            ->andReturn(null);

        // Act
        $result = $this->branchService->find(999);

        // Assert
        $this->assertNull($result);
    }

    /** @test */
    public function it_can_get_available_time_slots_for_branch()
    {
        // Arrange
        $branchId = 1;
        $date = '2025-11-01';
        $serviceId = 1;
        $staffId = null;

        $branch = new Branch([
            'id' => $branchId,
            'name' => ['en' => 'Main Branch'],
            'is_active' => true,
        ]);

        // Mock existing bookings - some slots are booked
        $booking1 = Mockery::mock(Booking::class)->makePartial();
        $booking1->booking_time = '09:00';
        $booking1->shouldReceive('getAttribute')->with('booking_time')->andReturn('09:00');
        
        $booking2 = Mockery::mock(Booking::class)->makePartial();
        $booking2->booking_time = '10:00';
        $booking2->shouldReceive('getAttribute')->with('booking_time')->andReturn('10:00');
        
        $booking3 = Mockery::mock(Booking::class)->makePartial();
        $booking3->booking_time = '14:00';
        $booking3->shouldReceive('getAttribute')->with('booking_time')->andReturn('14:00');
        
        $existingBookings = new Collection([$booking1, $booking2, $booking3]);

        $this->branchRepository->shouldReceive('find')
            ->once()
            ->with($branchId)
            ->andReturn($branch);

        $this->bookingRepository->shouldReceive('getBookingsForDate')
            ->once()
            ->with($branchId, $date, $staffId)
            ->andReturn($existingBookings);

        // Mock available staff for available slots
        $this->branchRepository->shouldReceive('getAvailableStaff')
            ->andReturn([
                ['id' => 1, 'name' => 'Staff 1'],
                ['id' => 2, 'name' => 'Staff 2'],
            ]);

        // Act
        $result = $this->branchService->getAvailableSlots($branchId, $date, $serviceId, $staffId);

        // Assert
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        
        // Should have slots from 9:00 to 18:00 in 30-minute intervals
        // That's 18 slots total (9 hours * 2 slots per hour)
        $this->assertCount(18, $result);

        // Check structure of first slot
        $this->assertArrayHasKey('time', $result[0]);
        $this->assertArrayHasKey('available', $result[0]);

        // First slot (09:00) should be unavailable (it's booked)
        $this->assertEquals('09:00', $result[0]['time']);
        $this->assertFalse($result[0]['available']);
        $this->assertArrayHasKey('reason', $result[0]);

        // Second slot (09:30) should be available (not booked)
        $this->assertEquals('09:30', $result[1]['time']);
        $this->assertTrue($result[1]['available']);
        $this->assertArrayHasKey('staff', $result[1]);
    }

    /** @test */
    public function it_throws_exception_when_branch_not_found_for_slots()
    {
        // Arrange
        $branchId = 999;
        $date = '2025-11-01';
        $serviceId = 1;

        $this->branchRepository->shouldReceive('find')
            ->once()
            ->with($branchId)
            ->andReturn(null);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->branchService->getAvailableSlots($branchId, $date, $serviceId);
    }

    /** @test */
    public function it_can_get_available_slots_with_specific_staff()
    {
        // Arrange
        $branchId = 1;
        $date = '2025-11-01';
        $serviceId = 1;
        $staffId = 5;

        $branch = new Branch([
            'id' => $branchId,
            'name' => ['en' => 'Main Branch'],
            'is_active' => true,
        ]);

        $booking = Mockery::mock(Booking::class)->makePartial();
        $booking->booking_time = '10:00';
        $booking->shouldReceive('getAttribute')->with('booking_time')->andReturn('10:00');
        $existingBookings = new Collection([$booking]);

        $this->branchRepository->shouldReceive('find')
            ->once()
            ->with($branchId)
            ->andReturn($branch);

        $this->bookingRepository->shouldReceive('getBookingsForDate')
            ->once()
            ->with($branchId, $date, $staffId)
            ->andReturn($existingBookings);

        $this->branchRepository->shouldReceive('getAvailableStaff')
            ->andReturn([['id' => 5, 'name' => 'Specific Staff']]);

        // Act
        $result = $this->branchService->getAvailableSlots($branchId, $date, $serviceId, $staffId);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(18, $result);
    }

    /** @test */
    public function it_generates_correct_time_slots()
    {
        // Arrange
        $branchId = 1;
        $date = '2025-11-01';
        $serviceId = 1;

        $branch = new Branch([
            'id' => $branchId,
            'name' => ['en' => 'Main Branch'],
        ]);

        $existingBookings = new Collection();

        $this->branchRepository->shouldReceive('find')
            ->once()
            ->with($branchId)
            ->andReturn($branch);

        $this->bookingRepository->shouldReceive('getBookingsForDate')
            ->once()
            ->andReturn($existingBookings);

        $this->branchRepository->shouldReceive('getAvailableStaff')
            ->andReturn([['id' => 1, 'name' => 'Staff']]);

        // Act
        $result = $this->branchService->getAvailableSlots($branchId, $date, $serviceId);

        // Assert - Check specific time slots
        $times = array_column($result, 'time');
        
        // First slot should be 09:00
        $this->assertEquals('09:00', $times[0]);
        
        // Second slot should be 09:30
        $this->assertEquals('09:30', $times[1]);
        
        // Last slot should be 17:30 (last slot before 18:00 end time)
        $this->assertEquals('17:30', $times[17]);
    }

    /** @test */
    public function it_marks_all_slots_as_available_when_no_bookings()
    {
        // Arrange
        $branchId = 1;
        $date = '2025-11-01';
        $serviceId = 1;

        $branch = new Branch([
            'id' => $branchId,
            'name' => ['en' => 'Main Branch'],
        ]);

        $existingBookings = new Collection(); // No bookings

        $this->branchRepository->shouldReceive('find')
            ->once()
            ->with($branchId)
            ->andReturn($branch);

        $this->bookingRepository->shouldReceive('getBookingsForDate')
            ->once()
            ->andReturn($existingBookings);

        $this->branchRepository->shouldReceive('getAvailableStaff')
            ->andReturn([['id' => 1, 'name' => 'Staff']]);

        // Act
        $result = $this->branchService->getAvailableSlots($branchId, $date, $serviceId);

        // Assert - All slots should be available
        foreach ($result as $slot) {
            $this->assertTrue($slot['available']);
            $this->assertArrayHasKey('staff', $slot);
            $this->assertArrayNotHasKey('reason', $slot);
        }
    }

    /** @test */
    public function it_marks_all_slots_as_unavailable_when_fully_booked()
    {
        // Arrange
        $branchId = 1;
        $date = '2025-11-01';
        $serviceId = 1;

        $branch = new Branch([
            'id' => $branchId,
            'name' => ['en' => 'Main Branch'],
        ]);

        // Create bookings for all time slots
        $bookings = [];
        $startTime = Carbon::parse($date)->setTime(9, 0);
        $endTime = Carbon::parse($date)->setTime(18, 0);
        
        while ($startTime->lt($endTime)) {
            $timeStr = $startTime->format('H:i');
            $booking = Mockery::mock(Booking::class)->makePartial();
            $booking->booking_time = $timeStr;
            $booking->shouldReceive('getAttribute')->with('booking_time')->andReturn($timeStr);
            $bookings[] = $booking;
            $startTime->addMinutes(30);
        }
        $existingBookings = new Collection($bookings);

        $this->branchRepository->shouldReceive('find')
            ->once()
            ->with($branchId)
            ->andReturn($branch);

        $this->bookingRepository->shouldReceive('getBookingsForDate')
            ->once()
            ->andReturn($existingBookings);

        $this->branchRepository->shouldReceive('getAvailableStaff')
            ->andReturn([]);

        // Act
        $result = $this->branchService->getAvailableSlots($branchId, $date, $serviceId);

        // Assert - All slots should be unavailable
        foreach ($result as $slot) {
            $this->assertFalse($slot['available']);
            $this->assertArrayHasKey('reason', $slot);
            $this->assertArrayNotHasKey('staff', $slot);
        }
    }

    /** @test */
    public function it_can_get_nearby_branches()
    {
        // Arrange
        $latitude = 10.762622;
        $longitude = 106.660172;
        $radiusKm = 5.0;

        $nearbyBranches = new Collection([
            new Branch([
                'id' => 1,
                'name' => ['en' => 'Branch 1'],
                'latitude' => 10.763,
                'longitude' => 106.661,
            ]),
            new Branch([
                'id' => 2,
                'name' => ['en' => 'Branch 2'],
                'latitude' => 10.765,
                'longitude' => 106.658,
            ]),
        ]);

        $this->branchRepository->shouldReceive('getNearby')
            ->once()
            ->with($latitude, $longitude, $radiusKm)
            ->andReturn($nearbyBranches);

        // Act
        $result = $this->branchService->getNearbyBranches($latitude, $longitude, $radiusKm);

        // Assert
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
    }

    /** @test */
    public function it_can_get_nearby_branches_with_default_radius()
    {
        // Arrange
        $latitude = 10.762622;
        $longitude = 106.660172;

        $nearbyBranches = new Collection([
            new Branch(['id' => 1, 'name' => ['en' => 'Branch 1']]),
        ]);

        $this->branchRepository->shouldReceive('getNearby')
            ->once()
            ->with($latitude, $longitude, 10) // Default radius is 10km
            ->andReturn($nearbyBranches);

        // Act
        $result = $this->branchService->getNearbyBranches($latitude, $longitude);

        // Assert
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(1, $result);
    }

    /** @test */
    public function it_returns_empty_collection_when_no_nearby_branches()
    {
        // Arrange
        $latitude = 10.762622;
        $longitude = 106.660172;
        $radiusKm = 1.0;

        $nearbyBranches = new Collection();

        $this->branchRepository->shouldReceive('getNearby')
            ->once()
            ->with($latitude, $longitude, $radiusKm)
            ->andReturn($nearbyBranches);

        // Act
        $result = $this->branchService->getNearbyBranches($latitude, $longitude, $radiusKm);

        // Assert
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(0, $result);
    }

    /** @test */
    public function it_includes_available_staff_for_available_time_slots()
    {
        // Arrange
        $branchId = 1;
        $date = '2025-11-01';
        $serviceId = 1;

        $branch = new Branch([
            'id' => $branchId,
            'name' => ['en' => 'Main Branch'],
        ]);

        $existingBookings = new Collection();

        $availableStaff = [
            ['id' => 1, 'name' => 'John Doe', 'avatar' => 'avatar1.jpg'],
            ['id' => 2, 'name' => 'Jane Smith', 'avatar' => 'avatar2.jpg'],
        ];

        $this->branchRepository->shouldReceive('find')
            ->once()
            ->with($branchId)
            ->andReturn($branch);

        $this->bookingRepository->shouldReceive('getBookingsForDate')
            ->once()
            ->andReturn($existingBookings);

        $this->branchRepository->shouldReceive('getAvailableStaff')
            ->andReturn($availableStaff);

        // Act
        $result = $this->branchService->getAvailableSlots($branchId, $date, $serviceId);

        // Assert
        $firstSlot = $result[0];
        $this->assertTrue($firstSlot['available']);
        $this->assertArrayHasKey('staff', $firstSlot);
        $this->assertIsArray($firstSlot['staff']);
        $this->assertCount(2, $firstSlot['staff']);
    }

    /** @test */
    public function it_handles_mixed_availability_correctly()
    {
        // Arrange
        $branchId = 1;
        $date = '2025-11-01';
        $serviceId = 1;

        $branch = new Branch([
            'id' => $branchId,
            'name' => ['en' => 'Main Branch'],
        ]);

        // Book specific time slots
        $times = ['09:00', '09:30', '10:00', '15:00', '15:30'];
        $bookings = [];
        foreach ($times as $time) {
            $booking = Mockery::mock(Booking::class)->makePartial();
            $booking->booking_time = $time;
            $booking->shouldReceive('getAttribute')->with('booking_time')->andReturn($time);
            $bookings[] = $booking;
        }
        $existingBookings = new Collection($bookings);

        $this->branchRepository->shouldReceive('find')
            ->once()
            ->with($branchId)
            ->andReturn($branch);

        $this->bookingRepository->shouldReceive('getBookingsForDate')
            ->once()
            ->andReturn($existingBookings);

        $this->branchRepository->shouldReceive('getAvailableStaff')
            ->andReturn([['id' => 1, 'name' => 'Staff']]);

        // Act
        $result = $this->branchService->getAvailableSlots($branchId, $date, $serviceId);

        // Assert
        // Check specific slots
        $slotsByTime = [];
        foreach ($result as $slot) {
            $slotsByTime[$slot['time']] = $slot;
        }

        // These should be unavailable
        $this->assertFalse($slotsByTime['09:00']['available']);
        $this->assertFalse($slotsByTime['09:30']['available']);
        $this->assertFalse($slotsByTime['10:00']['available']);
        $this->assertFalse($slotsByTime['15:00']['available']);
        $this->assertFalse($slotsByTime['15:30']['available']);

        // These should be available
        $this->assertTrue($slotsByTime['10:30']['available']);
        $this->assertTrue($slotsByTime['11:00']['available']);
        $this->assertTrue($slotsByTime['14:00']['available']);
        $this->assertTrue($slotsByTime['14:30']['available']);
        $this->assertTrue($slotsByTime['16:00']['available']);
    }
}

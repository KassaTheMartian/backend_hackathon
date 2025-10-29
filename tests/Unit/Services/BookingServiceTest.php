<?php

namespace Tests\Unit\Services;

use App\Data\Booking\BookingData;
use App\Data\Booking\UpdateBookingData;
use App\Mail\BookingConfirmationMail;
use App\Mail\OtpMail;
use App\Models\Booking;
use App\Models\OtpVerification;
use App\Models\Service;
use App\Models\User;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\OtpRepositoryInterface;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use App\Services\BookingService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    private BookingService $bookingService;
    private BookingRepositoryInterface $bookingRepository;
    private OtpRepositoryInterface $otpRepository;
    private ServiceRepositoryInterface $serviceRepository;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mock repositories
        $this->bookingRepository = Mockery::mock(BookingRepositoryInterface::class);
        $this->otpRepository = Mockery::mock(OtpRepositoryInterface::class);
        $this->serviceRepository = Mockery::mock(ServiceRepositoryInterface::class);

        // Create service instance
        $this->bookingService = new BookingService(
            $this->bookingRepository,
            $this->otpRepository,
            $this->serviceRepository
        );

        // Fake mail
        Mail::fake();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function admin_can_list_all_bookings()
    {
        // Arrange
        $admin = User::factory()->make(['id' => 1, 'is_admin' => true]);
        Auth::shouldReceive('user')->andReturn($admin);

        $request = Request::create('/bookings', 'GET');
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->bookingRepository->shouldReceive('paginateWithFilters')
            ->once()
            ->with($request)
            ->andReturn($paginator);

        // Act
        $result = $this->bookingService->list($request);

        // Assert
        $this->assertSame($paginator, $result);
    }

    /** @test */
    public function user_can_list_only_their_own_bookings()
    {
        // Arrange
        $user = User::factory()->make(['id' => 2, 'is_admin' => false]);
        Auth::shouldReceive('user')->andReturn($user);

        $request = Request::create('/bookings', 'GET');
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->bookingRepository->shouldReceive('paginateWithFilters')
            ->once()
            ->with(Mockery::on(function ($req) use ($user) {
                return $req->input('user_id') === $user->id;
            }))
            ->andReturn($paginator);

        // Act
        $result = $this->bookingService->list($request);

        // Assert
        $this->assertSame($paginator, $result);
    }

    /** @test */
    public function it_can_create_booking_for_authenticated_user()
    {
        // Arrange
        $user = User::factory()->make(['id' => 1, 'email' => 'user@example.com']);
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('id')->andReturn($user->id);
        Auth::shouldReceive('user')->andReturn($user);

        $service = new Service([
            'id' => 1,
            'duration' => 60,
            'price' => 100.00,
        ]);

        $bookingData = new BookingData(
            branch_id: 1,
            service_id: 1,
            staff_id: 1,
            booking_date: '2025-11-01',
            booking_time: '10:00',
            notes: 'Test booking',
            promotion_code: null,
            guest_name: null,
            guest_email: null,
            guest_phone: null
        );

        $booking = new Booking([
            'id' => 1,
            'user_id' => $user->id,
            'service_id' => 1,
            'branch_id' => 1,
            'booking_date' => '2025-11-01',
            'booking_time' => '10:00',
            'status' => 'pending',
        ]);

        $this->serviceRepository->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($service);

        $this->bookingRepository->shouldReceive('isTimeSlotAvailable')
            ->once()
            ->with(1, '2025-11-01', '10:00', 1)
            ->andReturn(true);

        $this->bookingRepository->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) use ($user) {
                return $data['user_id'] === $user->id
                    && $data['service_price'] == 100.00
                    && $data['status'] === 'pending'
                    && $data['payment_status'] === 'pending';
            }))
            ->andReturn($booking);

        // Act
        $result = $this->bookingService->create($bookingData);

        // Assert
        $this->assertInstanceOf(Booking::class, $result);
        Mail::assertSent(BookingConfirmationMail::class);
    }

    /** @test */
    public function it_throws_exception_when_creating_booking_without_authentication()
    {
        // Arrange
        Auth::shouldReceive('check')->andReturn(false);

        $bookingData = new BookingData(
            branch_id: 1,
            service_id: 1,
            staff_id: 1,
            booking_date: '2025-11-01',
            booking_time: '10:00',
            notes: null,
            promotion_code: null,
            guest_name: null,
            guest_email: null,
            guest_phone: null
        );

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->bookingService->create($bookingData);
    }

    /** @test */
    public function it_throws_exception_when_time_slot_is_unavailable()
    {
        // Arrange
        $user = User::factory()->make(['id' => 1, 'email' => 'user@example.com']);
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('id')->andReturn($user->id);

        $service = new Service([
            'id' => 1,
            'duration' => 60,
            'price' => 100.00,
        ]);

        $bookingData = new BookingData(
            branch_id: 1,
            service_id: 1,
            staff_id: 1,
            booking_date: '2025-11-01',
            booking_time: '10:00',
            notes: null,
            promotion_code: null,
            guest_name: null,
            guest_email: null,
            guest_phone: null
        );

        $this->serviceRepository->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($service);

        $this->bookingRepository->shouldReceive('isTimeSlotAvailable')
            ->once()
            ->with(1, '2025-11-01', '10:00', 1)
            ->andReturn(false);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->bookingService->create($bookingData);
    }

    /** @test */
    public function it_can_find_booking_by_id()
    {
        // Arrange
        $booking = new Booking(['id' => 1]);

        $this->bookingRepository->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($booking);

        // Act
        $result = $this->bookingService->find(1);

        // Assert
        $this->assertSame($booking, $result);
    }

    /** @test */
    public function it_can_update_booking()
    {
        // Arrange
        $existingBooking = new Booking([
            'id' => 1,
            'branch_id' => 1,
            'booking_date' => '2025-11-01',
            'booking_time' => '10:00',
            'staff_id' => 1,
        ]);

        $updateData = new UpdateBookingData(
            branch_id: null,
            service_id: null,
            staff_id: null,
            booking_date: '2025-11-02',
            booking_time: '14:00',
            notes: null,
            promotion_code: null
        );

        $updatedBooking = new Booking([
            'id' => 1,
            'booking_date' => '2025-11-02',
            'booking_time' => '14:00',
        ]);

        $this->bookingRepository->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($existingBooking);

        $this->bookingRepository->shouldReceive('isTimeSlotAvailable')
            ->once()
            ->with(1, '2025-11-02', '14:00', null)
            ->andReturn(true);

        $this->bookingRepository->shouldReceive('update')
            ->once()
            ->with(1, Mockery::on(function ($data) {
                return $data['booking_date'] === '2025-11-02'
                    && $data['booking_time'] === '14:00';
            }))
            ->andReturn($updatedBooking);

        // Act
        $result = $this->bookingService->update(1, $updateData);

        // Assert
        $this->assertSame($updatedBooking, $result);
    }

    /** @test */
    public function it_throws_exception_when_updating_to_unavailable_time_slot()
    {
        // Arrange
        $existingBooking = new Booking([
            'id' => 1,
            'branch_id' => 1,
            'booking_date' => '2025-11-01',
            'booking_time' => '10:00',
            'staff_id' => 1,
        ]);

        $updateData = new UpdateBookingData(
            branch_id: null,
            service_id: null,
            staff_id: null,
            booking_date: '2025-11-02',
            booking_time: '14:00',
            notes: null,
            promotion_code: null
        );

        $this->bookingRepository->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($existingBooking);

        $this->bookingRepository->shouldReceive('isTimeSlotAvailable')
            ->once()
            ->with(1, '2025-11-02', '14:00', null)
            ->andReturn(false);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->bookingService->update(1, $updateData);
    }

    /** @test */
    public function it_can_cancel_booking()
    {
        // Arrange
        $booking = new Booking(['id' => 1, 'status' => 'confirmed']);
        $cancelledBooking = new Booking(['id' => 1, 'status' => 'cancelled']);

        $this->bookingRepository->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($booking);

        $this->bookingRepository->shouldReceive('cancel')
            ->once()
            ->with($booking, 'Customer request')
            ->andReturn($cancelledBooking);

        // Act
        $result = $this->bookingService->cancel(1, 'Customer request');

        // Assert
        $this->assertSame($cancelledBooking, $result);
    }

    /** @test */
    public function it_returns_null_when_cancelling_non_existent_booking()
    {
        // Arrange
        $this->bookingRepository->shouldReceive('find')
            ->once()
            ->with(999)
            ->andReturn(null);

        // Act
        $result = $this->bookingService->cancel(999, 'Test reason');

        // Assert
        $this->assertNull($result);
    }

    /** @test */
    public function it_can_get_user_bookings()
    {
        // Arrange
        $user = User::factory()->make(['id' => 1]);
        Auth::shouldReceive('user')->andReturn($user);

        $request = Request::create('/my-bookings', 'GET');
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->bookingRepository->shouldReceive('paginateWithFilters')
            ->once()
            ->with(Mockery::on(function ($req) use ($user) {
                return $req->input('user_id') === $user->id;
            }))
            ->andReturn($paginator);

        // Act
        $result = $this->bookingService->myBookings($request);

        // Assert
        $this->assertSame($paginator, $result);
    }

    /** @test */
    public function it_throws_exception_when_getting_bookings_without_authentication()
    {
        // Arrange
        Auth::shouldReceive('user')->andReturn(null);
        $request = Request::create('/my-bookings', 'GET');

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->bookingService->myBookings($request);
    }

    /** @test */
    public function it_can_check_time_slot_availability()
    {
        // Arrange
        $this->bookingRepository->shouldReceive('isTimeSlotAvailable')
            ->once()
            ->with(1, '2025-11-01', '10:00', 1)
            ->andReturn(true);

        // Act
        $result = $this->bookingService->isTimeSlotAvailable(1, '2025-11-01', '10:00', 1);

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_get_available_slots()
    {
        // Arrange
        $service = new Service([
            'id' => 1,
            'duration' => 60,
            'price' => 100.00,
        ]);

        $this->serviceRepository->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($service);

        // Mock availability for some time slots
        $this->bookingRepository->shouldReceive('isTimeSlotAvailable')
            ->andReturn(true, false, true, false); // Alternate available/unavailable

        // Act
        $result = $this->bookingService->availableSlots(1, 1, '2025-11-01', null, 60);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('date', $result);
        $this->assertArrayHasKey('granularity', $result);
        $this->assertArrayHasKey('available', $result);
        $this->assertEquals('2025-11-01', $result['date']);
        $this->assertEquals(60, $result['granularity']);
    }

    /** @test */
    public function it_throws_exception_when_service_not_found_for_available_slots()
    {
        // Arrange
        $this->serviceRepository->shouldReceive('find')
            ->once()
            ->with(999)
            ->andReturn(null);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->bookingService->availableSlots(1, 999, '2025-11-01');
    }

    /** @test */
    public function it_can_reschedule_booking()
    {
        // Arrange
        $futureDate = now()->addDays(10);
        $booking = Mockery::mock(Booking::class)->makePartial();
        $booking->id = 1;
        $booking->branch_id = 1;
        $booking->booking_date = $futureDate->format('Y-m-d');
        $booking->booking_time = '10:00';
        $booking->staff_id = 1;
        $booking->shouldReceive('getAttribute')
            ->with('booking_date')
            ->andReturn($futureDate->format('Y-m-d'));
        $booking->shouldReceive('getAttribute')
            ->with('booking_time')
            ->andReturn('10:00');

        $rescheduledBooking = new Booking([
            'id' => 1,
            'branch_id' => 1,
            'booking_date' => now()->addDays(11)->format('Y-m-d'),
            'booking_time' => '14:00',
            'staff_id' => 1,
        ]);

        $this->bookingRepository->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($booking);

        $this->bookingRepository->shouldReceive('isTimeSlotAvailable')
            ->once()
            ->with(1, now()->addDays(11)->format('Y-m-d'), '14:00', 1)
            ->andReturn(true);

        $this->bookingRepository->shouldReceive('update')
            ->once()
            ->with(1, Mockery::on(function ($data) {
                return isset($data['booking_date'])
                    && isset($data['booking_time'])
                    && isset($data['staff_id']);
            }))
            ->andReturn($rescheduledBooking);

        // Act
        $result = $this->bookingService->reschedule(
            1,
            now()->addDays(11)->format('Y-m-d'),
            '14:00',
            1
        );

        // Assert
        $this->assertSame($rescheduledBooking, $result);
    }

    /** @test */
    public function it_throws_exception_when_rescheduling_within_cutoff_time()
    {
        // Arrange
        $nearDate = now()->addHours(2);
        $booking = Mockery::mock(Booking::class)->makePartial();
        $booking->id = 1;
        $booking->branch_id = 1;
        $booking->booking_date = $nearDate->format('Y-m-d');
        $booking->booking_time = $nearDate->format('H:i');
        $booking->staff_id = 1;
        $booking->shouldReceive('getAttribute')
            ->with('booking_date')
            ->andReturn($nearDate->format('Y-m-d'));
        $booking->shouldReceive('getAttribute')
            ->with('booking_time')
            ->andReturn($nearDate->format('H:i'));

        $this->bookingRepository->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($booking);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->bookingService->reschedule(
            1,
            now()->addDays(5)->format('Y-m-d'),
            '14:00'
        );
    }

    /** @test */
    public function it_can_send_guest_booking_otp()
    {
        // Arrange
        $email = 'guest@example.com';

        $this->otpRepository->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) use ($email) {
                return $data['phone_or_email'] === $email
                    && $data['type'] === 'email'
                    && $data['purpose'] === 'guest_booking'
                    && isset($data['otp'])
                    && $data['attempts'] === 0;
            }))
            ->andReturn(new OtpVerification());

        // Act
        $result = $this->bookingService->sendGuestBookingOtp($email);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message', $result);
        Mail::assertSent(OtpMail::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });
    }

    /** @test */
    public function it_can_get_guest_bookings_with_valid_otp()
    {
        // Arrange
        $email = 'guest@example.com';
        $otp = '123456';

        $otpRecord = Mockery::mock(OtpVerification::class)->makePartial();
        $otpRecord->id = 1;
        $otpRecord->otp = $otp;
        $otpRecord->shouldReceive('isLockedOut')
            ->once()
            ->andReturn(false);
        $otpRecord->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(1);
        $otpRecord->shouldReceive('getAttribute')
            ->with('otp')
            ->andReturn($otp);

        $paginator = Mockery::mock(LengthAwarePaginator::class);
        $paginator->shouldReceive('through')
            ->once()
            ->andReturn($paginator);

        $this->otpRepository->shouldReceive('findLatestValid')
            ->once()
            ->with($email, 'guest_booking')
            ->andReturn($otpRecord);

        $this->otpRepository->shouldReceive('markAsVerified')
            ->once()
            ->with($otpRecord->id)
            ->andReturn(true);

        $this->bookingRepository->shouldReceive('getGuestBookingsByEmail')
            ->once()
            ->with($email, 15)
            ->andReturn($paginator);

        // Act
        $result = $this->bookingService->guestBookings($email, $otp);

        // Assert
        $this->assertSame($paginator, $result);
    }

    /** @test */
    public function it_throws_exception_when_guest_otp_not_found()
    {
        // Arrange
        $email = 'guest@example.com';
        $otp = '123456';

        $this->otpRepository->shouldReceive('findLatestValid')
            ->once()
            ->with($email, 'guest_booking')
            ->andReturn(null);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->bookingService->guestBookings($email, $otp);
    }

    /** @test */
    public function it_throws_exception_when_guest_otp_is_locked_out()
    {
        // Arrange
        $email = 'guest@example.com';
        $otp = '123456';

        $otpRecord = Mockery::mock(OtpVerification::class)->makePartial();
        $otpRecord->shouldReceive('isLockedOut')
            ->once()
            ->andReturn(true);

        $this->otpRepository->shouldReceive('findLatestValid')
            ->once()
            ->with($email, 'guest_booking')
            ->andReturn($otpRecord);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->bookingService->guestBookings($email, $otp);
    }

    /** @test */
    public function it_throws_exception_and_increments_attempts_when_guest_otp_is_invalid()
    {
        // Arrange
        $email = 'guest@example.com';
        $correctOtp = '123456';
        $wrongOtp = '654321';

        $otpRecord = Mockery::mock(OtpVerification::class)->makePartial();
        $otpRecord->id = 1;
        $otpRecord->otp = $correctOtp;
        $otpRecord->shouldReceive('isLockedOut')
            ->once()
            ->andReturn(false);
        $otpRecord->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(1);
        $otpRecord->shouldReceive('getAttribute')
            ->with('otp')
            ->andReturn($correctOtp);

        $this->otpRepository->shouldReceive('findLatestValid')
            ->once()
            ->with($email, 'guest_booking')
            ->andReturn($otpRecord);

        $this->otpRepository->shouldReceive('incrementAttempts')
            ->once()
            ->with($otpRecord->id)
            ->andReturn(true);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->bookingService->guestBookings($email, $wrongOtp);
    }

    /** @test */
    public function it_returns_null_when_updating_non_existent_booking()
    {
        // Arrange
        $updateData = new UpdateBookingData(
            branch_id: null,
            service_id: null,
            staff_id: null,
            booking_date: null,
            booking_time: null,
            notes: 'Updated notes',
            promotion_code: null
        );

        $this->bookingRepository->shouldReceive('find')
            ->never();

        $this->bookingRepository->shouldReceive('update')
            ->once()
            ->with(999, Mockery::type('array'))
            ->andReturn(null);

        // Act
        $result = $this->bookingService->update(999, $updateData);

        // Assert
        $this->assertNull($result);
    }

    /** @test */
    public function it_returns_null_when_rescheduling_non_existent_booking()
    {
        // Arrange
        $this->bookingRepository->shouldReceive('find')
            ->once()
            ->with(999)
            ->andReturn(null);

        // Act
        $result = $this->bookingService->reschedule(
            999,
            now()->addDays(5)->format('Y-m-d'),
            '14:00'
        );

        // Assert
        $this->assertNull($result);
    }

    /** @test */
    public function it_throws_exception_when_rescheduling_to_unavailable_slot()
    {
        // Arrange
        $futureDate = now()->addDays(10);
        $booking = Mockery::mock(Booking::class)->makePartial();
        $booking->id = 1;
        $booking->branch_id = 1;
        $booking->booking_date = $futureDate->format('Y-m-d');
        $booking->booking_time = '10:00';
        $booking->staff_id = 1;
        $booking->shouldReceive('getAttribute')
            ->with('booking_date')
            ->andReturn($futureDate->format('Y-m-d'));
        $booking->shouldReceive('getAttribute')
            ->with('booking_time')
            ->andReturn('10:00');

        $this->bookingRepository->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($booking);

        $this->bookingRepository->shouldReceive('isTimeSlotAvailable')
            ->once()
            ->with(1, now()->addDays(11)->format('Y-m-d'), '14:00', 1)
            ->andReturn(false);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->bookingService->reschedule(
            1,
            now()->addDays(11)->format('Y-m-d'),
            '14:00',
            1
        );
    }
}

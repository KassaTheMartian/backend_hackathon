<?php

namespace App\Services;

use App\Data\Booking\BookingData;
use App\Data\Booking\UpdateBookingData;
use App\Mail\OtpMail;
use App\Mail\BookingConfirmationMail;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\OtpRepositoryInterface;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use App\Services\Contracts\BookingServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class BookingService implements BookingServiceInterface
{
	/**
	 * Create a new BookingService instance.
	 *
	 * @param BookingRepositoryInterface $bookings The booking repository
	 * @param OtpRepositoryInterface $otpRepository The OTP repository
	 * @param ServiceRepositoryInterface $services The service repository
	 */
	public function __construct(
		private readonly BookingRepositoryInterface $bookings,
		private readonly OtpRepositoryInterface $otpRepository,
		private readonly ServiceRepositoryInterface $services
	) {
	}

	/**
	 * Get a paginated list of bookings.
	 * - Admin: Can view all bookings
	 * - User: Can view their own bookings
	 *
	 * @param Request $request The HTTP request
	 * @return LengthAwarePaginator The paginated bookings
	 */
	public function list(Request $request): LengthAwarePaginator
	{
		$user = Auth::user();
		
		// Admin can view all bookings
		if ($user && $user->is_admin) {
			return $this->bookings->paginateWithFilters($request);
		}
		
		// User can view their own bookings
		if ($user) {
			$request->merge(['user_id' => $user->id]);
			return $this->bookings->paginateWithFilters($request);
		}
		
		return $this->bookings->paginateWithFilters($request);
	}

	/**
	 * Create a new booking.
	 *
	 * @param BookingData $data The booking data
	 * @return Model The created booking
	 */
	public function create(BookingData $data): Model
	{
		$payload = $data->toArray();
		
        // Only allow authenticated users to create bookings; guest flow handled elsewhere
        if (!Auth::check()) {
            throw new \Exception(__('bookings.user_not_authenticated'));
        }
        $payload['user_id'] = Auth::id();
        unset($payload['guest_name'], $payload['guest_email'], $payload['guest_phone']);
		
		// Get duration and price from service via repository
		$service = $this->services->find($data->service_id);
		if ($service) {
			$payload['duration'] = $service->duration;
			$payload['service_price'] = $service->price;
			$payload['total_amount'] = $service->price; // Initially same as service price
			$payload['discount_amount'] = 0;
		}

		// Check availability
		$available = $this->isTimeSlotAvailable(
			$payload['branch_id'],
			$payload['booking_date'],
			$payload['booking_time'],
			$payload['staff_id'] ?? null
		);
        if (!$available) {
            throw new \Exception(__('bookings.time_slot_unavailable'));
		}

		// Set default status
		$payload['status'] = 'pending';
		$payload['payment_status'] = 'pending';
		
        $booking = $this->bookings->create($payload);

        // Send confirmation email to user
        $recipient = Auth::user()?->email;
        if ($recipient) {
            Mail::to($recipient)->send(new BookingConfirmationMail($booking));
        }

        return $booking;
	}

	/**
	 * Find a booking by ID.
	 *
	 * @param int $id The booking ID
	 * @return Model|null The booking if found, null otherwise
	 */
	public function find(int $id): ?Model
	{
		return $this->bookings->find($id);
	}

	/**
	 * Update a booking.
	 *
	 * @param int $id The booking ID
	 * @param UpdateBookingData $data The booking data
	 * @return Model|null The updated booking if found, null otherwise
	 */
	public function update(int $id, UpdateBookingData $data): ?Model
	{
		$payload = $data->toArray();
		if (isset($payload['booking_date']) || isset($payload['booking_time']) || isset($payload['staff_id']) || isset($payload['branch_id'])) {
			$existing = $this->bookings->find($id);
			if (!$existing) {
				return null;
			}
			$branchId = $payload['branch_id'] ?? $existing->branch_id;
			$date = $payload['booking_date'] ?? $existing->booking_date;
			$time = $payload['booking_time'] ?? $existing->booking_time;
			$staffId = array_key_exists('staff_id', $payload) ? $payload['staff_id'] : $existing->staff_id;
			$available = $this->isTimeSlotAvailable($branchId, $date, $time, $staffId);
            if (!$available) {
                throw new \Exception(__('bookings.time_slot_unavailable'));
			}
		}
		return $this->bookings->update($id, $payload);
	}

	/**
	 * Cancel a booking.
	 *
	 * @param int $id The booking ID
	 * @param string $reason The cancellation reason
	 * @return Model|null The cancelled booking if found, null otherwise
	 */
	public function cancel(int $id, string $reason): ?Model
	{
		$booking = $this->bookings->find($id);
		if (!$booking) {
			return null;
		}
		
		return $this->bookings->cancel($booking, $reason);
	}

	/**
	 * Get user's bookings.
	 *
	 * @param Request $request The HTTP request
	 * @return LengthAwarePaginator The paginated user's bookings
	 */
	public function myBookings(Request $request): LengthAwarePaginator
	{
		$user = Auth::user();
        if (!$user) {
            throw new \Exception(__('bookings.user_not_authenticated'));
		}
		$request->merge(['user_id' => $user->id]);
		return $this->bookings->paginateWithFilters($request);
	}

	/**
	 * Check time slot availability.
	 */
	public function isTimeSlotAvailable(int $branchId, string $date, string $time, ?int $staffId = null): bool
	{
		return $this->bookings->isTimeSlotAvailable($branchId, $date, $time, $staffId);
	}

	public function availableSlots(int $branchId, int $serviceId, string $date, ?int $staffId = null, int $granularity = 15): array
	{
		// Get service via repository
		$service = $this->services->find($serviceId);
        if (!$service) {
            throw new \Exception(__('bookings.service_not_found'));
		}
		
		$start = \Carbon\Carbon::parse($date . ' 08:00');
		$end = \Carbon\Carbon::parse($date . ' 20:00');
		$slots = [];
		for ($t = $start->copy(); $t < $end; $t->addMinutes($granularity)) {
			$time = $t->format('H:i');
			if ($this->isTimeSlotAvailable($branchId, $date, $time, $staffId)) {
				$slots[] = $time;
			}
		}
		return [
			'date' => $date,
			'granularity' => $granularity,
			'available' => $slots,
		];
	}

	public function reschedule(int $id, string $bookingDate, string $bookingTime, ?int $staffId = null): ?Model
	{
		$booking = $this->bookings->find($id);
		if (!$booking) {
			return null;
		}
        if (\Carbon\Carbon::parse($booking->booking_date . ' ' . $booking->booking_time) < now()->addHours(4)) {
            throw new \Exception(__('bookings.reschedule_cutoff_exceeded'));
		}
		$branchId = $booking->branch_id;
		$staff = $staffId ?? $booking->staff_id;
        if (!$this->isTimeSlotAvailable($branchId, $bookingDate, $bookingTime, $staff)) {
            throw new \Exception(__('bookings.time_slot_unavailable'));
		}
		return $this->bookings->update($id, [
			'booking_date' => $bookingDate,
			'booking_time' => $bookingTime,
			'staff_id' => $staff,
		]);
	}

	// Send OTP to guest email for booking verification
	public function sendGuestBookingOtp(string $email): array
	{
		$otp = (string) random_int(100000, 999999);
		$this->otpRepository->create([
			'phone_or_email' => $email,
			'otp' => $otp,
			'type' => 'email',
			'purpose' => 'guest_booking',
			'expires_at' => now()->addMinutes(10),
			'attempts' => 0,
		]);

		// Send email with beautiful template
		Mail::to($email)->send(new OtpMail($otp, 'guest_booking', 10));

        return ['message' => __('bookings.otp_sent')];
	}

	// Get guest bookings by email with OTP verification
	public function guestBookings(string $email, string $otp, int $perPage = 15): LengthAwarePaginator
	{
		// Verify OTP via repository
		$record = $this->otpRepository->findLatestValid($email, 'guest_booking');

        if (!$record) {
            throw new \Exception(__('bookings.otp_invalid_or_expired'));
		}
		if ($record->isLockedOut()) {
            throw new \Exception(__('bookings.otp_locked'));
		}
		if ($record->otp !== $otp) {
			$this->otpRepository->incrementAttempts($record->id);
            throw new \Exception(__('bookings.otp_invalid_or_expired'));
		}
		$this->otpRepository->markAsVerified($record->id);

		// Get guest bookings via repository
		return $this->bookings->getGuestBookingsByEmail($email, $perPage)
			->through(fn ($model) => \App\Http\Resources\Booking\BookingResource::make($model));
	}
}
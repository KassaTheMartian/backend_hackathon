<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Requests\Booking\UpdateBookingRequest;
use App\Http\Requests\Booking\CancelBookingRequest;
use App\Http\Requests\Booking\AvailabilityRequest;
use App\Http\Requests\Booking\RescheduleBookingRequest;
use App\Http\Requests\Booking\GuestBookingSendOtpRequest;
use App\Http\Requests\Booking\GuestBookingHistoryRequest;
use App\Models\OtpVerification;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\Booking\BookingResource;
use App\Http\Responses\ApiResponse;
use App\Models\Booking;
use App\Services\Contracts\BookingServiceInterface;
use App\Data\Booking\BookingData;
use App\Data\Booking\UpdateBookingData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * Create a new BookingController instance.
     *
     * @param BookingServiceInterface $service The booking service
     */
    public function __construct(private readonly BookingServiceInterface $service)
    {
        //
    }

    /**
     * @OA\Get(
     *     path="/api/v1/bookings",
     *     summary="List bookings",
     *     tags={"Bookings"},
     *     
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Display a listing of bookings.
     *
     * @param Request $request The HTTP request
     * @return JsonResponse The paginated list of bookings
     */
    public function index(Request $request): JsonResponse
    {
        
        $items = $this->service->list($request)->through(fn ($model) => BookingResource::make($model));
        return $this->paginated($items, 'Bookings retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/bookings",
     *     summary="Create booking",
     *     tags={"Bookings"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"branch_id","service_id","staff_id","booking_date","booking_time"},
     *             @OA\Property(property="branch_id", type="integer"),
     *             @OA\Property(property="service_id", type="integer"),
     *             @OA\Property(property="staff_id", type="integer"),
     *             @OA\Property(property="booking_date", type="string", format="date"),
     *             @OA\Property(property="booking_time", type="string", format="time"),
     *             @OA\Property(property="notes", type="string"),
     *             @OA\Property(property="promotion_code", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=422, description="Validation Error", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Store a newly created booking.
     *
     * @param StoreBookingRequest $request The store booking request
     * @return JsonResponse The created booking response
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        // For guest bookings, verify OTP
        if (!$this->user()) {
            $email = (string)$request->guest_email;
            $otp = (string)$request->guest_email_otp;
            $record = OtpVerification::where('phone_or_email', $email)
                ->where('purpose', 'guest_booking')
                ->unexpired()
                ->unverified()
                ->latest('id')
                ->first();
            if (!$record) {
                return ApiResponse::validationError(['guest_email_otp' => ['Invalid or expired OTP']]);
            }
            if ($record->isLockedOut()) {
                return ApiResponse::validationError(['guest_email_otp' => ['Too many invalid attempts. Please request a new OTP.']]);
            }
            if ($record->otp !== $otp) {
                $record->incrementAttempts();
                return ApiResponse::validationError(['guest_email_otp' => ['Invalid or expired OTP']]);
            }
            $record->markAsVerified();
        }

        $dto = BookingData::from($request->validated());
        $booking = $this->service->create($dto);
        return $this->created(BookingResource::make($booking), 'Booking created successfully. Confirmation email sent.');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/bookings/{id}",
     *     summary="Get booking by id",
     *     tags={"Bookings"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Display the specified booking.
     *
     * @param int $id The booking ID
     * @return JsonResponse The booking response
     */
    public function show(int $id): JsonResponse
    {
        $booking = $this->service->find($id);
        if (!$booking) {
            $this->notFound('Booking');
        }
        
        return $this->ok(BookingResource::make($booking), 'Booking retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/bookings/{id}",
     *     summary="Update booking",
     *     tags={"Bookings"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="branch_id", type="integer"),
     *             @OA\Property(property="service_id", type="integer"),
     *             @OA\Property(property="staff_id", type="integer"),
     *             @OA\Property(property="booking_date", type="string", format="date"),
     *             @OA\Property(property="booking_time", type="string", format="time"),
     *             @OA\Property(property="notes", type="string"),
     *             @OA\Property(property="promotion_code", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=422, description="Validation Error", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Update the specified booking.
     *
     * @param UpdateBookingRequest $request The update booking request
     * @param int $id The booking ID
     * @return JsonResponse The updated booking response
     */
    public function update(UpdateBookingRequest $request, int $id): JsonResponse
    {
        $booking = $this->service->find($id);
        if (!$booking) {
            $this->notFound('Booking');
        }
        
        $dto = UpdateBookingData::from($request->validated());
        $booking = $this->service->update($id, $dto);
        return $this->ok(BookingResource::make($booking), 'Booking updated successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/bookings/{id}/cancel",
     *     summary="Cancel booking",
     *     tags={"Bookings"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"cancellation_reason"},
     *             @OA\Property(property="cancellation_reason", type="string", maxLength=500)
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=422, description="Validation Error", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Cancel the specified booking.
     *
     * @param CancelBookingRequest $request The HTTP request
     * @param int $id The booking ID
     * @return JsonResponse The cancellation response
     */
    public function cancel(CancelBookingRequest $request, int $id): JsonResponse
    {
        $booking = $this->service->find($id);
        if (!$booking) {
            $this->notFound('Booking');
        }
        
        $booking = $this->service->cancel($id, $request->cancellation_reason);
        return $this->ok(BookingResource::make($booking), 'Booking cancelled successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/my-bookings",
     *     summary="Get user's bookings",
     *     tags={"Bookings"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Get user's bookings.
     *
     * @param Request $request The HTTP request
     * @return JsonResponse The paginated list of user's bookings
     */
    public function myBookings(Request $request): JsonResponse
    {
        $items = $this->service->myBookings($request)->through(fn ($model) => BookingResource::make($model));
        return $this->paginated($items, 'My bookings retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/availability",
     *     summary="Search available slots",
     *     tags={"Bookings"},
     *     @OA\Parameter(name="branch_id", in="query", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="service_id", in="query", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="date", in="query", required=true, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="staff_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="granularity", in="query", required=false, @OA\Schema(type="integer", enum={5,10,15,20,30})),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     */
    public function availability(AvailabilityRequest $request): JsonResponse
    {
        $result = $this->service->availableSlots(
            (int)$request->branch_id,
            (int)$request->service_id,
            (string)$request->date,
            $request->input('staff_id'),
            (int)$request->input('granularity', 15)
        );
        return $this->ok($result, 'Available slots retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/bookings/{id}/reschedule",
     *     summary="Reschedule a booking",
     *     tags={"Bookings"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"booking_date","booking_time"},
     *             @OA\Property(property="booking_date", type="string", format="date"),
     *             @OA\Property(property="booking_time", type="string", format="time"),
     *             @OA\Property(property="staff_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     */
    public function reschedule(RescheduleBookingRequest $request, int $id): JsonResponse
    {
        $booking = $this->service->reschedule($id, (string)$request->booking_date, (string)$request->booking_time, $request->input('staff_id'));
        if (!$booking) {
            $this->notFound('Booking');
        }
        return $this->ok(BookingResource::make($booking), 'Booking rescheduled successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/guest-booking/send-otp",
     *     summary="Send OTP for guest booking",
     *     tags={"Bookings"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"guest_email"},
     *             @OA\Property(property="guest_email", type="string", format="email")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     */
    public function sendGuestBookingOtp(GuestBookingSendOtpRequest $request): JsonResponse
    {
        try {
            $email = (string)$request->guest_email;
            $otp = (string)random_int(100000, 999999);
            OtpVerification::create([
                'phone_or_email' => $email,
                'otp' => $otp,
                'type' => 'email',
                'purpose' => 'guest_booking',
                'expires_at' => now()->addMinutes(10),
                'attempts' => 0,
            ]);
            Mail::raw('Your booking verification code is: ' . $otp, function ($message) use ($email) {
                $message->to($email)
                    ->subject('Your Booking OTP Code')
                    ->from(config('mail.from.address'), config('mail.from.name'));
            });
            return $this->ok(['message' => 'OTP sent']);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Failed to send OTP', 'OTP_SEND_ERROR', 422);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/guest-bookings",
     *     summary="Get guest bookings by email with OTP",
     *     tags={"Bookings"},
     *     @OA\Parameter(name="guest_email", in="query", required=true, @OA\Schema(type="string", format="email")),
     *     @OA\Parameter(name="guest_email_otp", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=422, description="Validation Error", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     */
    public function guestBookings(GuestBookingHistoryRequest $request): JsonResponse
    {
        $email = (string)$request->guest_email;
        $otp = (string)$request->guest_email_otp;
        $record = OtpVerification::where('phone_or_email', $email)
            ->where('purpose', 'guest_booking')
            ->unexpired()
            ->unverified()
            ->latest('id')
            ->first();
        if (!$record) {
            return ApiResponse::validationError(['guest_email_otp' => ['Invalid or expired OTP']]);
        }
        if ($record->isLockedOut()) {
            return ApiResponse::validationError(['guest_email_otp' => ['Too many invalid attempts. Please request a new OTP.']]);
        }
        if ($record->otp !== $otp) {
            $record->incrementAttempts();
            return ApiResponse::validationError(['guest_email_otp' => ['Invalid or expired OTP']]);
        }
        $record->markAsVerified();

        $perPage = (int)$request->input('per_page', 15);
        $items = Booking::whereNull('user_id')
            ->where('guest_email', $email)
            ->latest('id')
            ->paginate($perPage)
            ->through(fn ($model) => BookingResource::make($model));

        return $this->paginated($items, 'Guest bookings retrieved successfully');
    }
}

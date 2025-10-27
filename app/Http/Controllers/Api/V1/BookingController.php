<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Requests\Booking\UpdateBookingRequest;
use App\Http\Resources\Booking\BookingResource;
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
     *     security={{"sanctum": {}}},
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
        $this->authorize('viewAny', Booking::class);
        
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
        $this->authorize('create', Booking::class);
        
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
        
        $this->authorize('view', $booking);
        
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
        
        $this->authorize('update', $booking);
        
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
     * @param Request $request The HTTP request
     * @param int $id The booking ID
     * @return JsonResponse The cancellation response
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        $booking = $this->service->find($id);
        if (!$booking) {
            $this->notFound('Booking');
        }
        
        $this->authorize('update', $booking);
        
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
        $this->authorize('viewAny', Booking::class);
        
        $items = $this->service->myBookings($request)->through(fn ($model) => BookingResource::make($model));
        return $this->paginated($items, 'My bookings retrieved successfully');
    }
}
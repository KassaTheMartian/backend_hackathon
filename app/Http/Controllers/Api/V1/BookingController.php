<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Requests\Booking\UpdateBookingRequest;
use App\Http\Resources\Booking\BookingResource;
use App\Http\Resources\Booking\BookingCollection;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        private BookingService $bookingService
    ) {}

    /**
     * Display a listing of bookings.
     */
    public function index(Request $request): JsonResponse
    {
        $bookings = $this->bookingService->getBookings($request->all());
        $items = $bookings->through(fn($booking) => new BookingResource($booking));
        
        return $this->paginated($items);
    }

    /**
     * Store a newly created booking.
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        $booking = $this->bookingService->createBooking($request->validated());
        
        return $this->created(new BookingResource($booking), 'Booking created successfully. Confirmation email sent.');
    }

    /**
     * Display the specified booking.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $booking = $this->bookingService->getBookingById($id);
        
        if (!$booking) {
            $this->notFound('Booking');
        }
        
        $booking = $this->bookingService->getBookingWithDetails($booking);
        
        return $this->ok(new BookingResource($booking));
    }

    /**
     * Update the specified booking.
     */
    public function update(UpdateBookingRequest $request, int $id): JsonResponse
    {
        $updatedBooking = $this->bookingService->updateBooking($id, $request->validated());
        
        if (!$updatedBooking) {
            $this->notFound('Booking');
        }
        
        return $this->ok(new BookingResource($updatedBooking), 'Booking updated successfully');
    }

    /**
     * Cancel the specified booking.
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        $booking = $this->bookingService->cancelBooking($id, $request->cancellation_reason);
        
        if (!$booking) {
            $this->notFound('Booking');
        }
        
        return $this->ok(new BookingResource($booking), 'Booking cancelled successfully');
    }

    /**
     * Get user's bookings.
     */
    public function myBookings(Request $request): JsonResponse
    {
        $user = $request->user();
        $bookings = $this->bookingService->getUserBookings($user, $request->all());
        $items = $bookings->through(fn($booking) => new BookingResource($booking));
        
        return $this->paginated($items);
    }
}
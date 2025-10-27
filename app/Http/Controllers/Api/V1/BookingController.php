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
        
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => new BookingCollection($bookings),
            'error' => null,
            'meta' => [
                'page' => $bookings->currentPage(),
                'page_size' => $bookings->perPage(),
                'total_count' => $bookings->total(),
                'total_pages' => $bookings->lastPage(),
                'has_next_page' => $bookings->hasMorePages(),
                'has_previous_page' => $bookings->currentPage() > 1,
            ],
            'trace_id' => $request->header('X-Trace-ID'),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Store a newly created booking.
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        $booking = $this->bookingService->createBooking($request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully. Confirmation email sent.',
            'data' => new BookingResource($booking),
            'error' => null,
            'meta' => null,
            'trace_id' => $request->header('X-Trace-ID'),
            'timestamp' => now()->toISOString(),
        ], 201);
    }

    /**
     * Display the specified booking.
     */
    public function show(Request $request, Booking $booking): JsonResponse
    {
        $booking = $this->bookingService->getBookingWithDetails($booking);
        
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => new BookingResource($booking),
            'error' => null,
            'meta' => null,
            'trace_id' => $request->header('X-Trace-ID'),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Update the specified booking.
     */
    public function update(UpdateBookingRequest $request, Booking $booking): JsonResponse
    {
        $booking = $this->bookingService->updateBooking($booking, $request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'Booking updated successfully',
            'data' => new BookingResource($booking),
            'error' => null,
            'meta' => null,
            'trace_id' => $request->header('X-Trace-ID'),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Cancel the specified booking.
     */
    public function cancel(Request $request, Booking $booking): JsonResponse
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        $booking = $this->bookingService->cancelBooking($booking, $request->cancellation_reason);
        
        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully',
            'data' => new BookingResource($booking),
            'error' => null,
            'meta' => null,
            'trace_id' => $request->header('X-Trace-ID'),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get user's bookings.
     */
    public function myBookings(Request $request): JsonResponse
    {
        $user = $request->user();
        $bookings = $this->bookingService->getUserBookings($user, $request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => new BookingCollection($bookings),
            'error' => null,
            'meta' => [
                'page' => $bookings->currentPage(),
                'page_size' => $bookings->perPage(),
                'total_count' => $bookings->total(),
                'total_pages' => $bookings->lastPage(),
                'has_next_page' => $bookings->hasMorePages(),
                'has_previous_page' => $bookings->currentPage() > 1,
            ],
            'trace_id' => $request->header('X-Trace-ID'),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
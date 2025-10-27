<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Resources\Review\ReviewResource;
use App\Http\Resources\Review\ReviewCollection;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        private ReviewService $reviewService
    ) {}

    /**
     * Display a listing of reviews.
     */
    public function index(Request $request): JsonResponse
    {
        $reviews = $this->reviewService->getReviews($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => new ReviewCollection($reviews),
            'error' => null,
            'meta' => [
                'page' => $reviews->currentPage(),
                'page_size' => $reviews->perPage(),
                'total_count' => $reviews->total(),
                'total_pages' => $reviews->lastPage(),
                'has_next_page' => $reviews->hasMorePages(),
                'has_previous_page' => $reviews->currentPage() > 1,
            ],
            'trace_id' => $request->header('X-Trace-ID'),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Store a newly created review.
     */
    public function store(StoreReviewRequest $request): JsonResponse
    {
        $review = $this->reviewService->createReview($request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully. Waiting for approval.',
            'data' => new ReviewResource($review),
            'error' => null,
            'meta' => null,
            'trace_id' => $request->header('X-Trace-ID'),
            'timestamp' => now()->toISOString(),
        ], 201);
    }

    /**
     * Display the specified review.
     */
    public function show(Request $request, Review $review): JsonResponse
    {
        $review = $this->reviewService->getReviewWithDetails($review);
        
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => new ReviewResource($review),
            'error' => null,
            'meta' => null,
            'trace_id' => $request->header('X-Trace-ID'),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
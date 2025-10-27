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
        $items = $reviews->through(fn($review) => new ReviewResource($review));
        
        return $this->paginated($items);
    }

    /**
     * Store a newly created review.
     */
    public function store(StoreReviewRequest $request): JsonResponse
    {
        $review = $this->reviewService->createReview($request->validated());
        
        return $this->created(new ReviewResource($review), 'Review submitted successfully. Waiting for approval.');
    }

    /**
     * Display the specified review.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $review = $this->reviewService->getReviewById($id);
        
        if (!$review) {
            $this->notFound('Review');
        }
        
        $review = $this->reviewService->getReviewWithDetails($review);
        
        return $this->ok(new ReviewResource($review));
    }
}
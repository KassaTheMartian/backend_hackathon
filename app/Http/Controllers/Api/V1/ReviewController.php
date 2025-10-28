<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Review\ReviewResource;
use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Requests\Review\UpdateReviewRequest;
use App\Models\Review;
use App\Services\Contracts\ReviewServiceInterface;
use App\Data\Review\ReviewData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Create a new ReviewController instance.
     *
     * @param ReviewServiceInterface $service The review service
     */
    public function __construct(private readonly ReviewServiceInterface $service)
    {
        //
    }

    /**
     * @OA\Get(
     *     path="/api/v1/reviews",
     *     summary="List reviews",
     *     tags={"Reviews"},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="service_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="branch_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="rating", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Display a listing of reviews.
     *
     * @param Request $request The HTTP request
     * @return JsonResponse The paginated list of reviews
     */
    public function index(Request $request): JsonResponse
    {
        $items = $this->service->list($request)->through(fn ($model) => ReviewResource::make($model));
        return $this->paginated($items, 'Reviews retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/reviews",
     *     summary="Create review",
     *     tags={"Reviews"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"booking_id","rating","comment"},
     *             @OA\Property(property="booking_id", type="integer"),
     *             @OA\Property(property="rating", type="integer", minimum=1, maximum=5),
     *             @OA\Property(property="comment", type="string"),
     *             @OA\Property(property="images", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=422, description="Validation Error", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Store a newly created review.
     *
     * @param StoreReviewRequest $request The store review request
     * @return JsonResponse The created review response
     */
    public function store(StoreReviewRequest $request): JsonResponse
    {
        $review = $this->service->createFromBooking(
            $request->validated(),
            $request->user()->id
        );
        
        return $this->created(ReviewResource::make($review), 'Review submitted successfully. Waiting for approval.');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/reviews/{id}",
     *     summary="Get review by id",
     *     tags={"Reviews"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Display the specified review.
     *
     * @param int $id The review ID
     * @return JsonResponse The review response
     */
    public function show(int $id): JsonResponse
    {
        $review = $this->service->find($id);
        if (!$review) {
            $this->notFound('Review');
        }
        
        return $this->ok(ReviewResource::make($review), 'Review retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/reviews/pending",
     *     summary="List pending reviews (admin)",
     *     tags={"Reviews"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=403, description="Forbidden", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     */
    public function pending(Request $request): JsonResponse
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            return $this->forbidden('Admin only');
        }
        $items = Review::with(['user','service','staff','branch'])
            ->where('is_approved', false)
            ->latest('id')
            ->paginate((int)$request->query('per_page', 15))
            ->through(fn ($model) => ReviewResource::make($model));
        return $this->paginated($items, 'Pending reviews retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/reviews/{id}/approve",
     *     summary="Approve review (admin)",
     *     tags={"Reviews"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=403, description="Forbidden", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            return $this->forbidden('Admin only');
        }
        $review = $this->service->find($id);
        if (!$review) {
            $this->notFound('Review');
        }
        $approved = $this->service->approveReview($review);
        return $this->ok(ReviewResource::make($approved), 'Review approved');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/reviews/{id}/reject",
     *     summary="Reject review (admin)",
     *     tags={"Reviews"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(@OA\Property(property="reason", type="string"))
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=403, description="Forbidden", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            return $this->forbidden('Admin only');
        }
        $review = $this->service->find($id);
        if (!$review) {
            $this->notFound('Review');
        }
        $reason = (string)$request->input('reason', '');
        $rejected = $this->service->rejectReview($review, $reason);
        return $this->ok(ReviewResource::make($rejected), 'Review rejected');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/reviews/{id}/respond",
     *     summary="Respond to a review (admin)",
     *     tags={"Reviews"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"admin_response"},
     *             @OA\Property(property="admin_response", type="string", maxLength=1000)
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=403, description="Forbidden", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     */
    public function respond(Request $request, int $id): JsonResponse
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            return $this->forbidden('Admin only');
        }
        $review = $this->service->find($id);
        if (!$review) {
            $this->notFound('Review');
        }
        $message = trim((string)$request->input('admin_response', ''));
        if ($message === '') {
            return \App\Http\Responses\ApiResponse::validationError([
                'admin_response' => ['admin_response is required']
            ]);
        }
        $review->update([
            'admin_response' => $message,
            'responded_at' => now(),
            'responded_by' => $request->user()->id,
        ]);
        return $this->ok(ReviewResource::make($review->fresh()), 'Response saved');
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Review\ReviewResource;
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
        $this->authorize('viewAny', Review::class);
        
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
     * @param Request $request The store review request
     * @return JsonResponse The created review response
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Review::class);
        
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
            'images' => 'nullable|array',
            'images.*' => 'string|max:255'
        ]);
        
        $dto = ReviewData::from($validated);
        $review = $this->service->create($dto);
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
        
        $this->authorize('view', $review);
        
        return $this->ok(ReviewResource::make($review), 'Review retrieved successfully');
    }
}
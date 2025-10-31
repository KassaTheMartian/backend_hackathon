<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Branch\AvailableSlotsRequest;
use App\Http\Resources\Branch\BranchResource;
use App\Services\Contracts\BranchServiceInterface;
use App\Traits\HasLocalization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BranchController extends Controller
{
    use HasLocalization;
    /**
     * Create a new BranchController instance.
     *
     * @param BranchServiceInterface $service The branch service
     */
    public function __construct(private readonly BranchServiceInterface $service)
    {
        //
    }

    /**
     * @OA\Get(
     *     path="/api/v1/branches",
     *     summary="List branches",
     *     tags={"Branches"},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Display a listing of branches.
     *
     * @param Request $request The HTTP request
     * @return JsonResponse The paginated list of branches
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);
        $cacheKey = sprintf('branches.index:%s:%s', app()->getLocale(), md5(json_encode($request->query())));
        $ttlSeconds = 300; // 5 minutes

        $paginator = Cache::remember($cacheKey, $ttlSeconds, function () use ($request) {
            return $this->service->list($request);
        });

        $items = $paginator->through(fn ($model) => BranchResource::make($model));
        return $this->paginated($items, __('branches.list_retrieved'));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/branches/{id}",
     *     summary="Get branch by id or slug",
     *     tags={"Branches"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Display the specified branch by id or slug.
     *
     * @param string $id The branch ID or slug
     * @return JsonResponse The branch response
     */
    public function show(string $id): JsonResponse
    {
        $cacheKey = sprintf('branches.show:%s:%s', app()->getLocale(), $id);
        $ttlSeconds = 900; // 15 minutes

        // Cho phép $id là số hoặc slug
        $branch = Cache::remember($cacheKey, $ttlSeconds, function () use ($id) {
            return is_numeric($id)
            ? $this->service->find((int)$id)
            : $this->service->findBySlug($id);
        });
        if (!$branch) {
            return $this->notFound(__('branches.not_found'));
        }
        $branch->load(['services']);
        return $this->ok(BranchResource::make($branch), __('branches.retrieved'));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/branches/{id}/available-slots",
     *     summary="Get available time slots for a branch",
     *     tags={"Branches"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="date", in="query", required=true, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="service_id", in="query", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="staff_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=422, description="Validation Error", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Get available time slots for a branch.
     *
     * @param AvailableSlotsRequest $request The HTTP request
     * @param int $id The branch ID
     * @return JsonResponse The available slots response
     */
    public function availableSlots(AvailableSlotsRequest $request, int $id): JsonResponse
    {
        try {
            $slots = $this->service->getAvailableSlots(
                $id,
                $request->date,
                $request->service_id,
                $request->staff_id
            );
            
            return $this->ok($slots, __('branches.available_slots_retrieved'));
        } catch (\Exception $e) {
            return $this->notFound($e->getMessage());
        }
    }
}

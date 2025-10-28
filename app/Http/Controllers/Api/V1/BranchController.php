<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Branch\StoreBranchRequest;
use App\Http\Requests\Branch\UpdateBranchRequest;
use App\Http\Requests\Branch\AvailableSlotsRequest;
use App\Http\Resources\Branch\BranchResource;
use App\Models\Branch;
use App\Services\Contracts\BranchServiceInterface;
use App\Data\Branch\BranchData;
use App\Data\Branch\UpdateBranchData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BranchController extends Controller
{
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
        $items = $this->service->list($request)->through(fn ($model) => BranchResource::make($model));
        return $this->paginated($items, 'Branches retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/branches/{id}",
     *     summary="Get branch by id",
     *     tags={"Branches"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Display the specified branch.
     *
     * @param int $id The branch ID
     * @return JsonResponse The branch response
     */
    public function show(int $id): JsonResponse
    {
        $branch = $this->service->find($id);
        if (!$branch) {
            $this->notFound('Branch');
        }
        
        return $this->ok(BranchResource::make($branch), 'Branch retrieved successfully');
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
        $slots = $this->service->getAvailableSlots(
            $id,
            $request->date,
            $request->service_id,
            $request->staff_id
        );
        
        return $this->ok($slots, 'Available slots retrieved successfully');
    }
}

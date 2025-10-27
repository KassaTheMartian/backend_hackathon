<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Branch\StoreBranchRequest;
use App\Http\Requests\Branch\UpdateBranchRequest;
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
     * @OA\Post(
     *     path="/api/v1/branches",
     *     summary="Create branch",
     *     tags={"Branches"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","address","phone","email","latitude","longitude","opening_hours"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="image", type="string"),
     *             @OA\Property(property="latitude", type="number"),
     *             @OA\Property(property="longitude", type="number"),
     *             @OA\Property(property="opening_hours", type="string"),
     *             @OA\Property(property="is_active", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=422, description="Validation Error", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Store a newly created branch.
     *
     * @param StoreBranchRequest $request The store branch request
     * @return JsonResponse The created branch response
     */
    public function store(StoreBranchRequest $request): JsonResponse
    {
        $this->authorize('create', Branch::class);
        
        $dto = BranchData::from($request->validated());
        $branch = $this->service->create($dto);
        return $this->created(BranchResource::make($branch), 'Branch created successfully');
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
     * @OA\Put(
     *     path="/api/v1/branches/{id}",
     *     summary="Update branch",
     *     tags={"Branches"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="image", type="string"),
     *             @OA\Property(property="latitude", type="number"),
     *             @OA\Property(property="longitude", type="number"),
     *             @OA\Property(property="opening_hours", type="string"),
     *             @OA\Property(property="is_active", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=422, description="Validation Error", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Update the specified branch.
     *
     * @param UpdateBranchRequest $request The update branch request
     * @param int $id The branch ID
     * @return JsonResponse The updated branch response
     */
    public function update(UpdateBranchRequest $request, int $id): JsonResponse
    {
        $branch = $this->service->find($id);
        if (!$branch) {
            $this->notFound('Branch');
        }
        
        $this->authorize('update', $branch);
        
        $dto = UpdateBranchData::from($request->validated());
        $branch = $this->service->update($id, $dto);
        return $this->ok(BranchResource::make($branch), 'Branch updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/branches/{id}",
     *     summary="Delete branch",
     *     tags={"Branches"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="No Content"),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Remove the specified branch from storage.
     *
     * @param int $id The branch ID
     * @return JsonResponse The deletion response
     */
    public function destroy(int $id): JsonResponse
    {
        $branch = $this->service->find($id);
        if (!$branch) {
            $this->notFound('Branch');
        }
        
        $this->authorize('delete', $branch);
        
        $deleted = $this->service->delete($id);
        return $this->noContent('Branch deleted successfully');
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
     * @param Request $request The HTTP request
     * @param int $id The branch ID
     * @return JsonResponse The available slots response
     */
    public function availableSlots(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'date' => 'required|date',
            'service_id' => 'required|integer|exists:services,id',
            'staff_id' => 'nullable|integer|exists:staff,id',
        ]);
        
        $slots = $this->service->getAvailableSlots(
            $id,
            $request->date,
            $request->service_id,
            $request->staff_id
        );
        
        return $this->ok($slots, 'Available slots retrieved successfully');
    }
}
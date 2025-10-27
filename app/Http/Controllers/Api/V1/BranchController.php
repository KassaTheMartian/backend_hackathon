<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Branch\StoreBranchRequest;
use App\Http\Requests\Branch\UpdateBranchRequest;
use App\Http\Resources\Branch\BranchCollection;
use App\Http\Resources\Branch\BranchResource;
use App\Services\BranchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function __construct(
        private BranchService $branchService
    ) {}

    /**
     * Display a listing of branches.
     */
    public function index(Request $request): JsonResponse
    {
        $branches = $this->branchService->getBranches($request->all());
        
        return $this->ok(new BranchCollection($branches));
    }

    /**
     * Store a newly created branch.
     */
    public function store(StoreBranchRequest $request): JsonResponse
    {
        $branch = $this->branchService->createBranch($request->validated());
        
        return $this->created(new BranchResource($branch), 'Branch created successfully');
    }

    /**
     * Display the specified branch.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $branch = $this->branchService->getBranchById($id);
        
        if (!$branch) {
            $this->notFound('Branch');
        }
        
        return $this->ok(new BranchResource($branch));
    }

    /**
     * Update the specified branch.
     */
    public function update(UpdateBranchRequest $request, int $id): JsonResponse
    {
        $updatedBranch = $this->branchService->updateBranch($id, $request->validated());
        
        if (!$updatedBranch) {
            $this->notFound('Branch');
        }
        
        return $this->ok(new BranchResource($updatedBranch), 'Branch updated successfully');
    }

    /**
     * Remove the specified branch.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $deleted = $this->branchService->deleteBranch($id);
        
        if (!$deleted) {
            $this->notFound('Branch');
        }
        
        return $this->ok(null, 'Branch deleted successfully');
    }

    /**
     * Get available time slots for a branch.
     */
    public function availableSlots(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'date' => 'required|date',
            'service_id' => 'required|integer|exists:services,id',
            'staff_id' => 'nullable|integer|exists:staff,id',
        ]);
        
        $slots = $this->branchService->getAvailableSlots(
            $id,
            $request->date,
            $request->service_id,
            $request->staff_id
        );
        
        return $this->ok($slots);
    }
}
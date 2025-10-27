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
        
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => new BranchCollection($branches),
            'error' => null,
            'meta' => null,
            'trace_id' => $request->header('X-Trace-ID'),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Store a newly created branch.
     */
    public function store(StoreBranchRequest $request): JsonResponse
    {
        $branch = $this->branchService->createBranch($request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'Branch created successfully',
            'data' => new BranchResource($branch),
            'error' => null,
            'meta' => null,
            'trace_id' => $request->header('X-Trace-ID'),
            'timestamp' => now()->toISOString(),
        ], 201);
    }

    /**
     * Display the specified branch.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $branch = $this->branchService->getBranchById($id);
        
        if (!$branch) {
            return response()->json([
                'success' => false,
                'message' => 'Branch not found',
                'data' => null,
                'error' => [
                    'type' => 'NotFoundError',
                    'code' => 'NOT_FOUND',
                    'details' => []
                ],
                'meta' => null,
                'trace_id' => $request->header('X-Trace-ID'),
                'timestamp' => now()->toISOString(),
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => new BranchResource($branch),
            'error' => null,
            'meta' => null,
            'trace_id' => $request->header('X-Trace-ID'),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Update the specified branch.
     */
    public function update(UpdateBranchRequest $request, int $id): JsonResponse
    {
        $branch = $this->branchService->getBranchById($id);
        
        if (!$branch) {
            return response()->json([
                'success' => false,
                'message' => 'Branch not found',
                'data' => null,
                'error' => [
                    'type' => 'NotFoundError',
                    'code' => 'NOT_FOUND',
                    'details' => []
                ],
                'meta' => null,
                'trace_id' => $request->header('X-Trace-ID'),
                'timestamp' => now()->toISOString(),
            ], 404);
        }
        
        $updatedBranch = $this->branchService->updateBranch($branch, $request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'Branch updated successfully',
            'data' => new BranchResource($updatedBranch),
            'error' => null,
            'meta' => null,
            'trace_id' => $request->header('X-Trace-ID'),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Remove the specified branch.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $branch = $this->branchService->getBranchById($id);
        
        if (!$branch) {
            return response()->json([
                'success' => false,
                'message' => 'Branch not found',
                'data' => null,
                'error' => [
                    'type' => 'NotFoundError',
                    'code' => 'NOT_FOUND',
                    'details' => []
                ],
                'meta' => null,
                'trace_id' => $request->header('X-Trace-ID'),
                'timestamp' => now()->toISOString(),
            ], 404);
        }
        
        $this->branchService->deleteBranch($branch);
        
        return response()->json([
            'success' => true,
            'message' => 'Branch deleted successfully',
            'data' => null,
            'error' => null,
            'meta' => null,
            'trace_id' => $request->header('X-Trace-ID'),
            'timestamp' => now()->toISOString(),
        ]);
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
        
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => $slots,
            'error' => null,
            'meta' => null,
            'trace_id' => $request->header('X-Trace-ID'),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
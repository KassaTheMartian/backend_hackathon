<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Branch\BranchResource;
use App\Http\Resources\Branch\BranchCollection;
use App\Models\Branch;
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
     * Display the specified branch.
     */
    public function show(Request $request, Branch $branch): JsonResponse
    {
        $branch = $this->branchService->getBranchWithDetails($branch, $request->get('locale', 'vi'));
        
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
     * Get available time slots for a branch.
     */
    public function availableSlots(Request $request, Branch $branch): JsonResponse
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'service_id' => 'required|exists:services,id',
            'staff_id' => 'nullable|exists:staff,id',
        ]);

        $slots = $this->branchService->getAvailableSlots(
            $branch,
            $request->date,
            $request->service_id,
            $request->staff_id
        );
        
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => [
                'date' => $request->date,
                'available_slots' => $slots,
            ],
            'error' => null,
            'meta' => null,
            'trace_id' => $request->header('X-Trace-ID'),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
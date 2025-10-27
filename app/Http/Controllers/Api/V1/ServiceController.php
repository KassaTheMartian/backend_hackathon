<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\StoreServiceRequest;
use App\Http\Requests\Service\UpdateServiceRequest;
use App\Http\Resources\Service\ServiceResource;
use App\Http\Resources\Service\ServiceCollection;
use App\Models\Service;
use App\Services\ServiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function __construct(
        private ServiceService $serviceService
    ) {}

    /**
     * Display a listing of services.
     */
    public function index(Request $request): JsonResponse
    {
        $services = $this->serviceService->getServices($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => new ServiceCollection($services),
            'error' => null,
            'meta' => [
                'page' => $services->currentPage(),
                'page_size' => $services->perPage(),
                'total_count' => $services->total(),
                'total_pages' => $services->lastPage(),
                'has_next_page' => $services->hasMorePages(),
                'has_previous_page' => $services->currentPage() > 1,
            ],
            'trace_id' => $request->header('X-Trace-ID'),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Store a newly created service.
     */
    public function store(StoreServiceRequest $request): JsonResponse
    {
        $service = $this->serviceService->createService($request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'Service created successfully',
            'data' => new ServiceResource($service),
            'error' => null,
            'meta' => null,
            'trace_id' => $request->header('X-Trace-ID'),
            'timestamp' => now()->toISOString(),
        ], 201);
    }

    /**
     * Display the specified service.
     */
    public function show(Request $request, Service $service): JsonResponse
    {
        $service = $this->serviceService->getServiceWithDetails($service, $request->get('locale', 'vi'));
        
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => new ServiceResource($service),
            'error' => null,
            'meta' => null,
            'trace_id' => $request->header('X-Trace-ID'),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Update the specified service.
     */
    public function update(UpdateServiceRequest $request, Service $service): JsonResponse
    {
        $service = $this->serviceService->updateService($service, $request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully',
            'data' => new ServiceResource($service),
            'error' => null,
            'meta' => null,
            'trace_id' => $request->header('X-Trace-ID'),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Remove the specified service.
     */
    public function destroy(Service $service): JsonResponse
    {
        $this->serviceService->deleteService($service);
        
        return response()->json([
            'success' => true,
            'message' => 'Service deleted successfully',
            'data' => null,
            'error' => null,
            'meta' => null,
            'trace_id' => request()->header('X-Trace-ID'),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get service categories.
     */
    public function categories(Request $request): JsonResponse
    {
        $categories = $this->serviceService->getCategories($request->get('locale', 'vi'));
        
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => $categories,
            'error' => null,
            'meta' => null,
            'trace_id' => $request->header('X-Trace-ID'),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
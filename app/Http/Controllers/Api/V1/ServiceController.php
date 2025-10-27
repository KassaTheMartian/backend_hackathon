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
        $items = $services->through(fn($service) => new ServiceResource($service));
        
        return $this->paginated($items);
    }

    /**
     * Store a newly created service.
     */
    public function store(StoreServiceRequest $request): JsonResponse
    {
        $service = $this->serviceService->createService($request->validated());
        
        return $this->created(new ServiceResource($service), 'Service created successfully');
    }

    /**
     * Display the specified service.
     */
    public function show(Request $request, Service $service): JsonResponse
    {
        $service = $this->serviceService->getServiceWithDetails($service, $request->get('locale', 'vi'));
        
        return $this->ok(new ServiceResource($service));
    }

    /**
     * Update the specified service.
     */
    public function update(UpdateServiceRequest $request, Service $service): JsonResponse
    {
        $service = $this->serviceService->updateService($service, $request->validated());
        
        return $this->ok(new ServiceResource($service), 'Service updated successfully');
    }

    /**
     * Remove the specified service.
     */
    public function destroy(Service $service): JsonResponse
    {
        $this->serviceService->deleteService($service);
        
        return $this->ok(null, 'Service deleted successfully');
    }

    /**
     * Get service categories.
     */
    public function categories(Request $request): JsonResponse
    {
        $categories = $this->serviceService->getCategories($request->get('locale', 'vi'));
        
        return $this->ok($categories);
    }
}
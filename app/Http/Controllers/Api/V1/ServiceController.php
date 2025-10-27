<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\StoreServiceRequest;
use App\Http\Requests\Service\UpdateServiceRequest;
use App\Http\Resources\Service\ServiceResource;
use App\Models\Service;
use App\Services\Contracts\ServiceServiceInterface;
use App\Data\Service\ServiceData;
use App\Data\Service\UpdateServiceData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Create a new ServiceController instance.
     *
     * @param ServiceServiceInterface $service The service service
     */
    public function __construct(private readonly ServiceServiceInterface $service)
    {
        //
    }

    /**
     * @OA\Get(
     *     path="/api/v1/services",
     *     summary="List services",
     *     tags={"Services"},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="locale", in="query", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Display a listing of services.
     *
     * @param Request $request The HTTP request
     * @return JsonResponse The paginated list of services
     */
    public function index(Request $request): JsonResponse
    {
        $items = $this->service->list($request)->through(fn ($model) => ServiceResource::make($model));
        return $this->paginated($items, 'Services retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/services",
     *     summary="Create service",
     *     tags={"Services"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","price","duration","category_id"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="duration", type="integer"),
     *             @OA\Property(property="category_id", type="integer"),
     *             @OA\Property(property="is_active", type="boolean"),
     *             @OA\Property(property="image", type="string"),
     *             @OA\Property(property="features", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=422, description="Validation Error", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Store a newly created service.
     *
     * @param StoreServiceRequest $request The store service request
     * @return JsonResponse The created service response
     */
    public function store(StoreServiceRequest $request): JsonResponse
    {
        $this->authorize('create', Service::class);
        
        $dto = ServiceData::from($request->validated());
        $service = $this->service->create($dto);
        return $this->created(ServiceResource::make($service), 'Service created successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/services/{id}",
     *     summary="Get service by id",
     *     tags={"Services"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="locale", in="query", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Display the specified service.
     *
     * @param int $id The service ID
     * @return JsonResponse The service response
     */
    public function show(int $id): JsonResponse
    {
        $service = $this->service->find($id);
        if (!$service) {
            $this->notFound('Service');
        }
        
        return $this->ok(ServiceResource::make($service), 'Service retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/services/{id}",
     *     summary="Update service",
     *     tags={"Services"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="duration", type="integer"),
     *             @OA\Property(property="category_id", type="integer"),
     *             @OA\Property(property="is_active", type="boolean"),
     *             @OA\Property(property="image", type="string"),
     *             @OA\Property(property="features", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=422, description="Validation Error", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Update the specified service.
     *
     * @param UpdateServiceRequest $request The update service request
     * @param int $id The service ID
     * @return JsonResponse The updated service response
     */
    public function update(UpdateServiceRequest $request, int $id): JsonResponse
    {
        $service = $this->service->find($id);
        if (!$service) {
            $this->notFound('Service');
        }
        
        $this->authorize('update', $service);
        
        $dto = UpdateServiceData::from($request->validated());
        $service = $this->service->update($id, $dto);
        return $this->ok(ServiceResource::make($service), 'Service updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/services/{id}",
     *     summary="Delete service",
     *     tags={"Services"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="No Content"),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Remove the specified service from storage.
     *
     * @param int $id The service ID
     * @return JsonResponse The deletion response
     */
    public function destroy(int $id): JsonResponse
    {
        $service = $this->service->find($id);
        if (!$service) {
            $this->notFound('Service');
        }
        
        $this->authorize('delete', $service);
        
        $deleted = $this->service->delete($id);
        return $this->noContent('Service deleted successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/service-categories",
     *     summary="Get service categories",
     *     tags={"Services"},
     *     @OA\Parameter(name="locale", in="query", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Get service categories.
     *
     * @param Request $request The HTTP request
     * @return JsonResponse The categories response
     */
    public function categories(Request $request): JsonResponse
    {
        $categories = $this->service->categories($request->get('locale', 'vi'));
        return $this->ok($categories, 'Service categories retrieved successfully');
    }
}
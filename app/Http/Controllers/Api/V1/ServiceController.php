<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\StoreServiceRequest;
use App\Http\Requests\Service\UpdateServiceRequest;
use App\Http\Resources\Service\ServiceResource;
use App\Services\Contracts\ServiceServiceInterface;
use App\Data\Service\ServiceData;
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

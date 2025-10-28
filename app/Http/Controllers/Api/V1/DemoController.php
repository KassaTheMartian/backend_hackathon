<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Demo\StoreDemoRequest;
use App\Http\Requests\Demo\UpdateDemoRequest;
use App\Http\Resources\Demo\DemoResource;
use App\Models\Demo;
use App\Services\Contracts\DemoServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Data\Demo\DemoData;

class DemoController extends Controller
{
    /**
     * Create a new DemoController instance.
     *
     * @param DemoServiceInterface $service The demo service
     */
    public function __construct(private readonly DemoServiceInterface $service)
    {
        //
    }

    /**
     * @OA\Get(
     *     path="/api/v1/demos",
     *     summary="List demos",
     *     tags={"Demos"},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Display a listing of demos.
     *
     * @param Request $request The HTTP request
     * @return JsonResponse The paginated list of demos
     */
    public function index(Request $request): JsonResponse
    {
        
        $items = $this->service->list($request)->through(fn ($model) => DemoResource::make($model));
        return $this->paginated($items, 'Demos retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/demos",
     *     summary="Create demo",
     *     tags={"Demos"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="is_active", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=422, description="Validation Error", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Store a newly created demo.
     *
     * @param StoreDemoRequest $request The store demo request
     * @return JsonResponse The created demo response
     */
    public function store(StoreDemoRequest $request): JsonResponse
    {
        
        $dto = DemoData::from($request->validated());
        $demo = $this->service->create($dto);
        return $this->created(DemoResource::make(parameters: $demo), 'Demo created successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/demos/{id}",
     *     summary="Get demo by id",
     *     tags={"Demos"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Display the specified demo.
     *
     * @param int $id The demo ID
     * @return JsonResponse The demo response
     */
    public function show(int $id): JsonResponse
    {
        $demo = $this->service->find($id);
        if (!$demo) {
            $this->notFound('Demo');
        }
        
        
        return $this->ok(DemoResource::make($demo), 'Demo retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/demos/{id}",
     *     summary="Update demo",
     *     tags={"Demos"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="is_active", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=422, description="Validation Error", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Update the specified demo.
     *
     * @param UpdateDemoRequest $request The update demo request
     * @param int $id The demo ID
     * @return JsonResponse The updated demo response
     */
    public function update(UpdateDemoRequest $request, int $id): JsonResponse
    {
        $demo = $this->service->find($id);
        if (!$demo) {
            $this->notFound('Demo');
        }
        
        
        $dto = DemoData::from($request->validated());
        $demo = $this->service->update($id, $dto);
        return $this->ok(DemoResource::make($demo), 'Demo updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/demos/{id}",
     *     summary="Delete demo",
     *     tags={"Demos"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="No Content"),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Remove the specified demo from storage.
     *
     * @param int $id The demo ID
     * @return JsonResponse The deletion response
     */
    public function destroy(int $id): JsonResponse
    {
        $demo = $this->service->find($id);
        if (!$demo) {
            $this->notFound('Demo');
        }
        
        
        $deleted = $this->service->delete($id);
        return $this->noContent('Demo deleted successfully');
    }
}



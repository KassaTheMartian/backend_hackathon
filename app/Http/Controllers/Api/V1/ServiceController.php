<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Service\ServiceResource;
use App\Services\Contracts\ServiceServiceInterface;
use App\Traits\HasLocalization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ServiceController extends Controller
{
    use HasLocalization;
    
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
        $request->validate([
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);
        $cacheKey = sprintf('services.index:%s:%s', app()->getLocale(), md5(json_encode($request->query())));
        $ttlSeconds = 300; // 5 minutes

        $paginator = Cache::remember($cacheKey, $ttlSeconds, function () use ($request) {
            return $this->service->list($request);
        });

        $items = $paginator->through(fn ($model) => ServiceResource::make($model));
        return $this->paginated($items, __('services.list_retrieved'));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/services/{id}",
     *     summary="Get service by id or slug",
     *     tags={"Services"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Display the specified service by id or slug.
     *
     * @param string $id The service ID or slug
     * @return JsonResponse The service response
     */
    public function show(string $id): JsonResponse
    {
        $cacheKey = sprintf('services.show:%s:%s', app()->getLocale(), $id);
        $ttlSeconds = 900; // 15 minutes

        $service = Cache::remember($cacheKey, $ttlSeconds, function () use ($id) {
            return is_numeric($id)
            ? $this->service->find((int)$id)
            : $this->service->findBySlug($id);
        });
        if (!$service) {
            return $this->notFound(__('services.not_found'));
        }
        return $this->ok(ServiceResource::make($service), __('services.retrieved'));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/service-categories",
     *     summary="Get service categories",
     *     tags={"Services"},
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
        $locale = $this->getLocale($request);
        $cacheKey = sprintf('services.categories:%s', $locale);
        $ttlSeconds = 3600; // 60 minutes

        $categories = Cache::remember($cacheKey, $ttlSeconds, fn () => $this->service->categories($locale));
        return $this->ok($categories, __('services.categories_retrieved'));
    }
}

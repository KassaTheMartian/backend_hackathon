## Controller conventions (based on `App\Http\Controllers\Api\V1\DemoController`)

This document defines the coding conventions and a ready-to-use template for building RESTful Controllers in this codebase. It follows exactly the style and logic of `DemoController` to keep all generated controllers consistent.

### Principles
- **Constructor DI**: Inject the corresponding Service via constructor: `private readonly {{Entity}}ServiceInterface $service`.
- **Validation via FormRequest**: Use `Store{{Entity}}Request` and `Update{{Entity}}Request`.
- **DTO layer**: Convert validated input into `{{Entity}}Data::from($request->validated())`.
- **Resource layer**: Wrap outputs with `{{Entity}}Resource::make(...)`. For lists, map using `->through(...)`.
- **HTTP response helpers**: Use `ok`, `created`, `noContent`, `paginated`, `notFound('Entity')` from base `Controller`.
- **Swagger/OpenAPI annotations**: Provide `@OA\Get`, `@OA\Post`, `@OA\Put`, `@OA\Delete` with parameters, bodies, and responses.
- **Typing**: Explicit return type `JsonResponse`, typed params like `int $id`, `Request $request`.
- **Flow & errors**: For `show`, `update`, `destroy`, check existence first; call `notFound('Entity')` if missing, then proceed.

### Required pieces per Entity
- Interface: `App\Services\Contracts\{{Entity}}ServiceInterface`
- DTO: `App\Data\{{Entity}}\{{Entity}}Data`
- Resource: `App\Http\Resources\{{Entity}}\{{Entity}}Resource`
- FormRequests: `App\Http\Requests\{{Entity}}\Store{{Entity}}Request`, `Update{{Entity}}Request`

### Route base and tag
- `{{routeBase}}`: e.g., `/api/v1/demos`
- `{{tag}}`: e.g., `Demos`

### Controller template
Replace placeholders `{{Entity}}`, `{{entity}}`, `{{routeBase}}`, `{{tag}}` accordingly.

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\{{Entity}}\Store{{Entity}}Request;
use App\Http\Requests\{{Entity}}\Update{{Entity}}Request;
use App\Http\Resources\{{Entity}}\{{Entity}}Resource;
use App\Services\Contracts\{{Entity}}ServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Data\{{Entity}}\{{Entity}}Data;

class {{Entity}}Controller extends Controller
{
    /**
     * Create a new {{Entity}}Controller instance.
     *
     * @param {{Entity}}ServiceInterface $service The {{entity}} service
     */
    public function __construct(private readonly {{Entity}}ServiceInterface $service)
    {
        //
    }

    /**
     * @OA\Get(
     *     path="{{routeBase}}",
     *     summary="List {{entity}}",
     *     tags={"{{tag}}"},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     *
     * Display a listing of {{entity}}.
     *
     * @param Request $request The HTTP request
     * @return JsonResponse The paginated list of {{entity}}
     */
    public function index(Request $request): JsonResponse
    {
        $items = $this->service
            ->list($request)
            ->through(fn ($model) => {{Entity}}Resource::make($model));

        return $this->paginated($items, '{{Entity}}s retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="{{routeBase}}",
     *     summary="Create {{entity}}",
     *     tags={"{{tag}}"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="is_active", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=422, description="Validation Error", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     *
     * Store a newly created {{entity}}.
     *
     * @param Store{{Entity}}Request $request The store {{entity}} request
     * @return JsonResponse The created {{entity}} response
     */
    public function store(Store{{Entity}}Request $request): JsonResponse
    {
        $dto = {{Entity}}Data::from($request->validated());
        $created = $this->service->create($dto);

        return $this->created({{Entity}}Resource::make($created), '{{Entity}} created successfully');
    }

    /**
     * @OA\Get(
     *     path="{{routeBase}}/{id}",
     *     summary="Get {{entity}} by id",
     *     tags={"{{tag}}"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     *
     * Display the specified {{entity}}.
     *
     * @param int $id The {{entity}} ID
     * @return JsonResponse The {{entity}} response
     */
    public function show(int $id): JsonResponse
    {
        $item = $this->service->find($id);
        if (!$item) {
            $this->notFound('{{Entity}}');
        }

        return $this->ok({{Entity}}Resource::make($item), '{{Entity}} retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="{{routeBase}}/{id}",
     *     summary="Update {{entity}}",
     *     tags={"{{tag}}"},
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
     * Update the specified {{entity}}.
     *
     * @param Update{{Entity}}Request $request The update {{entity}} request
     * @param int $id The {{entity}} ID
     * @return JsonResponse The updated {{entity}} response
     */
    public function update(Update{{Entity}}Request $request, int $id): JsonResponse
    {
        $existing = $this->service->find($id);
        if (!$existing) {
            $this->notFound('{{Entity}}');
        }

        $dto = {{Entity}}Data::from($request->validated());
        $updated = $this->service->update($id, $dto);

        return $this->ok({{Entity}}Resource::make($updated), '{{Entity}} updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="{{routeBase}}/{id}",
     *     summary="Delete {{entity}}",
     *     tags={"{{tag}}"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="No Content"),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     *
     * Remove the specified {{entity}} from storage.
     *
     * @param int $id The {{entity}} ID
     * @return JsonResponse The deletion response
     */
    public function destroy(int $id): JsonResponse
    {
        $existing = $this->service->find($id);
        if (!$existing) {
            $this->notFound('{{Entity}}');
        }

        $this->service->delete($id);

        return $this->noContent('{{Entity}} deleted successfully');
    }
}
```

### Notes
- Keep property and method order as shown for readability.
- Do not embed business logic in controllers; delegate to the Service.
- Swagger example fields are illustrative; align them with your FormRequest rules.
- The `notFound('Entity')` helper should stop execution (as in `DemoController`).



## Backend API Coding Standard (Laravel 12)

This document describes how to design and implement an API endpoint end-to-end in this codebase. It covers conventions, folder structure, validation, DTOs, models, migrations, repositories, services, controllers, resources, envelopes, error handling, caching, auth, i18n, observability, performance, testing, and OpenAPI annotations.

The goal is to keep controllers thin, services cohesive, repositories clear, validations strong, resources consistent, and responses uniform with traceability.

---

### Guiding Principles
- Single responsibility per layer: Controller (HTTP), Service (business), Repository (persistence).
- Validation first: Never process invalid inputs.
- Typed boundaries: Use DTOs (spatie/laravel-data) to shape request/response boundaries.
- Consistent response envelope: Use `ApiResponse` helpers.
- Security by default: Auth, authorization, input hardening, logging hygiene.
- Observability: Trace IDs, structured logs, meaningful messages.
- Performance: Pagination, caching, query optimization, N+1 avoidance.
- Internationalization: Locale-aware messages and data when applicable.
- Documentation: OpenAPI annotations close to code; living API docs.
- Testing: Feature tests for endpoints; unit tests for business logic.

---

## Folder and Naming Conventions

- Controllers: `app/Http/Controllers/Api/V1/<Domain>Controller.php`
- Requests (validation): `app/Http/Requests/<Domain>/<Action>Request.php`
- Resources (transformers): `app/Http/Resources/<Domain>/<Resource>Resource.php`
- Responses (envelope): `app/Http/Responses/ApiResponse.php` (already provided)
- Services: `app/Services/<Domain>Service.php`
- Service Interfaces: `app/Services/Contracts/<Domain>ServiceInterface.php`
- Repositories: `app/Repositories/Eloquent/<Entity>Repository.php`
- Repository Interfaces: `app/Repositories/Contracts/<Entity>RepositoryInterface.php`
- Models: `app/Models/<Entity>.php`
- DTOs: `app/Data/<Domain>/<Name>Data.php`
- Middleware: `app/Http/Middleware`
- Policies: `app/Policies`
- Routes: `routes/api.php`
- Config: `config/<name>.php`
- Migrations: `database/migrations`
- Seeders: `database/seeders`
- Factories: `database/factories`
- Tests: `tests/Feature`, `tests/Unit`

Naming
- Controllers: `<Entity>Controller` with verbs: `index`, `show`, `store`, `update`, `destroy` or domain-specific actions.
- Requests: `<Action>Request` (e.g., `CreateProductRequest`).
- Resources: `<Entity>Resource` (single) and `<Entity>Collection` (rare; prefer Resource::collection).
- Services: `<Domain>Service`; interfaces: `<Domain>ServiceInterface`.
- Repositories: `<Entity>Repository`; interfaces: `<Entity>RepositoryInterface`.
- DTOs: `<Action>Data` or `<Entity>Data`.

---

## End-to-End Example: Product API

We will implement a simple Product resource with endpoints:
- GET /api/v1/products (list)
- GET /api/v1/products/{id} (detail)
- POST /api/v1/products (create)
- PUT /api/v1/products/{id} (update)
- DELETE /api/v1/products/{id} (delete)

The example demonstrates validation, DTOs, model, migration, repository, service, controller, resource, caching, envelope responses, and tests.

Note: Code shown is illustrative; adapt fields to your domain.

---

### 1) Database Migration

Create a migration for `products` with typical fields.

```php
// database/migrations/2025_01_01_000000_create_products_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('price'); // store in smallest currency unit
            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['is_active', 'price']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
```

Migration rules
- Use unsigned integers for money (smallest currency unit) or decimal with precision if needed.
- Add indexes for common filters/sorts.
- Use `json` for flexible metadata when justified.

---

### 2) Eloquent Model

```php
// app/Models/Product.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'price', 'is_active', 'meta',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'meta' => 'array',
    ];
}
```

Model rules
- Keep `$fillable` explicit.
- Use `$casts` for booleans/arrays/datetimes.

---

### 3) Repository Interface and Implementation

```php
// app/Repositories/Contracts/ProductRepositoryInterface.php
namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function paginate(array $filters = [], int $page = 1, int $perPage = 20): LengthAwarePaginator;
    public function findById(int $id): ?Product;
    public function findBySlug(string $slug): ?Product;
    public function create(array $attributes): Product;
    public function update(Product $product, array $attributes): Product;
    public function delete(Product $product): bool;
}
```

```php
// app/Repositories/Eloquent/ProductRepository.php
namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    public function paginate(array $filters = [], int $page = 1, int $perPage = 20): LengthAwarePaginator
    {
        $query = Product::query();

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (isset($filters['min_price'])) {
            $query->where('price', '>=', (int) $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', (int) $filters['max_price']);
        }

        if (!empty($filters['sort'])) {
            // expected format: field:direction
            [$field, $dir] = array_pad(explode(':', $filters['sort'], 2), 2, 'asc');
            $allowed = ['id', 'name', 'price', 'created_at'];
            if (in_array($field, $allowed, true)) {
                $query->orderBy($field, $dir === 'desc' ? 'desc' : 'asc');
            }
        } else {
            $query->orderBy('id', 'desc');
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function findById(int $id): ?Product
    {
        return Product::find($id);
    }

    public function findBySlug(string $slug): ?Product
    {
        return Product::where('slug', $slug)->first();
    }

    public function create(array $attributes): Product
    {
        return Product::create($attributes);
    }

    public function update(Product $product, array $attributes): Product
    {
        $product->fill($attributes)->save();
        return $product->refresh();
    }

    public function delete(Product $product): bool
    {
        return (bool) $product->delete();
    }
}
```

Registration (AppServiceProvider)
```php
// app/Providers/AppServiceProvider.php (excerpt)
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Eloquent\ProductRepository;

public function register(): void
{
    $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
}
```

Repository rules
- Keep query logic encapsulated; avoid leaking Eloquent specifics to services.
- Validate/normalize filters in Request/Service layer.

---

### 4) Service Interface and Implementation

```php
// app/Services/Contracts/ProductServiceInterface.php
namespace App\Services\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductServiceInterface
{
    public function list(array $filters, int $page, int $perPage): LengthAwarePaginator;
    public function getByIdOrSlug(string $idOrSlug): ?Product;
    public function create(array $attributes): Product;
    public function updateById(int $id, array $attributes): ?Product;
    public function deleteById(int $id): bool;
}
```

```php
// app/Services/ProductService.php
namespace App\Services;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\Contracts\ProductServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ProductService implements ProductServiceInterface
{
    public function __construct(private readonly ProductRepositoryInterface $products) {}

    public function list(array $filters, int $page, int $perPage): LengthAwarePaginator
    {
        return $this->products->paginate($filters, $page, $perPage);
    }

    public function getByIdOrSlug(string $idOrSlug): ?Product
    {
        if (ctype_digit($idOrSlug)) {
            return $this->products->findById((int) $idOrSlug);
        }
        return $this->products->findBySlug($idOrSlug);
    }

    public function create(array $attributes): Product
    {
        // Generate slug if not provided
        if (empty($attributes['slug']) && !empty($attributes['name'])) {
            $attributes['slug'] = Str::slug($attributes['name']);
        }
        return $this->products->create($attributes);
    }

    public function updateById(int $id, array $attributes): ?Product
    {
        $product = $this->products->findById($id);
        if (!$product) {
            return null;
        }
        if (isset($attributes['name']) && empty($attributes['slug'])) {
            $attributes['slug'] = Str::slug($attributes['name']);
        }
        return $this->products->update($product, $attributes);
    }

    public function deleteById(int $id): bool
    {
        $product = $this->products->findById($id);
        return $product ? $this->products->delete($product) : false;
    }
}
```

Bind the service
```php
// app/Providers/AppServiceProvider.php (excerpt)
use App\Services\Contracts\ProductServiceInterface;
use App\Services\ProductService;

public function register(): void
{
    $this->app->bind(ProductServiceInterface::class, ProductService::class);
}
```

Service rules
- Derive slugs/derived fields here, not in controllers.
- Enforce business invariants.

---

### 5) Request Validation (FormRequest)

```php
// app/Http/Requests/Product/ListProductsRequest.php
namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ListProductsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'is_active' => 'sometimes|boolean',
            'min_price' => 'sometimes|integer|min:0',
            'max_price' => 'sometimes|integer|min:0',
            'sort' => 'sometimes|string', // field:direction
        ];
    }
}
```

```php
// app/Http/Requests/Product/StoreProductRequest.php
namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => 'required|string|min:1|max:255',
            'slug' => 'sometimes|nullable|string|min:1|max:255',
            'description' => 'sometimes|nullable|string',
            'price' => 'required|integer|min:0',
            'is_active' => 'sometimes|boolean',
            'meta' => 'sometimes|array',
        ];
    }
}
```

```php
// app/Http/Requests/Product/UpdateProductRequest.php
namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|min:1|max:255',
            'slug' => 'sometimes|nullable|string|min:1|max:255',
            'description' => 'sometimes|nullable|string',
            'price' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
            'meta' => 'sometimes|array',
        ];
    }
}
```

Validation rules
- Only accept fields you expect.
- Constrain sizes and types.
- Use custom messages/localization as needed.

---

### 6) DTOs (Optional but Recommended)

```php
// app/Data/Product/ProductData.php
namespace App\Data\Product;

use Spatie\LaravelData\Data;

class ProductData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public ?string $description,
        public int $price,
        public bool $is_active,
        public ?array $meta,
    ) {}
}
```

DTOs are useful when mapping models to typed arrays in services or to return from resources.

---

### 7) Resource Transformer

```php
// app/Http/Resources/Product/ProductResource.php
namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'is_active' => $this->is_active,
            'meta' => $this->meta,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

Resource rules
- Do not leak internal fields.
- Consistently include timestamps where useful.

---

### 8) Controller (Thin) + Caching + Envelope

```php
// app/Http/Controllers/Api/V1/ProductController.php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ListProductsRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\Product\ProductResource;
use App\Services\Contracts\ProductServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function __construct(private readonly ProductServiceInterface $service) {}

    /**
     * @OA\Get(
     *   path="/api/v1/products",
     *   summary="List products",
     *   tags={"Products"}
     * )
     */
    public function index(ListProductsRequest $request): JsonResponse
    {
        $cacheKey = sprintf('products.index:%s:%s', app()->getLocale(), md5(json_encode($request->query())));
        $ttl = 300;
        $paginator = Cache::remember($cacheKey, $ttl, function () use ($request) {
            $filters = $request->validated();
            $page = (int) $request->integer('page', 1);
            $perPage = (int) $request->integer('per_page', 20);
            return $this->service->list($filters, $page, $perPage);
        });

        $items = $paginator->through(fn ($m) => ProductResource::make($m));
        return $this->paginated($items, __('products.list_retrieved'));
    }

    /**
     * @OA\Get(
     *   path="/api/v1/products/{id}",
     *   summary="Get product by ID or slug",
     *   tags={"Products"}
     * )
     */
    public function show(string $id): JsonResponse
    {
        $cacheKey = sprintf('products.show:%s:%s', app()->getLocale(), $id);
        $ttl = 900;
        $product = Cache::remember($cacheKey, $ttl, fn () => $this->service->getByIdOrSlug($id));
        if (!$product) {
            return $this->notFound(__('products.not_found'));
        }
        return $this->ok(ProductResource::make($product), __('products.retrieved'));
    }

    /**
     * @OA\Post(
     *   path="/api/v1/products",
     *   summary="Create product",
     *   tags={"Products"}
     * )
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->service->create($request->validated());
        return $this->created(ProductResource::make($product), __('products.created'));
    }

    /**
     * @OA\Put(
     *   path="/api/v1/products/{id}",
     *   summary="Update product",
     *   tags={"Products"}
     * )
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = $this->service->updateById($id, $request->validated());
        if (!$product) {
            return $this->notFound(__('products.not_found'));
        }
        return $this->ok(ProductResource::make($product), __('products.updated'));
    }

    /**
     * @OA\Delete(
     *   path="/api/v1/products/{id}",
     *   summary="Delete product",
     *   tags={"Products"}
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->service->deleteById($id);
        if (!$deleted) {
            return $this->notFound(__('products.not_found'));
        }
        return $this->ok(null, __('products.deleted'));
    }
}
```

Controller rules
- Validate via FormRequests.
- Use `ApiResponse` helpers: `ok`, `created`, `paginated`, `notFound`.
- Cache read endpoints (list/detail) with a reasonable TTL.

Routes (excerpt)
```php
// routes/api.php (excerpt inside v1 group)
Route::get('/products', [V1ProductController::class, 'index']);
Route::get('/products/{id}', [V1ProductController::class, 'show']);
Route::post('/products', [V1ProductController::class, 'store']);
Route::put('/products/{id}', [V1ProductController::class, 'update']);
Route::delete('/products/{id}', [V1ProductController::class, 'destroy']);
```

---

### 9) Response Envelope and Errors

Always wrap responses using `ApiResponse` to include:
- success: boolean
- message: string
- data: mixed
- error: { type, code, details? }
- meta: pagination or extra info
- trace_id: uuid (from incoming `X-Request-Id` or generated)
- timestamp: ISO string

Error handling rules
- Validation errors → 400/422 depending on policy; include field details.
- Unauthorized/Forbidden → 401/403 helpers.
- Not found → throw/return using `notFound` helper.
- Server errors → 500 with safe message; log detailed error.

---

### 10) Authentication and Authorization

- Public endpoints: no auth; still apply rate limiting and validation.
- Protected endpoints: use Sanctum `auth:sanctum` routes group.
- Role checks: enforce in controllers/services (e.g., admin-only).
- Do not leak sensitive data in responses or logs.

---

### 11) Caching Strategy

- Use `Cache::remember` in controllers for read endpoints.
- Key pattern: `<resource>.<action>:<locale>:<md5(query_or_id)>`.
- TTL guidelines:
  - Lists with filters: 5 minutes
  - Details: 15 minutes
  - Dictionaries (categories/tags): 60 minutes
- Invalidation:
  - On create/update/delete, evict related keys (consider tags if using Redis).

---

### 12) Internationalization (i18n)

- `SetLocale` middleware reads locale from request and sets app locale.
- Use translation strings for messages (e.g., `__('products.created')`).
- For translatable fields, store as arrays or leverage dedicated packages.

---

### 13) Observability (Logging & Metrics)

- `ApiResponse` includes `trace_id` and `timestamp`.
- Use structured logging for external calls (status, body when safe).
- Log at appropriate levels: info (normal), warning (recoverable issues), error (failures).

---

### 14) Performance

- Always paginate list endpoints.
- Avoid N+1: eager load relations where needed.
- Add DB indexes for frequent filters/sorts.
- Cache read endpoints as per policy.

---

### 15) OpenAPI (Swagger) Annotations

- Add `@OA` annotations to controller actions: path, summary, parameters, request body, responses.
- Generate docs with:
```bash
php artisan l5-swagger:generate
```
- Output is written to `storage/api-docs` (served via Swagger UI as configured).

---

### 16) Testing

Feature test example (list)
```php
// tests/Feature/Api/V1/Product/ListProductsTest.php
namespace Tests\Feature\Api\V1\Product;

use App\Models\Product;
use Tests\TestCase;

class ListProductsTest extends TestCase
{
    public function test_it_lists_products(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/products?page=1&per_page=2');

        $response->assertOk()
            ->assertJsonStructure([
                'success', 'message', 'data', 'meta' => ['page','page_size','total_count','total_pages'], 'trace_id', 'timestamp'
            ]);
    }
}
```

Feature test example (crud)
```php
// tests/Feature/Api/V1/Product/CrudProductsTest.php
namespace Tests\Feature\Api\V1\Product;

use App\Models\Product;
use Tests\TestCase;

class CrudProductsTest extends TestCase
{
    public function test_create_update_delete_product(): void
    {
        // Create
        $create = $this->postJson('/api/v1/products', [
            'name' => 'Pro Serum',
            'price' => 199000,
            'description' => 'Skin care',
        ])->assertCreated();

        $id = $create->json('data.id');

        // Update
        $this->putJson("/api/v1/products/{$id}", [
            'price' => 249000,
        ])->assertOk();

        // Show
        $this->getJson("/api/v1/products/{$id}")
            ->assertOk()
            ->assertJsonPath('data.price', 249000);

        // Delete
        $this->deleteJson("/api/v1/products/{$id}")
            ->assertOk();

        // Show not found
        $this->getJson("/api/v1/products/{$id}")
            ->assertStatus(404);
    }
}
```

Unit test example (service)
```php
// tests/Unit/Services/ProductServiceTest.php
namespace Tests\Unit\Services;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\ProductService;
use PHPUnit\Framework\TestCase;

class ProductServiceTest extends TestCase
{
    public function test_slug_generation_when_missing(): void
    {
        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('create')
            ->with($this->callback(function(array $attrs) {
                return $attrs['slug'] === 'pro-serum';
            }))
            ->willReturn(new Product());

        $service = new ProductService($repo);
        $service->create(['name' => 'Pro Serum', 'price' => 100]);
    }
}
```

---

### 17) Security and Hardening

- Input validation at boundaries (FormRequests).
- Mask PII in logs; avoid logging raw secrets.
- Idempotency for mutating endpoints when appropriate (e.g., payments).
- Rate limit public endpoints (`throttle:60,1` already applied at group level).
- Use HTTPS everywhere; validate external callback signatures (e.g., VNPay).

---

### 18) Versioning and Deprecation

- Use path versioning (`/api/v1`).
- For breaking changes, add `/api/v2` and maintain `/v1` until deprecation window passes.
- Communicate changes in docs; provide migration guide when needed.

---

### 19) Error Codes and Frontend Mapping

Standard mapping (examples):
- 400 VALIDATION_FAILED → show field errors
- 401 UNAUTHORIZED → redirect to login
- 403 FORBIDDEN → permission error display
- 404 NOT_FOUND → empty state
- 409 CONFLICT → conflict resolution UI
- 422 UNPROCESSABLE → specific business rule violation
- 429 RATE_LIMIT_EXCEEDED → retry-after UX
- 500 INTERNAL_ERROR → generic error with retry option

---

### 20) Checklists Before Merging

- Request validation covers all fields.
- Controller delegates to service; no business logic in controllers.
- Repository contains all query logic; no raw queries in controllers/services (unless justified).
- Resource output matches API docs; no sensitive leakage.
- OpenAPI annotations up to date and `php artisan l5-swagger:generate` passes.
- Feature tests pass; unit tests added for complex logic.
- Caching keys deterministic and scoped by locale/query.
- i18n strings added for user-facing messages.
- Logs contain meaningful context; no secrets.

---

### 21) Example: Full Flow Recap

1) Define migration (schema).
2) Create Eloquent model (fillable + casts).
3) Create repository interface + implementation.
4) Register repository binding in `AppServiceProvider`.
5) Create service interface + implementation; register in `AppServiceProvider`.
6) Create FormRequests for list/store/update; add rules and messages.
7) Create Resource transformer for output.
8) Create Controller: inject service, wire endpoints, use envelope + caching.
9) Add routes in `routes/api.php`.
10) Add OpenAPI annotations.
11) Write feature tests and unit tests.
12) Update API docs under `docs/api/<domain>` using template.
13) Run swagger generation; verify.

---

### 22) Appendix: Useful Snippets

Paginated envelope usage
```php
$paginator = $service->list($filters, $page, $perPage);
$items = $paginator->through(fn ($m) => ProductResource::make($m));
return $this->paginated($items, __('products.list_retrieved'));
```

Cache key helper (pattern)
```php
$key = sprintf('%s.%s:%s:%s', $resource, $action, app()->getLocale(), md5(json_encode($request->query())));
```

Localized messages example
```php
return $this->ok(ProductResource::make($product), __('products.retrieved'));
```

HTTP client call with logging (external API)
```php
$response = Http::timeout(10)->post($url, $payload);
if (!$response->successful()) {
    Log::error('External API error', ['status' => $response->status(), 'body' => $response->body()]);
    throw new \RuntimeException('External API failure');
}
```

---

This standard should be applied consistently across modules. Extend and adapt where necessary, but keep the separation of concerns, validation rigor, response consistency, and observability intact.



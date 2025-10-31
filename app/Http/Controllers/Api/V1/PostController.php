<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\Post\PostResource;
use App\Services\Contracts\PostServiceInterface;
use App\Models\PostCategory;
use App\Models\PostTag;
use App\Traits\HasLocalization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PostController extends Controller
{
    use HasLocalization;
    /**
     * Create a new PostController instance.
     *
     * @param PostServiceInterface $service The post service
     */
    public function __construct(private readonly PostServiceInterface $service)
    {
        //
    }

    /**
     * @OA\Get(
     *     path="/api/v1/posts",
     *     summary="List posts",
     *     tags={"Posts"},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="category_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="tag_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="featured", in="query", @OA\Schema(type="boolean")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Display a listing of posts.
     *
     * @param Request $request The HTTP request
     * @return JsonResponse The paginated list of posts
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'category_id' => 'sometimes|integer|min:1',
            'tag_id' => 'sometimes|integer|min:1',
            'featured' => 'sometimes|boolean',
        ]);

        $cacheKey = sprintf('posts.index:%s:%s', app()->getLocale(), md5(json_encode($request->query())));
        $ttlSeconds = 300; // 5 minutes

        $paginator = Cache::remember($cacheKey, $ttlSeconds, function () use ($request) {
            return $this->service->getPosts($request->all());
        });

        $items = $paginator->through(fn($post) => PostResource::make($post));
        
        return $this->paginated($items, __('posts.list_retrieved'));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/posts/{id}",
     *     summary="Get post by id or slug",
     *     tags={"Posts"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", description="Post ID or slug")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Display the specified post.
     *
     * @param Request $request The HTTP request
     * @param string $idOrSlug The post ID or slug
     * @return JsonResponse The post response
     */
    public function show(Request $request, string $idOrSlug): JsonResponse
    {
        $cacheKey = sprintf('posts.show:%s:%s', app()->getLocale(), $idOrSlug);
        $ttlSeconds = 900; // 15 minutes

        // Try to get by ID first, then by slug
        $post = Cache::remember($cacheKey, $ttlSeconds, function () use ($idOrSlug) {
        if (is_numeric($idOrSlug)) {
                return $this->service->getPostById((int)$idOrSlug);
        }
            return $this->service->getPostBySlug($idOrSlug);
        });
        
        if (!$post) {
            $this->notFound(__('posts.resource_post'));
        }
        
        // Increment view count
        $this->service->incrementViews($post);
        
        return $this->ok(PostResource::make($post), __('posts.retrieved'));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/posts/featured",
     *     summary="Get featured posts",
     *     tags={"Posts"},
     *     @OA\Parameter(name="limit", in="query", @OA\Schema(type="integer", default=6)),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Get featured posts.
     *
     * @param Request $request The HTTP request
     * @return JsonResponse The featured posts response
     */
    public function featured(Request $request): JsonResponse
    {
        $request->validate([
            'limit' => 'sometimes|integer|min:1|max:50',
        ]);
        $limit = $request->input('limit', 6);

        $cacheKey = sprintf('posts.featured:%s:%d', app()->getLocale(), (int)$limit);
        $ttlSeconds = 900; // 15 minutes

        $posts = Cache::remember($cacheKey, $ttlSeconds, function () use ($limit) {
            return $this->service->getFeaturedPosts($limit);
        });
        
        return $this->ok(
            PostResource::collection($posts),
            __('posts.featured_retrieved')
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/post-categories",
     *     summary="List post categories",
     *     tags={"Posts"},
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     */
    public function categories(): JsonResponse
    {
        $cacheKey = sprintf('posts.categories:%s', app()->getLocale());
        $ttlSeconds = 3600; // 60 minutes
        $items = Cache::remember($cacheKey, $ttlSeconds, function () {
            return PostCategory::query()->active()->orderBy('name')->get(['id','name','slug']);
        });
        return $this->ok($items, __('posts.categories_retrieved'));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/post-tags",
     *     summary="List post tags",
     *     tags={"Posts"},
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     */
    public function tags(): JsonResponse
    {
        $cacheKey = 'posts.tags';
        $ttlSeconds = 3600; // 60 minutes
        $items = Cache::remember($cacheKey, $ttlSeconds, function () {
            return PostTag::query()->orderBy('name')->get(['id','name','slug']);
        });
        return $this->ok($items, __('posts.tags_retrieved'));
    }
}

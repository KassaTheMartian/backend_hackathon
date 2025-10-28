<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\Post\PostResource;
use App\Services\Contracts\PostServiceInterface;
use App\Models\PostCategory;
use App\Models\PostTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
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
     *     @OA\Parameter(name="locale", in="query", @OA\Schema(type="string")),
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
        
        $posts = $this->service->getPosts($request->all());
        $items = $posts->through(fn($post) => PostResource::make($post));
        
        return $this->paginated($items, 'Posts retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/posts/{id}",
     *     summary="Get post by id or slug",
     *     tags={"Posts"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", description="Post ID or slug")),
     *     @OA\Parameter(name="locale", in="query", @OA\Schema(type="string")),
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
        // Try to get by ID first, then by slug
        if (is_numeric($idOrSlug)) {
            $post = $this->service->getPostById((int)$idOrSlug);
        } else {
            $post = $this->service->getPostBySlug($idOrSlug);
        }
        
        if (!$post) {
            $this->notFound('Post');
        }
        
        // Increment view count
        $this->service->incrementViews($post);
        
        return $this->ok(PostResource::make($post), 'Post retrieved successfully');
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
        $limit = $request->input('limit', 6);
        $posts = $this->service->getFeaturedPosts($limit);
        
        return $this->ok(
            PostResource::collection($posts),
            'Featured posts retrieved successfully'
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
        $items = PostCategory::query()->where('is_active', true)->orderBy('name')->get(['id','name','slug']);
        return $this->ok($items, 'Post categories retrieved successfully');
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
        $items = PostTag::query()->orderBy('name')->get(['id','name','slug']);
        return $this->ok($items, 'Post tags retrieved successfully');
    }
}

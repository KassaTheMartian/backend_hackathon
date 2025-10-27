<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\Post\PostResource;
use App\Models\Post;
use App\Services\Contracts\PostServiceInterface;
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
        $this->authorize('viewAny', Post::class);
        
        $posts = $this->service->getPosts($request->all());
        $items = $posts->through(fn($post) => PostResource::make($post));
        
        return $this->paginated($items, 'Posts retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/posts",
     *     summary="Create post",
     *     tags={"Posts"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "content"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="excerpt", type="string"),
     *             @OA\Property(property="featured_image", type="string"),
     *             @OA\Property(property="category_id", type="integer"),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="integer")),
     *             @OA\Property(property="is_published", type="boolean"),
     *             @OA\Property(property="locale", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=422, description="Validation Error", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Store a newly created post.
     *
     * @param StorePostRequest $request The store post request
     * @return JsonResponse The created post response
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        $this->authorize('create', Post::class);
        
        $post = $this->service->createPost($request->validated());
        return $this->created(PostResource::make($post), 'Post created successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/posts/{id}",
     *     summary="Get post by id",
     *     tags={"Posts"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="locale", in="query", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Display the specified post.
     *
     * @param Request $request The HTTP request
     * @param int $id The post ID
     * @return JsonResponse The post response
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $post = $this->service->getPostById($id);
        
        if (!$post) {
            $this->notFound('Post');
        }
        
        $this->authorize('view', $post);
        
        // Increment view count
        $this->service->incrementViews($post);
        
        return $this->ok(PostResource::make($post), 'Post retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/posts/{id}",
     *     summary="Update post",
     *     tags={"Posts"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="excerpt", type="string"),
     *             @OA\Property(property="featured_image", type="string"),
     *             @OA\Property(property="category_id", type="integer"),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="integer")),
     *             @OA\Property(property="is_published", type="boolean"),
     *             @OA\Property(property="locale", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=422, description="Validation Error", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Update the specified post.
     *
     * @param UpdatePostRequest $request The update post request
     * @param int $id The post ID
     * @return JsonResponse The updated post response
     */
    public function update(UpdatePostRequest $request, int $id): JsonResponse
    {
        $post = $this->service->getPostById($id);
        if (!$post) {
            $this->notFound('Post');
        }
        
        $this->authorize('update', $post);
        
        $post = $this->service->updatePost($id, $request->validated());
        return $this->ok(PostResource::make($post), 'Post updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/posts/{id}",
     *     summary="Delete post",
     *     tags={"Posts"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="No Content"),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Remove the specified post from storage.
     *
     * @param int $id The post ID
     * @return JsonResponse The deletion response
     */
    public function destroy(int $id): JsonResponse
    {
        $post = $this->service->getPostById($id);
        if (!$post) {
            $this->notFound('Post');
        }
        
        $this->authorize('delete', $post);
        
        $deleted = $this->service->deletePost($id);
        return $this->noContent('Post deleted successfully');
    }
}
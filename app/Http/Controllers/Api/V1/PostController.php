<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Post\PostResource;
use App\Http\Resources\Post\PostCollection;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(
        private PostService $postService
    ) {}

    /**
     * Display a listing of posts.
     */
    public function index(Request $request): JsonResponse
    {
        $posts = $this->postService->getPosts($request->all());
        $items = $posts->through(fn($post) => new PostResource($post));
        
        return $this->paginated($items);
    }

    /**
     * Display the specified post.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $post = $this->postService->getPostById($id);
        
        if (!$post) {
            $this->notFound('Post');
        }
        
        $post = $this->postService->getPostWithDetails($post, $request->get('locale', 'vi'));
        
        // Increment view count
        $this->postService->incrementViews($post);
        
        return $this->ok(new PostResource($post));
    }
}
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
        
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => new PostCollection($posts),
            'error' => null,
            'meta' => [
                'page' => $posts->currentPage(),
                'page_size' => $posts->perPage(),
                'total_count' => $posts->total(),
                'total_pages' => $posts->lastPage(),
                'has_next_page' => $posts->hasMorePages(),
                'has_previous_page' => $posts->currentPage() > 1,
            ],
            'trace_id' => $request->header('X-Trace-ID'),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Display the specified post.
     */
    public function show(Request $request, Post $post): JsonResponse
    {
        $post = $this->postService->getPostWithDetails($post, $request->get('locale', 'vi'));
        
        // Increment view count
        $this->postService->incrementViews($post);
        
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => new PostResource($post),
            'error' => null,
            'meta' => null,
            'trace_id' => $request->header('X-Trace-ID'),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
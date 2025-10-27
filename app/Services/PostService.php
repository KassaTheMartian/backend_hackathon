<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PostService
{
    /**
     * Get posts with filters.
     */
    public function getPosts(array $filters = []): LengthAwarePaginator
    {
        $query = Post::with(['author', 'category', 'tags'])
            ->published()
            ->latest();

        // Apply filters
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['search'])) {
            $query->search($filters['search']);
        }

        if (isset($filters['featured'])) {
            $query->featured();
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get post with details.
     */
    public function getPostWithDetails(Post $post, string $locale = 'vi'): Post
    {
        return $post->load([
            'author',
            'category',
            'tags'
        ]);
    }

    /**
     * Increment post views.
     */
    public function incrementViews(Post $post): void
    {
        $post->incrementViews();
    }

    /**
     * Get related posts.
     */
    public function getRelatedPosts(Post $post, int $limit = 4): Collection
    {
        return Post::with(['author', 'category'])
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->published()
            ->latest()
            ->limit($limit)
            ->get();
    }
}

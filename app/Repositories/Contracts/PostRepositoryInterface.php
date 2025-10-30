<?php

namespace App\Repositories\Contracts;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PostRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get posts with filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getWithFilters(array $filters = []): LengthAwarePaginator;

    /**
     * Get post by slug.
     *
     * @param string $slug
     * @return Post|null
     */
    public function getBySlug(string $slug): ?Post;

    /**
     * Publish a post.
     *
     * @param Post $post
     * @return Post
     */
    public function publish(Post $post): Post;

    /**
     * Unpublish a post.
     *
     * @param Post $post
     * @return Post
     */
    public function unpublish(Post $post): Post;

    /**
     * Increment post views.
     *
     * @param Post $post
     * @return void
     */
    public function incrementViews(Post $post): void;

    /**
     * Get related posts.
     *
     * @param Post $post
     * @param int $limit
     * @return Collection
     */
    public function getRelated(Post $post, int $limit = 4): Collection;

    /**
     * Get featured posts.
     *
     * @param int $limit
     * @return Collection
     */
    public function getFeatured(int $limit = 6): Collection;
}


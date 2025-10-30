<?php

namespace App\Services\Contracts;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PostServiceInterface
{
    /**
     * Get posts with filters.
     *
     * @param array $filters The filters.
     * @return LengthAwarePaginator
     */
    public function getPosts(array $filters = []): LengthAwarePaginator;

    /**
     * Get post by ID.
     *
     * @param int $id The post ID.
     * @return Post|null
     */
    public function getPostById(int $id): ?Post;

    /**
     * Get post by slug.
     *
     * @param string $slug The post slug.
     * @return Post|null
     */
    public function getPostBySlug(string $slug): ?Post;

    /**
     * Create a new post.
     *
     * @param array $data The post data.
     * @return Post
     */
    public function createPost(array $data): Post;

    /**
     * Update a post.
     *
     * @param int $id The post ID.
     * @param array $data The updated post data.
     * @return Post|null
     */
    public function updatePost(int $id, array $data): ?Post;

    /**
     * Delete a post.
     *
     * @param int $id The post ID.
     * @return bool
     */
    public function deletePost(int $id): bool;

    /**
     * Publish a post.
     *
     * @param Post $post The post.
     * @return Post
     */
    public function publishPost(Post $post): Post;

    /**
     * Unpublish a post.
     *
     * @param Post $post The post.
     * @return Post
     */
    public function unpublishPost(Post $post): Post;

    /**
     * Increment post views.
     *
     * @param Post $post The post.
     * @return void
     */
    public function incrementViews(Post $post): void;

    /**
     * Get related posts.
     *
     * @param Post $post The post.
     * @param int $limit The limit.
     * @return Collection
     */
    public function getRelatedPosts(Post $post, int $limit = 4): Collection;

    /**
     * Get featured posts.
     *
     * @param int $limit The limit.
     * @return Collection
     */
    public function getFeaturedPosts(int $limit = 6): Collection;
}

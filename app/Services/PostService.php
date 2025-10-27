<?php

namespace App\Services;

use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PostService
{
    public function __construct(
        private PostRepositoryInterface $postRepository
    ) {}

    /**
     * Get posts with filters.
     */
    public function getPosts(array $filters = []): LengthAwarePaginator
    {
        return $this->postRepository->getWithFilters($filters);
    }

    /**
     * Get post by ID.
     */
    public function getPostById(int $id): ?Post
    {
        return $this->postRepository->getById($id);
    }

    /**
     * Get post by slug.
     */
    public function getPostBySlug(string $slug): ?Post
    {
        return $this->postRepository->getBySlug($slug);
    }

    /**
     * Get post with details (relationships loaded).
     */
    public function getPostWithDetails(Post $post, string $locale = 'vi'): Post
    {
        return $post->load(['category', 'tags', 'author']);
    }

    /**
     * Create a new post.
     */
    public function createPost(array $data): Post
    {
        return $this->postRepository->create($data);
    }

    /**
     * Update a post.
     */
    public function updatePost(int $id, array $data): ?Post
    {
        return $this->postRepository->update($id, $data);
    }

    /**
     * Delete a post.
     */
    public function deletePost(int $id): bool
    {
        return $this->postRepository->delete($id);
    }

    /**
     * Publish a post.
     */
    public function publishPost(Post $post): Post
    {
        return $this->postRepository->publish($post);
    }

    /**
     * Unpublish a post.
     */
    public function unpublishPost(Post $post): Post
    {
        return $this->postRepository->unpublish($post);
    }

    /**
     * Increment post views.
     */
    public function incrementViews(Post $post): void
    {
        $this->postRepository->incrementViews($post);
    }

    /**
     * Get related posts.
     */
    public function getRelatedPosts(Post $post, int $limit = 4): Collection
    {
        return $this->postRepository->getRelated($post, $limit);
    }

    /**
     * Get featured posts.
     */
    public function getFeaturedPosts(int $limit = 6): Collection
    {
        return $this->postRepository->getFeatured($limit);
    }
}
<?php

namespace App\Services\Contracts;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PostServiceInterface
{
    public function getPosts(array $filters = []): LengthAwarePaginator;
    public function getPostById(int $id): ?Post;
    public function getPostBySlug(string $slug): ?Post;
    public function createPost(array $data): Post;
    public function updatePost(int $id, array $data): ?Post;
    public function deletePost(int $id): bool;
    public function publishPost(Post $post): Post;
    public function unpublishPost(Post $post): Post;
    public function incrementViews(Post $post): void;
    public function getRelatedPosts(Post $post, int $limit = 4): Collection;
    public function getFeaturedPosts(int $limit = 6): Collection;
}

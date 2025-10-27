<?php

namespace App\Repositories\Contracts;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PostRepositoryInterface
{
    public function getWithFilters(array $filters = []): LengthAwarePaginator;
    public function getById(int $id): ?Post;
    public function getBySlug(string $slug): ?Post;
    public function create(array $data): Post;
    public function update(Post $post, array $data): Post;
    public function delete(Post $post): bool;
    public function publish(Post $post): Post;
    public function unpublish(Post $post): Post;
    public function incrementViews(Post $post): void;
    public function getRelated(Post $post, int $limit = 4): Collection;
    public function getFeatured(int $limit = 6): Collection;
}


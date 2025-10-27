<?php

namespace App\Repositories\Eloquent;

use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PostRepository extends BaseRepository implements PostRepositoryInterface
{
    public function getById(int $id): ?Post
    {
        return $this->model->find($id);
    }

    public function getWithFilters(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['featured'])) {
            $query->where('is_featured', $filters['featured']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereRaw("JSON_EXTRACT(title, '$.en') LIKE ?", ['%' . $filters['search'] . '%'])
                  ->orWhereRaw("JSON_EXTRACT(title, '$.vi') LIKE ?", ['%' . $filters['search'] . '%'])
                  ->orWhereRaw("JSON_EXTRACT(content, '$.en') LIKE ?", ['%' . $filters['search'] . '%'])
                  ->orWhereRaw("JSON_EXTRACT(content, '$.vi') LIKE ?", ['%' . $filters['search'] . '%']);
            });
        }

        $query->orderBy('created_at', 'desc');

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function getBySlug(string $slug): ?Post
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function publish(Post $post): Post
    {
        $post->update(['status' => 'published']);
        return $post;
    }

    public function unpublish(Post $post): Post
    {
        $post->update(['status' => 'draft']);
        return $post;
    }

    public function incrementViews(Post $post): void
    {
        $post->increment('views_count');
    }

    public function getRelated(Post $post, int $limit = 4): Collection
    {
        return $this->model
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->where('status', 'published')
            ->limit($limit)
            ->get();
    }

    public function getFeatured(int $limit = 6): Collection
    {
        return $this->model
            ->where('is_featured', true)
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}

<?php

namespace App\Repositories\Eloquent;

use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class PostRepository
 */
class PostRepository extends BaseRepository implements PostRepositoryInterface
{
    /**
     * Create a new repository instance.
     *
     * @param Post $model
     */
    public function __construct(Post $model)
    {
        parent::__construct($model);
    }

    /**
     * Get allowed includes for eager loading.
     *
     * @return array
     */
    protected function allowedIncludes(): array
    {
        return ['category', 'tags', 'author'];
    }

    /**
     * Get supported locales from configuration.
     * 
     * @return array<string>
     */
    protected function getSupportedLocales(): array
    {
        return config('localization.supported', ['en', 'vi']);
    }

    public function getWithFilters(array $filters = []): LengthAwarePaginator
    {
        $query = $this->query();

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
            $supportedLocales = $this->getSupportedLocales();
            
            $query->where(function ($q) use ($filters, $supportedLocales) {
                foreach ($supportedLocales as $locale) {
                    $q->orWhereRaw("JSON_EXTRACT(title, '$.{$locale}') LIKE ?", ['%' . $filters['search'] . '%'])
                      ->orWhereRaw("JSON_EXTRACT(content, '$.{$locale}') LIKE ?", ['%' . $filters['search'] . '%']);
                }
            });
        }

        $query->orderBy('created_at', 'desc');

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function getBySlug(string $slug): ?Post
    {
        // Slug is stored as JSON with locale keys
        // Try to find by any supported language slug
        $supportedLocales = $this->getSupportedLocales();
        
        return $this->query()
            ->where(function ($query) use ($slug, $supportedLocales) {
                foreach ($supportedLocales as $locale) {
                    $query->orWhereRaw('JSON_EXTRACT(slug, "$.{$locale}") = ?', [$slug]);
                }
            })
            ->first();
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
        return $this->query()
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->where('status', 'published')
            ->limit($limit)
            ->get();
    }

    public function getFeatured(int $limit = 6): Collection
    {
        return $this->query()
            ->where('is_featured', true)
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'author_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'images',
        'status',
        'published_at',
        'views_count',
        'reading_time',
        'is_featured',
        'allow_comments',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'title' => 'array',
        'slug' => 'array',
        'excerpt' => 'array',
        'content' => 'array',
        'images' => 'array',
        'published_at' => 'datetime',
        'views_count' => 'integer',
        'reading_time' => 'integer',
        'is_featured' => 'boolean',
        'allow_comments' => 'boolean',
        'meta_title' => 'array',
        'meta_description' => 'array',
        'meta_keywords' => 'array',
    ];

    /**
     * Get the author of the post.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the category of the post.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class);
    }

    /**
     * Get the tags for the post.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(PostTag::class, 'post_tag_pivot');
    }

    /**
     * Scope a query to only include published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where('published_at', '<=', now());
    }

    /**
     * Scope a query to only include featured posts.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to only include draft posts.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to order by published date.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('published_at', 'desc');
    }

    /**
     * Scope a query to search posts.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->whereRaw('JSON_EXTRACT(title, "$.vi") LIKE ?', ["%{$search}%"])
              ->orWhereRaw('JSON_EXTRACT(title, "$.en") LIKE ?', ["%{$search}%"])
              ->orWhereRaw('JSON_EXTRACT(excerpt, "$.vi") LIKE ?', ["%{$search}%"])
              ->orWhereRaw('JSON_EXTRACT(excerpt, "$.en") LIKE ?', ["%{$search}%"])
              ->orWhereRaw('JSON_EXTRACT(content, "$.vi") LIKE ?', ["%{$search}%"])
              ->orWhereRaw('JSON_EXTRACT(content, "$.en") LIKE ?', ["%{$search}%"]);
        });
    }

    /**
     * Increment the views count.
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Get the estimated reading time in minutes.
     */
    public function getEstimatedReadingTime(): int
    {
        if ($this->reading_time) {
            return $this->reading_time;
        }

        // Calculate based on content length (assuming 200 words per minute)
        $content = $this->content;
        if (is_array($content)) {
            $content = implode(' ', $content);
        }

        $wordCount = str_word_count(strip_tags($content));
        return max(1, ceil($wordCount / 200));
    }
}

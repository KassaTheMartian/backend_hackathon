<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'booking_id',
        'service_id',
        'staff_id',
        'branch_id',
        'rating',
        'title',
        'comment',
        'service_quality_rating',
        'staff_rating',
        'cleanliness_rating',
        'value_rating',
        'images',
        'is_approved',
        'is_featured',
        'admin_response',
        'responded_at',
        'responded_by',
        'helpful_count',
    ];

    protected $casts = [
        'rating' => 'integer',
        'service_quality_rating' => 'integer',
        'staff_rating' => 'integer',
        'cleanliness_rating' => 'integer',
        'value_rating' => 'integer',
        'images' => 'array',
        'is_approved' => 'boolean',
        'is_featured' => 'boolean',
        'responded_at' => 'datetime',
        'helpful_count' => 'integer',
    ];

    /**
     * Get the user who wrote the review.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the booking associated with this review.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the service being reviewed.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the staff member being reviewed.
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Get the branch being reviewed.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the admin who responded to this review.
     */
    public function respondedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    /**
     * Scope a query to only include approved reviews.
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope a query to only include featured reviews.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to filter by service.
     */
    public function scopeForService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    /**
     * Scope a query to filter by staff.
     */
    public function scopeForStaff($query, $staffId)
    {
        return $query->where('staff_id', $staffId);
    }

    /**
     * Scope a query to filter by rating.
     */
    public function scopeWithRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * Scope a query to filter by minimum rating.
     */
    public function scopeWithMinRating($query, $minRating)
    {
        return $query->where('rating', '>=', $minRating);
    }

    /**
     * Get the average rating for detailed ratings.
     */
    public function getAverageDetailedRatingAttribute(): float
    {
        $ratings = collect([
            $this->service_quality_rating,
            $this->staff_rating,
            $this->cleanliness_rating,
            $this->value_rating,
        ])->filter();

        return $ratings->avg() ?? 0;
    }

    /**
     * Check if the review has detailed ratings.
     */
    public function hasDetailedRatings(): bool
    {
        return !is_null($this->service_quality_rating) ||
               !is_null($this->staff_rating) ||
               !is_null($this->cleanliness_rating) ||
               !is_null($this->value_rating);
    }
}

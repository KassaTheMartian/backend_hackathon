<?php

namespace App\Repositories\Eloquent;

use App\Models\Review;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ReviewRepository
 */
class ReviewRepository extends BaseRepository implements ReviewRepositoryInterface
{
    /**
     * Create a new repository instance.
     *
     * @param Review $model
     */
    public function __construct(Review $model)
    {
        parent::__construct($model);
    }

    /**
     * Get reviews with filters.
     */
    public function getWithFilters(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['user', 'service', 'staff', 'branch'])
            ->approved()
            ->latest();

        // Apply filters
        if (isset($filters['service_id'])) {
            $query->where('service_id', $filters['service_id']);
        }

        if (isset($filters['rating'])) {
            $query->where('rating', $filters['rating']);
        }

        if (isset($filters['min_rating'])) {
            $query->where('rating', '>=', $filters['min_rating']);
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Approve a review.
     */
    public function approve(Review $review): Review
    {
        $review->update(['is_approved' => true]);
        return $review->fresh();
    }

    /**
     * Reject a review.
     */
    public function reject(Review $review, string $reason): Review
    {
        $review->update([
            'is_approved' => false,
            'admin_response' => $reason,
        ]);
        return $review->fresh();
    }

    /**
     * Get review statistics.
     */
    public function getStats(array $filters): array
    {
        $query = $this->model->query();

        // Apply filters
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        $totalReviews = $query->count();
        $approvedReviews = $query->where('is_approved', true)->count();
        $averageRating = $query->where('is_approved', true)->avg('rating') ?? 0;

        return [
            'total_reviews' => $totalReviews,
            'approved_reviews' => $approvedReviews,
            'pending_reviews' => $totalReviews - $approvedReviews,
            'average_rating' => round($averageRating, 2),
        ];
    }

    /**
     * Get reviews for a service.
     */
    public function getForService(int $serviceId, array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['user', 'staff', 'branch'])
            ->where('service_id', $serviceId)
            ->approved()
            ->latest();

        // Apply filters
        if (isset($filters['rating'])) {
            $query->where('rating', $filters['rating']);
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get reviews for a staff member.
     */
    public function getForStaff(int $staffId, array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['user', 'service', 'branch'])
            ->where('staff_id', $staffId)
            ->approved()
            ->latest();

        // Apply filters
        if (isset($filters['rating'])) {
            $query->where('rating', $filters['rating']);
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Check if user has already reviewed a booking.
     */
    public function hasUserReviewedBooking(int $bookingId, int $userId): bool
    {
        return $this->model
            ->where('booking_id', $bookingId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Get pending reviews with pagination.
     */
    public function getPending(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['user', 'service', 'staff', 'branch'])
            ->where('is_approved', false)
            ->latest('id')
            ->paginate($perPage);
    }
}


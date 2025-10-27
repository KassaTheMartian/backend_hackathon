<?php

namespace App\Services;

use App\Models\Review;
use App\Models\Service;
use App\Models\Staff;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ReviewService
{
    /**
     * Get reviews with filters.
     */
    public function getReviews(array $filters = []): LengthAwarePaginator
    {
        $query = Review::with(['user', 'service', 'staff', 'branch'])
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
     * Get review with details.
     */
    public function getReviewWithDetails(Review $review): Review
    {
        return $review->load(['user', 'service', 'staff', 'branch']);
    }

    /**
     * Create a new review.
     */
    public function createReview(array $data): Review
    {
        $review = Review::create($data);

        // Update service and staff ratings
        $this->updateServiceRating($review->service);
        if ($review->staff) {
            $this->updateStaffRating($review->staff);
        }

        return $review;
    }

    /**
     * Update service rating.
     */
    private function updateServiceRating(Service $service): void
    {
        $reviews = $service->reviews()->approved();
        $averageRating = $reviews->avg('rating') ?? 0;
        $totalReviews = $reviews->count();

        $service->update([
            'rating' => round($averageRating, 2),
            'total_reviews' => $totalReviews,
        ]);
    }

    /**
     * Update staff rating.
     */
    private function updateStaffRating(Staff $staff): void
    {
        $reviews = $staff->reviews()->approved();
        $averageRating = $reviews->avg('rating') ?? 0;
        $totalReviews = $reviews->count();

        $staff->update([
            'rating' => round($averageRating, 2),
            'total_reviews' => $totalReviews,
        ]);
    }
}

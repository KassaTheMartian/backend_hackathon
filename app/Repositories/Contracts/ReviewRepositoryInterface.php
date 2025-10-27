<?php

namespace App\Repositories\Contracts;

use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReviewRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get reviews with filters.
     */
    public function getWithFilters(array $filters): LengthAwarePaginator;

    /**
     * Approve a review.
     */
    public function approve(Review $review): Review;

    /**
     * Reject a review.
     */
    public function reject(Review $review, string $reason): Review;

    /**
     * Get review statistics.
     */
    public function getStats(array $filters): array;

    /**
     * Get reviews for a service.
     */
    public function getForService(int $serviceId, array $filters): LengthAwarePaginator;

    /**
     * Get reviews for a staff member.
     */
    public function getForStaff(int $staffId, array $filters): LengthAwarePaginator;
}


<?php

namespace App\Repositories\Contracts;

use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReviewRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get reviews with filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getWithFilters(array $filters): LengthAwarePaginator;

    /**
     * Approve a review.
     *
     * @param Review $review
     * @return Review
     */
    public function approve(Review $review): Review;

    /**
     * Reject a review.
     *
     * @param Review $review
     * @param string $reason
     * @return Review
     */
    public function reject(Review $review, string $reason): Review;

    /**
     * Get review statistics.
     *
     * @param array $filters
     * @return array
     */
    public function getStats(array $filters): array;

    /**
     * Get reviews for a service.
     *
     * @param int $serviceId
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getForService(int $serviceId, array $filters): LengthAwarePaginator;

    /**
     * Get reviews for a staff member.
     *
     * @param int $staffId
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getForStaff(int $staffId, array $filters): LengthAwarePaginator;

    /**
     * Check if user has already reviewed a booking.
     *
     * @param int $bookingId
     * @param int $userId
     * @return bool
     */
    public function hasUserReviewedBooking(int $bookingId, int $userId): bool;

    /**
     * Get pending reviews with pagination.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPending(int $perPage = 15): LengthAwarePaginator;
}


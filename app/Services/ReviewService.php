<?php

namespace App\Services;

use App\Models\Review;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ReviewService
{
    public function __construct(
        private ReviewRepositoryInterface $reviewRepository
    ) {}

    /**
     * Get reviews with filters.
     */
    public function getReviews(array $filters = []): LengthAwarePaginator
    {
        return $this->reviewRepository->getWithFilters($filters);
    }

    /**
     * Get review by ID.
     */
    public function getReviewById(int $id): ?Review
    {
        return $this->reviewRepository->getById($id);
    }

    /**
     * Create a new review.
     */
    public function createReview(array $data): Review
    {
        return $this->reviewRepository->create($data);
    }

    /**
     * Update a review.
     */
    public function updateReview(Review $review, array $data): Review
    {
        return $this->reviewRepository->updateModel($review, $data);
    }

    /**
     * Delete a review.
     */
    public function deleteReview(Review $review): bool
    {
        return $this->reviewRepository->deleteModel($review);
    }

    /**
     * Approve a review.
     */
    public function approveReview(Review $review): Review
    {
        return $this->reviewRepository->approve($review);
    }

    /**
     * Reject a review.
     */
    public function rejectReview(Review $review, string $reason): Review
    {
        return $this->reviewRepository->reject($review, $reason);
    }

    /**
     * Get review statistics.
     */
    public function getReviewStats(array $filters = []): array
    {
        return $this->reviewRepository->getStats($filters);
    }

    /**
     * Get reviews for a service.
     */
    public function getServiceReviews(int $serviceId, array $filters = []): LengthAwarePaginator
    {
        return $this->reviewRepository->getForService($serviceId, $filters);
    }

    /**
     * Get reviews for a staff member.
     */
    public function getStaffReviews(int $staffId, array $filters = []): LengthAwarePaginator
    {
        return $this->reviewRepository->getForStaff($staffId, $filters);
    }
}
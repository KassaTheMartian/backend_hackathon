<?php

namespace App\Services;

use App\Data\Review\ReviewData;
use App\Models\Review;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use App\Services\Contracts\ReviewServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ReviewService implements ReviewServiceInterface
{
    public function __construct(
        private ReviewRepositoryInterface $reviewRepository
    ) {}

    /**
     * List reviews with pagination (required by interface).
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $filters = $request->only(['service_id', 'branch_id', 'rating', 'status', 'user_id']);
        $filters['per_page'] = $request->input('per_page', 15);
        
        return $this->reviewRepository->getWithFilters($filters);
    }

    /**
     * Create a new review (required by interface).
     */
    public function create(ReviewData $data): Model
    {
        return $this->reviewRepository->create($data->toArray());
    }

    /**
     * Create a new review from booking.
     * Automatically extracts service_id, staff_id, branch_id from booking.
     */
    public function createFromBooking(array $reviewData, int $userId): Model
    {
        // Fetch booking to get service_id, staff_id, branch_id
        $booking = \App\Models\Booking::findOrFail($reviewData['booking_id']);
        
        // Merge user_id and booking-related fields
        $data = array_merge($reviewData, [
            'user_id' => $userId,
            'service_id' => $booking->service_id,
            'staff_id' => $booking->staff_id,
            'branch_id' => $booking->branch_id,
        ]);
        
        $dto = ReviewData::from($data);
        return $this->create($dto);
    }

    /**
     * Find review by ID (required by interface).
     */
    public function find(int $id): ?Model
    {
        return $this->reviewRepository->getById($id);
    }

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
     * Get review with details (relationships loaded).
     */
    public function getReviewWithDetails(Review $review): Review
    {
        return $review->load(['user', 'service', 'booking']);
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
    public function updateReview(int $id, array $data): ?Review
    {
        return $this->reviewRepository->update($id, $data);
    }

    /**
     * Delete a review.
     */
    public function deleteReview(int $id): bool
    {
        return $this->reviewRepository->delete($id);
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
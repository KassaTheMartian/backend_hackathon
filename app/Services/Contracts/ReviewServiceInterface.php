<?php

namespace App\Services\Contracts;

use App\Data\Review\ReviewData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\Review;

interface ReviewServiceInterface
{
    /**
     * List reviews with pagination.
     *
     * @param Request $request The HTTP request.
     * @return LengthAwarePaginator
     */
    public function list(Request $request): LengthAwarePaginator;

    /**
     * Create a new review.
     *
     * @param ReviewData $data The review data.
     * @return Model
     */
    public function create(ReviewData $data): Model;

    /**
     * Create a review from booking.
     *
     * @param array $reviewData The review data.
     * @param int $userId The user ID.
     * @return Model
     */
    public function createFromBooking(array $reviewData, int $userId): Model;

    /**
     * Find a review by ID.
     *
     * @param int $id The review ID.
     * @return Model|null
     */
    public function find(int $id): ?Model;

    /**
     * Approve a review.
     *
     * @param Review $review The review.
     * @return Review
     */
    public function approveReview(Review $review): Review;

    /**
     * Reject a review.
     *
     * @param Review $review The review.
     * @param string $reason The rejection reason.
     * @return Review
     */
    public function rejectReview(Review $review, string $reason): Review;

    /**
     * List pending reviews (admin use).
     */
    public function pending(Request $request): LengthAwarePaginator;

    /**
     * Respond to a review as admin.
     */
    public function respondToReview(Review $review, string $message): Review;
}
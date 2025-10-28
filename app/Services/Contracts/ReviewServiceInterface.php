<?php

namespace App\Services\Contracts;

use App\Data\Review\ReviewData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\Review;

interface ReviewServiceInterface
{
    public function list(Request $request): LengthAwarePaginator;

    public function create(ReviewData $data): Model;

    public function createFromBooking(array $reviewData, int $userId): Model;

    public function find(int $id): ?Model;

    public function approveReview(\App\Models\Review $review): \App\Models\Review;

    public function rejectReview(\App\Models\Review $review, string $reason): \App\Models\Review;

    /**
     * List pending reviews (admin use).
     */
    public function pending(Request $request): LengthAwarePaginator;

    /**
     * Respond to a review as admin.
     */
    public function respondToReview(Review $review, string $message): Review;
}
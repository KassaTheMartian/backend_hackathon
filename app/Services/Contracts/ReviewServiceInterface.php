<?php

namespace App\Services\Contracts;

use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReviewServiceInterface
{
    public function getReviews(array $filters = []): LengthAwarePaginator;
    public function getReviewById(int $id): ?Review;
    public function createReview(array $data): Review;
    public function updateReview(int $id, array $data): ?Review;
    public function deleteReview(int $id): bool;
    public function approveReview(Review $review): Review;
    public function rejectReview(Review $review, string $reason): Review;
    public function getReviewStats(array $filters = []): array;
    public function getServiceReviews(int $serviceId, array $filters = []): LengthAwarePaginator;
    public function getStaffReviews(int $staffId, array $filters = []): LengthAwarePaginator;
}

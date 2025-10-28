<?php

namespace App\Services\Contracts;

use App\Data\Review\ReviewData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface ReviewServiceInterface
{
    public function list(Request $request): LengthAwarePaginator;

    public function create(ReviewData $data): Model;

    public function createFromBooking(array $reviewData, int $userId): Model;

    public function find(int $id): ?Model;

    public function approveReview(\App\Models\Review $review): \App\Models\Review;

    public function rejectReview(\App\Models\Review $review, string $reason): \App\Models\Review;
}
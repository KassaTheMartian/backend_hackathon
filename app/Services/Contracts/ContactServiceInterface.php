<?php

namespace App\Services\Contracts;

use App\Models\ContactSubmission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ContactServiceInterface
{
    /**
     * Get contact submissions with filters.
     */
    public function getSubmissions(array $filters = []): LengthAwarePaginator;

    /**
     * Get submission by ID.
     */
    public function getSubmissionById(int $id): ?ContactSubmission;

    /**
     * Create a new contact submission.
     */
    public function createSubmission(array $data): ContactSubmission;

    /**
     * Mark submission as read.
     */
    public function markAsRead(int $id): ?ContactSubmission;

    /**
     * Mark submission as unread.
     */
    public function markAsUnread(int $id): ?ContactSubmission;

    /**
     * Reply to submission.
     */
    public function replyToSubmission(int $id, string $reply): ?ContactSubmission;

    /**
     * Delete a submission.
     */
    public function deleteSubmission(int $id): bool;

    /**
     * Get unread submissions count.
     */
    public function getUnreadCount(): int;
}

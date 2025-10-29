<?php

namespace App\Services\Contracts;

use App\Models\ContactSubmission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ContactServiceInterface
{
    /**
     * Get contact submissions with filters.
     *
     * @param array $filters The filters to apply.
     * @return LengthAwarePaginator
     */
    public function getSubmissions(array $filters = []): LengthAwarePaginator;

    /**
     * Get submission by ID.
     *
     * @param int $id The submission ID.
     * @return ContactSubmission|null
     */
    public function getSubmissionById(int $id): ?ContactSubmission;

    /**
     * Create a new contact submission.
     *
     * @param array $data The submission data.
     * @return ContactSubmission
     */
    public function createSubmission(array $data): ContactSubmission;

    /**
     * Mark submission as read.
     *
     * @param int $id The submission ID.
     * @return ContactSubmission|null
     */
    public function markAsRead(int $id): ?ContactSubmission;

    /**
     * Mark submission as unread.
     *
     * @param int $id The submission ID.
     * @return ContactSubmission|null
     */
    public function markAsUnread(int $id): ?ContactSubmission;

    /**
     * Reply to submission.
     *
     * @param int $id The submission ID.
     * @param string $reply The reply message.
     * @return ContactSubmission|null
     */
    public function replyToSubmission(int $id, string $reply): ?ContactSubmission;

    /**
     * Delete a submission.
     *
     * @param int $id The submission ID.
     * @return bool
     */
    public function deleteSubmission(int $id): bool;

    /**
     * Get unread submissions count.
     *
     * @return int
     */
    public function getUnreadCount(): int;
}

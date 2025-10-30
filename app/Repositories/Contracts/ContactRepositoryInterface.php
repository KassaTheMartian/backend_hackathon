<?php

namespace App\Repositories\Contracts;

use App\Models\ContactSubmission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ContactRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get contact submissions with filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getWithFilters(array $filters = []): LengthAwarePaginator;

    /**
     * Mark a submission as read.
     *
     * @param ContactSubmission $submission
     * @return ContactSubmission
     */
    public function markAsRead(ContactSubmission $submission): ContactSubmission;

    /**
     * Mark a submission as unread.
     *
     * @param ContactSubmission $submission
     * @return ContactSubmission
     */
    public function markAsUnread(ContactSubmission $submission): ContactSubmission;

    /**
     * Reply to a submission.
     *
     * @param ContactSubmission $submission
     * @param string $reply
     * @return ContactSubmission
     */
    public function reply(ContactSubmission $submission, string $reply): ContactSubmission;

    /**
     * Get the count of unread submissions.
     *
     * @return int
     */
    public function getUnreadCount(): int;
}


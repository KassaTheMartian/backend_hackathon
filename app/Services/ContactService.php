<?php

namespace App\Services;

use App\Models\ContactSubmission;
use App\Repositories\Contracts\ContactRepositoryInterface;
use App\Services\Contracts\ContactServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ContactService implements ContactServiceInterface
{
    public function __construct(
        private ContactRepositoryInterface $contactRepository
    ) {}

    /**
     * Get contact submissions with filters.
     */
    public function getSubmissions(array $filters = []): LengthAwarePaginator
    {
        return $this->contactRepository->getWithFilters($filters);
    }

    /**
     * Get submission by ID.
     */
    public function getSubmissionById(int $id): ?ContactSubmission
    {
        return $this->contactRepository->getById($id);
    }

    /**
     * Create a new contact submission.
     */
    public function createSubmission(array $data): ContactSubmission
    {
        return $this->contactRepository->create($data);
    }

    /**
     * Mark submission as read.
     */
    public function markAsRead(int $id): ?ContactSubmission
    {
        $submission = $this->contactRepository->getById($id);
        if (!$submission) {
            return null;
        }
        return $this->contactRepository->markAsRead($submission);
    }

    /**
     * Mark submission as unread.
     */
    public function markAsUnread(int $id): ?ContactSubmission
    {
        $submission = $this->contactRepository->getById($id);
        if (!$submission) {
            return null;
        }
        return $this->contactRepository->markAsUnread($submission);
    }

    /**
     * Reply to submission.
     */
    public function replyToSubmission(int $id, string $reply): ?ContactSubmission
    {
        $submission = $this->contactRepository->getById($id);
        if (!$submission) {
            return null;
        }
        return $this->contactRepository->reply($submission, $reply);
    }

    /**
     * Delete a submission.
     */
    public function deleteSubmission(int $id): bool
    {
        return $this->contactRepository->delete($id);
    }

    /**
     * Get unread submissions count.
     */
    public function getUnreadCount(): int
    {
        return $this->contactRepository->getUnreadCount();
    }
}
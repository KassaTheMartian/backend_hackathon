<?php

namespace App\Repositories\Eloquent;

use App\Models\ContactSubmission;
use App\Repositories\Contracts\ContactRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ContactRepository extends BaseRepository implements ContactRepositoryInterface
{
    public function __construct(ContactSubmission $model)
    {
        parent::__construct($model);
    }

    protected function allowedIncludes(): array
    {
        return [];
    }

    public function getWithFilters(array $filters = []): LengthAwarePaginator
    {
        $query = $this->query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['subject'])) {
            $query->where('subject', 'like', '%' . $filters['subject'] . '%');
        }

        if (isset($filters['email'])) {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }

        $query->orderBy('created_at', 'desc');

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function markAsRead(ContactSubmission $submission): ContactSubmission
    {
        $submission->update(['status' => 'read']);
        return $submission;
    }

    public function markAsUnread(ContactSubmission $submission): ContactSubmission
    {
        $submission->update(['status' => 'unread']);
        return $submission;
    }

    public function reply(ContactSubmission $submission, string $reply): ContactSubmission
    {
        $submission->update([
            'reply' => $reply,
            'status' => 'replied',
            'replied_at' => now()
        ]);
        return $submission;
    }

    public function getUnreadCount(): int
    {
        return $this->query()->where('status', 'unread')->count();
    }
}

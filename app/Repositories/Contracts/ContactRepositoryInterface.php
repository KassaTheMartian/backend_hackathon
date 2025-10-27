<?php

namespace App\Repositories\Contracts;

use App\Models\ContactSubmission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ContactRepositoryInterface extends BaseRepositoryInterface
{
    public function getWithFilters(array $filters = []): LengthAwarePaginator;
    public function markAsRead(ContactSubmission $submission): ContactSubmission;
    public function markAsUnread(ContactSubmission $submission): ContactSubmission;
    public function reply(ContactSubmission $submission, string $reply): ContactSubmission;
    public function getUnreadCount(): int;
}


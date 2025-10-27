<?php

namespace App\Services;

use App\Models\ContactSubmission;
use Illuminate\Support\Facades\Mail;

class ContactService
{
    /**
     * Create a new contact submission.
     */
    public function createSubmission(array $data): ContactSubmission
    {
        $submission = ContactSubmission::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Send notification email to admin
        // Mail::to(config('mail.admin_email'))->send(new ContactFormNotification($submission));

        return $submission;
    }
}

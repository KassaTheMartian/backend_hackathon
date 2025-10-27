<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\StoreContactRequest;
use App\Services\ContactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function __construct(
        private ContactService $contactService
    ) {}

    /**
     * Store a newly created contact submission.
     */
    public function store(StoreContactRequest $request): JsonResponse
    {
        $submission = $this->contactService->createSubmission(data: $request->validated());
        
        return $this->created([
            'id' => $submission->id,
            'reference_code' => 'CT' . date('Ymd') . str_pad($submission->id, 3, '0', STR_PAD_LEFT),
        ], 'Thank you for contacting us. We will respond soon.');
    }
}
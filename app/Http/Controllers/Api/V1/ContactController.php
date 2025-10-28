<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\StoreContactRequest;
use App\Http\Requests\Contact\UpdateContactRequest;
use App\Http\Requests\Contact\ReplyContactRequest;
use App\Http\Resources\Contact\ContactResource;
use App\Models\ContactSubmission;
use App\Services\Contracts\ContactServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Create a new ContactController instance.
     *
     * @param ContactServiceInterface $service The contact service
     */
    public function __construct(private readonly ContactServiceInterface $service)
    {
        //
    }

    /**
     * @OA\Post(
     *     path="/api/v1/contacts",
     *     summary="Create contact submission",
     *     tags={"Contacts"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "subject", "message"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="subject", type="string"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="type", type="string", enum={"general", "complaint", "suggestion", "support"})
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=422, description="Validation Error", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Store a newly created contact submission.
     *
     * @param StoreContactRequest $request The store contact request
     * @return JsonResponse The created contact submission response
     */
    public function store(StoreContactRequest $request): JsonResponse
    {
        $submission = $this->service->createSubmission($request->validated());
        
        return $this->created([
            'id' => $submission->id,
            'reference_code' => 'CT' . date('Ymd') . str_pad($submission->id, 3, '0', STR_PAD_LEFT),
        ], 'Thank you for contacting us. We will respond soon.');
    }
}

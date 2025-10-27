<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\StoreContactRequest;
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
     * @OA\Get(
     *     path="/api/v1/contacts",
     *     summary="List contact submissions",
     *     tags={"Contacts"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="status", in="query", @OA\Schema(type="string", enum={"read", "unread"})),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Display a listing of contact submissions.
     *
     * @param Request $request The HTTP request
     * @return JsonResponse The paginated list of contact submissions
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ContactSubmission::class);
        
        $submissions = $this->service->getSubmissions($request->all());
        $items = $submissions->through(fn ($model) => ContactResource::make($model));
        
        return $this->paginated($items, 'Contact submissions retrieved successfully');
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

    /**
     * @OA\Get(
     *     path="/api/v1/contacts/{id}",
     *     summary="Get contact submission by id",
     *     tags={"Contacts"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Display the specified contact submission.
     *
     * @param int $id The contact submission ID
     * @return JsonResponse The contact submission response
     */
    public function show(int $id): JsonResponse
    {
        $submission = $this->service->getSubmissionById($id);
        if (!$submission) {
            $this->notFound('Contact submission');
        }
        
        $this->authorize('view', $submission);
        
        return $this->ok(ContactResource::make($submission), 'Contact submission retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/contacts/{id}",
     *     summary="Update contact submission",
     *     tags={"Contacts"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", enum={"read", "unread"}),
     *             @OA\Property(property="priority", type="string", enum={"low", "medium", "high"}),
     *             @OA\Property(property="notes", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=422, description="Validation Error", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Update the specified contact submission.
     *
     * @param Request $request The update contact request
     * @param int $id The contact submission ID
     * @return JsonResponse The updated contact submission response
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'nullable|string|in:read,unread',
            'priority' => 'nullable|string|in:low,medium,high',
            'notes' => 'nullable|string|max:1000'
        ]);
        
        $submission = $this->service->getSubmissionById($id);
        if (!$submission) {
            $this->notFound('Contact submission');
        }
        
        $this->authorize('update', $submission);
        
        $data = $request->validated();
        
        if (isset($data['status']) && $data['status'] === 'read') {
            $submission = $this->service->markAsRead($id);
        } elseif (isset($data['status']) && $data['status'] === 'unread') {
            $submission = $this->service->markAsUnread($id);
        }
        
        return $this->ok(ContactResource::make($submission), 'Contact submission updated successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/contacts/{id}/reply",
     *     summary="Reply to contact submission",
     *     tags={"Contacts"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"reply"},
     *             @OA\Property(property="reply", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=422, description="Validation Error", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Reply to the specified contact submission.
     *
     * @param Request $request The reply contact request
     * @param int $id The contact submission ID
     * @return JsonResponse The reply response
     */
    public function reply(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'reply' => 'required|string|max:2000'
        ]);
        
        $submission = $this->service->getSubmissionById($id);
        if (!$submission) {
            $this->notFound('Contact submission');
        }
        
        $this->authorize('update', $submission);
        
        $submission = $this->service->replyToSubmission($id, $request->reply);
        
        return $this->ok(ContactResource::make($submission), 'Reply sent successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/contacts/{id}",
     *     summary="Delete contact submission",
     *     tags={"Contacts"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="No Content"),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Remove the specified contact submission from storage.
     *
     * @param int $id The contact submission ID
     * @return JsonResponse The deletion response
     */
    public function destroy(int $id): JsonResponse
    {
        $submission = $this->service->getSubmissionById($id);
        if (!$submission) {
            $this->notFound('Contact submission');
        }
        
        $this->authorize('delete', $submission);
        
        $deleted = $this->service->deleteSubmission($id);
        return $this->noContent('Contact submission deleted successfully');
    }
}
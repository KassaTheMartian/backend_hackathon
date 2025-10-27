<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Services\Contracts\ProfileServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Create a new ProfileController instance.
     *
     * @param ProfileServiceInterface $service The profile service
     */
    public function __construct(private readonly ProfileServiceInterface $service)
    {
        //
    }

    /**
     * @OA\Get(
     *     path="/api/v1/profile",
     *     summary="Get user profile",
     *     tags={"Profile"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Get the user's profile.
     *
     * @param Request $request The HTTP request
     * @return JsonResponse The user profile response
     */
    public function show(Request $request): JsonResponse
    {
        $user = $this->service->getProfile($request->user()->id);
        
        if (!$user) {
            $this->notFound('User');
        }
        
        $this->authorize('view', $user);
        
        return $this->ok(UserResource::make($user), 'Profile retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/profile",
     *     summary="Update user profile",
     *     tags={"Profile"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="date_of_birth", type="string", format="date"),
     *             @OA\Property(property="gender", type="string", enum={"male", "female", "other"}),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="city", type="string"),
     *             @OA\Property(property="country", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=422, description="Validation Error", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Update the user's profile.
     *
     * @param UpdateProfileRequest $request The update profile request
     * @return JsonResponse The updated profile response
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->service->updateProfile($request->user()->id, $request->validated());
        
        if (!$user) {
            $this->notFound('User');
        }
        
        $this->authorize('update', $user);
        
        return $this->ok(UserResource::make($user), 'Profile updated successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/profile/avatar",
     *     summary="Update user avatar",
     *     tags={"Profile"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="avatar", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=422, description="Validation Error", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Update the user's avatar.
     *
     * @param Request $request The update avatar request
     * @return JsonResponse The updated profile response
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        
        $user = $this->service->updateAvatar($request->user()->id, $request->file('avatar'));
        
        if (!$user) {
            $this->notFound('User');
        }
        
        $this->authorize('update', $user);
        
        return $this->ok(UserResource::make($user), 'Avatar updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/profile/avatar",
     *     summary="Delete user avatar",
     *     tags={"Profile"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Delete the user's avatar.
     *
     * @param Request $request The HTTP request
     * @return JsonResponse The updated profile response
     */
    public function deleteAvatar(Request $request): JsonResponse
    {
        $user = $this->service->deleteAvatar($request->user()->id);
        
        if (!$user) {
            $this->notFound('User');
        }
        
        $this->authorize('update', $user);
        
        return $this->ok(UserResource::make($user), 'Avatar deleted successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/profile/change-password",
     *     summary="Change user password",
     *     tags={"Profile"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password", "new_password", "new_password_confirmation"},
     *             @OA\Property(property="current_password", type="string"),
     *             @OA\Property(property="new_password", type="string"),
     *             @OA\Property(property="new_password_confirmation", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=422, description="Validation Error", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Change the user's password.
     *
     * @param ChangePasswordRequest $request The change password request
     * @return JsonResponse The password change response
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $success = $this->service->changePassword(
            $request->user()->id,
            $validated['current_password'],
            $validated['new_password']
        );
        
        if (!$success) {
            return $this->ok(null, 'Current password is incorrect');
        }
        
        return $this->ok(null, 'Password changed successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/profile/language",
     *     summary="Update user language preference",
     *     tags={"Profile"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"language"},
     *             @OA\Property(property="language", type="string", enum={"vi", "en"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=422, description="Validation Error", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Update the user's language preference.
     *
     * @param Request $request The update language request
     * @return JsonResponse The updated profile response
     */
    public function updateLanguage(Request $request): JsonResponse
    {
        $request->validate([
            'language' => 'required|string|in:vi,en'
        ]);
        
        $user = $this->service->updateLanguagePreference($request->user()->id, $request->language);
        
        if (!$user) {
            $this->notFound('User');
        }
        
        $this->authorize('update', $user);
        
        return $this->ok(UserResource::make($user), 'Language preference updated successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/profile/stats",
     *     summary="Get user statistics",
     *     tags={"Profile"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Get user's statistics.
     *
     * @param Request $request The HTTP request
     * @return JsonResponse The user statistics response
     */
    public function stats(Request $request): JsonResponse
    {
        $stats = $this->service->getUserStats($request->user()->id);
        
        return $this->ok($stats, 'User statistics retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/profile/deactivate",
     *     summary="Deactivate user account",
     *     tags={"Profile"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Deactivate the user's account.
     *
     * @param Request $request The HTTP request
     * @return JsonResponse The deactivation response
     */
    public function deactivate(Request $request): JsonResponse
    {
        $user = $this->service->deactivateAccount($request->user()->id);
        
        if (!$user) {
            $this->notFound('User');
        }
        
        $this->authorize('update', $user);
        
        return $this->ok(UserResource::make($user), 'Account deactivated successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/profile/reactivate",
     *     summary="Reactivate user account",
     *     tags={"Profile"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Reactivate the user's account.
     *
     * @param Request $request The HTTP request
     * @return JsonResponse The reactivation response
     */
    public function reactivate(Request $request): JsonResponse
    {
        $user = $this->service->reactivateAccount($request->user()->id);
        
        if (!$user) {
            $this->notFound('User');
        }
        
        $this->authorize('update', $user);
        
        return $this->ok(UserResource::make($user), 'Account reactivated successfully');
    }
}
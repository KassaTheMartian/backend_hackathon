<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Resources\User\UserResource;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private ProfileService $profileService
    ) {}

    /**
     * Get the user's profile.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $this->profileService->getProfile($request->user()->id);
        
        if (!$user) {
            $this->notFound('User');
        }
        
        return $this->ok(new UserResource($user));
    }

    /**
     * Update the user's profile.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->profileService->updateProfile($request->user()->id, $request->validated());
        
        if (!$user) {
            $this->notFound('User');
        }
        
        return $this->ok(new UserResource($user), 'Profile updated successfully');
    }

    /**
     * Change the user's password.
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $success = $this->profileService->changePassword(
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
     * Get user's statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $stats = $this->profileService->getUserStats($request->user()->id);
        
        return $this->ok($stats);
    }
}
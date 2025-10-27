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
        $user = $this->profileService->getUserProfile($request->user());
        
        return $this->ok(new UserResource($user));
    }

    /**
     * Update the user's profile.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->profileService->updateProfile($request->user(), $request->validated());
        
        return $this->ok(new UserResource($user), 'Profile updated successfully');
    }

    /**
     * Change the user's password.
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->profileService->changePassword($request->user(), $request->validated());
        
        return $this->ok(null, 'Password changed successfully');
    }

    /**
     * Get user's promotions.
     */
    public function promotions(Request $request): JsonResponse
    {
        $promotions = $this->profileService->getUserPromotions($request->user());
        
        return $this->ok($promotions);
    }
}
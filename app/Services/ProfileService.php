<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * Get user profile.
     */
    public function getProfile(int $userId): ?User
    {
        return $this->userRepository->getById($userId);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(User $user, array $data): User
    {
        return $this->userRepository->update($user, $data);
    }

    /**
     * Update user avatar.
     */
    public function updateAvatar(User $user, UploadedFile $file): User
    {
        // Delete old avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Store new avatar
        $path = $file->store('avatars', 'public');
        
        return $this->userRepository->update($user, ['avatar' => $path]);
    }

    /**
     * Delete user avatar.
     */
    public function deleteAvatar(User $user): User
    {
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
        
        return $this->userRepository->update($user, ['avatar' => null]);
    }

    /**
     * Change password.
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        if (!password_verify($currentPassword, $user->password)) {
            return false;
        }

        $this->userRepository->update($user, [
            'password' => bcrypt($newPassword)
        ]);

        return true;
    }

    /**
     * Update language preference.
     */
    public function updateLanguagePreference(User $user, string $language): User
    {
        return $this->userRepository->update($user, ['language_preference' => $language]);
    }

    /**
     * Deactivate account.
     */
    public function deactivateAccount(User $user): User
    {
        return $this->userRepository->update($user, ['is_active' => false]);
    }

    /**
     * Reactivate account.
     */
    public function reactivateAccount(User $user): User
    {
        return $this->userRepository->update($user, ['is_active' => true]);
    }

    /**
     * Get user statistics.
     */
    public function getUserStats(User $user): array
    {
        return [
            'total_bookings' => $user->bookings()->count(),
            'completed_bookings' => $user->bookings()->where('status', 'completed')->count(),
            'total_reviews' => $user->reviews()->count(),
            'member_since' => $user->created_at->format('Y-m-d'),
            'last_login' => $user->last_login_at?->format('Y-m-d H:i:s'),
        ];
    }
}
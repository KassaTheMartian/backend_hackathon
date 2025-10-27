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
    public function updateProfile(int $userId, array $data): ?User
    {
        return $this->userRepository->update($userId, $data);
    }

    /**
     * Update user avatar.
     */
    public function updateAvatar(int $userId, UploadedFile $file): ?User
    {
        $user = $this->userRepository->getById($userId);
        if (!$user) {
            return null;
        }

        // Delete old avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Store new avatar
        $path = $file->store('avatars', 'public');
        
        return $this->userRepository->update($userId, ['avatar' => $path]);
    }

    /**
     * Delete user avatar.
     */
    public function deleteAvatar(int $userId): ?User
    {
        $user = $this->userRepository->getById($userId);
        if (!$user) {
            return null;
        }

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
        
        return $this->userRepository->update($userId, ['avatar' => null]);
    }

    /**
     * Change password.
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        $user = $this->userRepository->getById($userId);
        if (!$user) {
            return false;
        }

        if (!password_verify($currentPassword, $user->password)) {
            return false;
        }

        $this->userRepository->update($userId, [
            'password' => bcrypt($newPassword)
        ]);

        return true;
    }

    /**
     * Update language preference.
     */
    public function updateLanguagePreference(int $userId, string $language): ?User
    {
        return $this->userRepository->update($userId, ['language_preference' => $language]);
    }

    /**
     * Deactivate account.
     */
    public function deactivateAccount(int $userId): ?User
    {
        return $this->userRepository->update($userId, ['is_active' => false]);
    }

    /**
     * Reactivate account.
     */
    public function reactivateAccount(int $userId): ?User
    {
        return $this->userRepository->update($userId, ['is_active' => true]);
    }

    /**
     * Get user statistics.
     */
    public function getUserStats(int $userId): ?array
    {
        $user = $this->userRepository->getById($userId);
        if (!$user) {
            return null;
        }

        return [
            'total_bookings' => $user->bookings()->count(),
            'completed_bookings' => $user->bookings()->where('status', 'completed')->count(),
            'total_reviews' => $user->reviews()->count(),
            'member_since' => $user->created_at->format('Y-m-d'),
            'last_login' => $user->last_login_at?->format('Y-m-d H:i:s'),
        ];
    }
}
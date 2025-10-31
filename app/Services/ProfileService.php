<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\PromotionRepositoryInterface;
use App\Services\Contracts\ProfileServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Service for handling user profile operations.
 *
 * Manages profile updates, avatar management, password changes, and user statistics.
 */
class ProfileService implements ProfileServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PromotionRepositoryInterface $promotionRepository
    ) {}

    public function getProfile(int $userId): ?User
    {
        return $this->userRepository->getById($userId);
    }

    public function updateProfile(int $userId, array $data): ?User
    {
        return $this->userRepository->update($userId, $data);
    }

    public function updateAvatar(int $userId, UploadedFile $file): ?User
    {
        $user = $this->userRepository->getById($userId);
        if (!$user) {
            return null;
        }

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $file->store('avatars', 'public');
        
        return $this->userRepository->update($userId, ['avatar' => $path]);
    }

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

    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        $user = $this->userRepository->getById($userId);
        if (!$user) {
            return false;
        }

        if (!password_verify($currentPassword, $user->password)) {
            return false;
        }

        $this->userRepository->update($userId, ['password' => bcrypt($newPassword)]);
        return true;
    }

    public function updateLanguagePreference(int $userId, string $language): ?User
    {
        return $this->userRepository->update($userId, ['language_preference' => $language]);
    }

    public function deactivateAccount(int $userId): ?User
    {
        return $this->userRepository->update($userId, ['is_active' => false]);
    }

    public function reactivateAccount(int $userId): ?User
    {
        return $this->userRepository->update($userId, ['is_active' => true]);
    }

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

    public function getUserPromotions(int $userId): Collection
    {
        $user = $this->userRepository->getById($userId);
        if (!$user instanceof User) {
            return new Collection();
        }

        return $this->promotionRepository->getUserPromotions($user);
    }
}

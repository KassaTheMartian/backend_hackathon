<?php

namespace App\Services\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface ProfileServiceInterface
{
    /**
     * Get user profile.
     *
     * @param int $userId The user ID.
     * @return User|null
     */
    public function getProfile(int $userId): ?User;

    /**
     * Update user profile.
     *
     * @param int $userId The user ID.
     * @param array $data The profile data.
     * @return User|null
     */
    public function updateProfile(int $userId, array $data): ?User;

    /**
     * Update user avatar.
     *
     * @param int $userId The user ID.
     * @param UploadedFile $file The avatar file.
     * @return User|null
     */
    public function updateAvatar(int $userId, UploadedFile $file): ?User;

    /**
     * Delete user avatar.
     *
     * @param int $userId The user ID.
     * @return User|null
     */
    public function deleteAvatar(int $userId): ?User;

    /**
     * Change user password.
     *
     * @param int $userId The user ID.
     * @param string $currentPassword The current password.
     * @param string $newPassword The new password.
     * @return bool
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool;

    /**
     * Update language preference.
     *
     * @param int $userId The user ID.
     * @param string $language The language.
     * @return User|null
     */
    public function updateLanguagePreference(int $userId, string $language): ?User;

    /**
     * Deactivate user account.
     *
     * @param int $userId The user ID.
     * @return User|null
     */
    public function deactivateAccount(int $userId): ?User;

    /**
     * Reactivate user account.
     *
     * @param int $userId The user ID.
     * @return User|null
     */
    public function reactivateAccount(int $userId): ?User;

    /**
     * Get user stats.
     *
     * @param int $userId The user ID.
     * @return array|null
     */
    public function getUserStats(int $userId): ?array;

    /**
     * Get user promotions.
     *
     * @param int $userId The user ID.
     * @return Collection
     */
    public function getUserPromotions(int $userId): Collection;
}

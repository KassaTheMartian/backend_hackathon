<?php

namespace App\Services\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface ProfileServiceInterface
{
    public function getProfile(int $userId): ?User;
    public function updateProfile(int $userId, array $data): ?User;
    public function updateAvatar(int $userId, UploadedFile $file): ?User;
    public function deleteAvatar(int $userId): ?User;
    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool;
    public function updateLanguagePreference(int $userId, string $language): ?User;
    public function deactivateAccount(int $userId): ?User;
    public function reactivateAccount(int $userId): ?User;
    public function getUserStats(int $userId): ?array;
    public function getUserPromotions(int $userId): Collection;
}

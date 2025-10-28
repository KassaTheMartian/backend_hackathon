<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

interface AuthRepositoryInterface
{
    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find user by ID
     */
    public function findById(int $userId): ?User;

    /**
     * Create a new user
     */
    public function create(array $attributes): User;

    /**
     * Update user by ID
     */
    public function update(int $userId, array $attributes): bool;

    /**
     * Create a new token for user
     */
    public function createToken(User $user, string $name = 'api'): string;

    /**
     * Revoke current token
     */
    public function revokeCurrentToken(User $user): bool;

    /**
     * Revoke all tokens for user
     */
    public function revokeAllTokens(User $user): bool;

    /**
     * Create password reset token
     */
    public function createPasswordResetToken(string $email): string;

    /**
     * Find password reset token
     */
    public function findPasswordResetToken(string $token): ?array;

    /**
     * Delete password reset token
     */
    public function deletePasswordResetToken(string $email): bool;
}

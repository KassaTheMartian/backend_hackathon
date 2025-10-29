<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

interface AuthRepositoryInterface
{
    /**
     * Find user by email.
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find user by ID.
     *
     * @param int $userId
     * @return User|null
     */
    public function findById(int $userId): ?User;

    /**
     * Create a new user.
     *
     * @param array $attributes
     * @return User
     */
    public function create(array $attributes): User;

    /**
     * Update user by ID.
     *
     * @param int $userId
     * @param array $attributes
     * @return bool
     */
    public function update(int $userId, array $attributes): bool;

    /**
     * Create a new token for user.
     *
     * @param User $user
     * @param string $name
     * @return string
     */
    public function createToken(User $user, string $name = 'api'): string;

    /**
     * Revoke current token.
     *
     * @param User $user
     * @return bool
     */
    public function revokeCurrentToken(User $user): bool;

    /**
     * Revoke all tokens for user.
     *
     * @param User $user
     * @return bool
     */
    public function revokeAllTokens(User $user): bool;

    /**
     * Create password reset token.
     *
     * @param string $email
     * @return string
     */
    public function createPasswordResetToken(string $email): string;

    /**
     * Find password reset token.
     *
     * @param string $token
     * @return array|null
     */
    public function findPasswordResetToken(string $token): ?array;

    /**
     * Delete password reset token.
     *
     * @param string $email
     * @return bool
     */
    public function deletePasswordResetToken(string $email): bool;
}

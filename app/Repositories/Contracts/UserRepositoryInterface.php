<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get user by email.
     *
     * @param string $email
     * @return User|null
     */
    public function getByEmail(string $email): ?User;

    /**
     * Find user by phone.
     *
     * @param string $phone
     * @return User|null
     */
    public function findByPhone(string $phone): ?User;

    /**
     * Verify user's email.
     *
     * @param User $user
     * @return User
     */
    public function verifyEmail(User $user): User;

    /**
     * Verify user's phone.
     *
     * @param User $user
     * @return User
     */
    public function verifyPhone(User $user): User;
}


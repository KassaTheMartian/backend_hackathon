<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function getByEmail(string $email): ?User;
    public function findByPhone(string $phone): ?User;
    public function verifyEmail(User $user): User;
    public function verifyPhone(User $user): User;
}


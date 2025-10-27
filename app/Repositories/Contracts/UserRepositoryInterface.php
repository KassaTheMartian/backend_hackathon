<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface
{
    public function getById(int $id): ?User;
    public function getByEmail(string $email): ?User;
    public function create(array $data): User;
    public function update(User $user, array $data): User;
    public function delete(User $user): bool;
    public function findByPhone(string $phone): ?User;
    public function verifyEmail(User $user): User;
    public function verifyPhone(User $user): User;
}


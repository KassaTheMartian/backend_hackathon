<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function getById(int $id): ?User
    {
        return $this->model->find($id);
    }

    public function getByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByPhone(string $phone): ?User
    {
        return $this->model->where('phone', $phone)->first();
    }

    public function verifyEmail(User $user): User
    {
        $user->update(['email_verified_at' => now()]);
        return $user;
    }

    public function verifyPhone(User $user): User
    {
        $user->update(['phone_verified_at' => now()]);
        return $user;
    }
}

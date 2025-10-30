<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

/**
 * Class UserRepository
 */
class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * Create a new repository instance.
     *
     * @param User $model
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Get allowed includes for eager loading.
     *
     * @return array
     */
    protected function allowedIncludes(): array
    {
        return ['bookings', 'reviews', 'promotions'];
    }

    /**
     * Get user by email.
     *
     * @param string $email
     * @return User|null
     */
    public function getByEmail(string $email): ?User
    {
        return $this->query()->where('email', $email)->first();
    }

    /**
     * Find user by phone.
     *
     * @param string $phone
     * @return User|null
     */
    public function findByPhone(string $phone): ?User
    {
        return $this->query()->where('phone', $phone)->first();
    }

    /**
     * Verify user's email.
     *
     * @param User $user
     * @return User
     */
    public function verifyEmail(User $user): User
    {
        $user->update(['email_verified_at' => now()]);
        return $user;
    }

    /**
     * Verify user's phone.
     *
     * @param User $user
     * @return User
     */
    public function verifyPhone(User $user): User
    {
        $user->update(['phone_verified_at' => now()]);
        return $user;
    }
}

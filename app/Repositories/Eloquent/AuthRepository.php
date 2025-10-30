<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\AuthRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Class AuthRepository
 */
class AuthRepository implements AuthRepositoryInterface
{
    /**
     * Find user by email address.
     *
     * @param string $email The user's email address
     * @return User|null The user if found, null otherwise
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Create a new user.
     *
     * @param array $attributes The user attributes (password should already be hashed)
     * @return User The created user
     */
    public function create(array $attributes): User
    {
        return User::create($attributes);
    }

    /**
     * Update user by ID.
     *
     * @param int $userId The user ID
     * @param array $attributes The attributes to update
     * @return bool True if update was successful
     */
    public function update(int $userId, array $attributes): bool
    {
        return User::where('id', $userId)->update($attributes);
    }

    /**
     * Find user by ID.
     *
     * @param int $userId The user ID
     * @return User|null The user if found, null otherwise
     */
    public function findById(int $userId): ?User
    {
        return User::find($userId);
    }

    /**
     * Create a new token for user.
     *
     * @param User $user The user to create token for
     * @param string $name The token name (default: 'api')
     * @return string The plain text token
     */
    public function createToken(User $user, string $name = 'api'): string
    {
        return $user->createToken($name)->plainTextToken;
    }

    /**
     * Revoke current token.
     *
     * @param User $user The user whose current token to revoke
     * @return bool True if token was revoked, false otherwise
     */
    public function revokeCurrentToken(User $user): bool
    {
        $token = $user->currentAccessToken();
        if ($token) {
            return $token->delete();
        }
        return false;
    }

    /**
     * Revoke all tokens for user.
     *
     * @param User $user The user whose tokens to revoke
     * @return bool True if tokens were revoked, false otherwise
     */
    public function revokeAllTokens(User $user): bool
    {
        return $user->tokens()->delete() > 0;
    }

    /**
     * Create password reset token.
     *
     * @param string $email The user's email address
     * @return string The plain text reset token
     */
    public function createPasswordResetToken(string $email): string
    {
        $token = Str::random(64);
        
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'email' => $email,
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        return $token;
    }

    /**
     * Find password reset token.
     *
     * @param string $token The plain text reset token
     * @return array|null The password reset record if found and valid, null otherwise
     */
    public function findPasswordResetToken(string $token): ?array
    {
        $passwordReset = DB::table('password_reset_tokens')
            ->where('created_at', '>', now()->subMinutes(60)) // Token expires in 60 minutes
            ->get()
            ->first(function ($record) use ($token) {
                return Hash::check($token, $record->token);
            });

        return $passwordReset ? (array) $passwordReset : null;
    }

    /**
     * Delete password reset token.
     *
     * @param string $email The user's email address
     * @return bool True if token was deleted, false otherwise
     */
    public function deletePasswordResetToken(string $email): bool
    {
        return DB::table('password_reset_tokens')
            ->where('email', $email)
            ->delete() > 0;
    }
}

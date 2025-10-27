<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\AuthRepositoryInterface;
use App\Services\Contracts\AuthServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthService implements AuthServiceInterface
{
    /**
     * Create a new AuthService instance.
     *
     * @param AuthRepositoryInterface $authRepository The authentication repository
     */
    public function __construct(private readonly AuthRepositoryInterface $authRepository)
    {
    }

    /**
     * Authenticate user with email and password.
     *
     * @param string $email The user's email address
     * @param string $password The user's password
     * @return array The authentication response with token and user data
     * @throws \Exception When credentials are invalid
     */
    public function login(string $email, string $password): array
    {
        $user = $this->authRepository->findByEmail($email);
        
        if (!$user || !Hash::check($password, $user->password)) {
            throw new \Exception('Invalid credentials');
        }

        $token = $this->authRepository->createToken($user);

        return [
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ];
    }

    /**
     * Register a new user.
     *
     * @param array $userData The user data containing name, email, and password
     * @return array The registration response with token and user data
     */
    public function register(array $userData): array
    {
        $user = $this->authRepository->create($userData);
        $token = $this->authRepository->createToken($user);

        return [
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ];
    }

    /**
     * Get the current authenticated user.
     *
     * @return User|null The current authenticated user or null if not authenticated
     */
    public function getCurrentUser(): ?User
    {
        return Auth::user();
    }

    /**
     * Logout current user (revoke current token).
     *
     * @return bool True if logout was successful, false otherwise
     */
    public function logout(): bool
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }

        return $this->authRepository->revokeCurrentToken($user);
    }

    /**
     * Logout from all devices (revoke all tokens).
     *
     * @return bool True if logout was successful, false otherwise
     */
    public function logoutAll(): bool
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }

        return $this->authRepository->revokeAllTokens($user);
    }

    /**
     * Send password reset link to user email.
     *
     * @param string $email The user's email address
     * @return array The response containing reset token (for demo purposes)
     * @throws \Exception When user is not found
     */
    public function sendPasswordResetLink(string $email): array
    {
        $user = $this->authRepository->findByEmail($email);
        if (!$user) {
            throw new \Exception('User not found');
        }

        $token = $this->authRepository->createPasswordResetToken($email);
        
        // In a real application, you would send an email here
        // For demo purposes, we'll just return the token
        // Mail::to($email)->send(new PasswordResetMail($token));

        return [
            'message' => 'Password reset link sent to your email',
            'token' => $token, // Remove this in production
        ];
    }

    /**
     * Reset password with token.
     *
     * @param string $token The password reset token
     * @param string $email The user's email address
     * @param string $password The new password
     * @return array The password reset response
     * @throws \Exception When token is invalid or user is not found
     */
    public function resetPassword(string $token, string $email, string $password): array
    {
        $passwordReset = $this->authRepository->findPasswordResetToken($token);
        if (!$passwordReset || $passwordReset['email'] !== $email) {
            throw new \Exception('Invalid or expired reset token');
        }

        $user = $this->authRepository->findByEmail($email);
        if (!$user) {
            throw new \Exception('User not found');
        }

        // Update password
        $user->password = Hash::make($password);
        $user->save();

        // Delete the reset token
        $this->authRepository->deletePasswordResetToken($email);

        // Revoke all existing tokens for security
        $this->authRepository->revokeAllTokens($user);

        return [
            'message' => 'Password reset successfully',
        ];
    }
}

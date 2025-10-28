<?php

namespace App\Services\Contracts;

use App\Models\User;

interface AuthServiceInterface
{
    /**
     * Authenticate user with email and password
     */
    public function login(string $email, string $password): array;

    /**
     * Register a new user
     */
    public function register(array $userData): array;

    /**
     * Get current authenticated user
     */
    public function getCurrentUser(): ?User;

    /**
     * Logout current user (revoke current token)
     */
    public function logout(): bool;

    /**
     * Logout from all devices (revoke all tokens)
     */
    public function logoutAll(): bool;

    /**
     * Send password reset link to user email
     */
    public function sendPasswordResetLink(string $email): array;

    /**
     * Reset password with token
     */
    public function resetPassword(string $token, string $email, string $password): array;

    /**
     * Send OTP to email for verification.
     */
    public function sendEmailOtp(string $email, string $purpose = 'verify_email'): array;

    /**
     * Verify OTP and mark email as verified.
     */
    public function verifyEmailOtp(string $email, string $otp, string $purpose = 'verify_email'): array;

    /**
     * Send OTP for password reset.
     */
    public function sendPasswordResetOtp(string $email): array;

    /**
     * Reset password using OTP.
     */
    public function resetPasswordWithOtp(string $email, string $otp, string $password): array;
}

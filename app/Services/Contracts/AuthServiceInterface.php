<?php

namespace App\Services\Contracts;

use App\Models\User;

interface AuthServiceInterface
{
    /**
     * Authenticate user with email and password.
     *
     * @param string $email The user email.
     * @param string $password The user password.
     * @return array
     */
    public function login(string $email, string $password): array;

    /**
     * Register a new user.
     *
     * @param array $userData The user data.
     * @return array
     */
    public function register(array $userData): array;

    /**
     * Get current authenticated user.
     *
     * @return User|null
     */
    public function getCurrentUser(): ?User;

    /**
     * Logout current user (revoke current token).
     *
     * @return bool
     */
    public function logout(): bool;

    /**
     * Logout from all devices (revoke all tokens).
     *
     * @return bool
     */
    public function logoutAll(): bool;

    // Removed legacy token-based reset methods in favor of OTP-only flow

    /**
     * Send OTP to email for verification.
     *
     * @param string $email The email address.
     * @param string $purpose The purpose of the OTP.
     * @return array
     */
    public function sendEmailOtp(string $email, string $purpose = 'verify_email'): array;

    /**
     * Verify OTP and mark email as verified.
     *
     * @param string $email The email address.
     * @param string $otp The OTP code.
     * @param string $purpose The purpose of the OTP.
     * @return array
     */
    public function verifyEmailOtp(string $email, string $otp, string $purpose = 'verify_email'): array;

    /**
     * Send OTP for password reset.
     *
     * @param string $email The email address.
     * @return array
     */
    public function sendPasswordResetOtp(string $email): array;

    /**
     * Reset password using OTP.
     *
     * @param string $email The email address.
     * @param string $otp The OTP code.
     * @param string $password The new password.
     * @return array
     */
    public function resetPasswordWithOtp(string $email, string $otp, string $password): array;

    /**
     * Send a test email.
     *
     * @param string $email The email address.
     * @return array
     */
    public function sendTestEmail(string $email): array;
}

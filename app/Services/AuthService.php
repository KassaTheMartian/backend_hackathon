<?php

namespace App\Services;

use App\Models\User;
use App\Models\OtpVerification;
use App\Mail\OtpMail;
use App\Repositories\Contracts\AuthRepositoryInterface;
use App\Repositories\Contracts\OtpRepositoryInterface;
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
     * @param OtpRepositoryInterface $otpRepository The OTP repository
     */
    public function __construct(
        private readonly AuthRepositoryInterface $authRepository,
        private readonly OtpRepositoryInterface $otpRepository
    ) {
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
        // Database Operation: Find user by email
        $user = $this->authRepository->findByEmail($email);
        
        // Business Logic: Validate credentials
        if (!$user || !Hash::check($password, $user->password)) {
            throw new \Exception(__("auth.invalid_credentials"));
        }

        // Business Logic: Check if user is active
        if (!$user->is_active) {
            throw new \Exception(__("auth.account_inactive"));
        }

        // Business Logic: Require verified email
        if (!$user->email_verified_at) {
            throw new \Exception(__("auth.email_not_verified"));
        }

        // Database Operation: Create authentication token
        $token = $this->authRepository->createToken($user);
        
        // Database Operation: Update last login timestamp
        $this->authRepository->update($user->id, ['last_login_at' => now()]);

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $user->avatar,
                'language_preference' => $user->language_preference ?? 'vi',
                'is_admin' => (bool) $user->is_admin,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
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
        // Business Logic: Hash password
        if (isset($userData['password'])) {
            $userData['password'] = Hash::make($userData['password']);
        }
        
        // Business Logic: Set defaults
        $userData['is_active'] = true;
        $userData['language_preference'] = $userData['language_preference'] ?? 'vi';
        
        // Database Operation: Create user via repository
        $user = $this->authRepository->create($userData);
        
        // Business Logic: Auto send OTP to email
        $this->sendEmailOtp($user->email, 'verify_email');

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'language_preference' => $user->language_preference,
                'email_verified_at' => $user->email_verified_at ? $user->email_verified_at->toISOString() : null,
            ],
            'message' => __("auth.registration_success"),
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
        // Database Operation: Find user
        $user = $this->authRepository->findByEmail($email);
        if (!$user) {
            throw new \Exception(__("auth.user_not_found"));
        }

        // Database Operation: Create password reset token
        $token = $this->authRepository->createPasswordResetToken($email);
        
        // Business Logic: Send email (in production, don't return token)
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
        // Business Logic: Validate reset token
        $passwordReset = $this->authRepository->findPasswordResetToken($token);
        if (!$passwordReset || $passwordReset['email'] !== $email) {
            throw new \Exception(__("auth.invalid_or_expired_token"));
        }

        // Database Operation: Find user
        $user = $this->authRepository->findByEmail($email);
        if (!$user) {
            throw new \Exception(__("auth.user_not_found"));
        }

        // Business Logic: Hash new password and update
        $hashedPassword = Hash::make($password);
        $this->authRepository->update($user->id, ['password' => $hashedPassword]);

        // Database Operation: Delete the reset token
        $this->authRepository->deletePasswordResetToken($email);

        // Business Logic: Revoke all existing tokens for security
        $this->authRepository->revokeAllTokens($user);

        return [
            'message' => __("auth.password_reset_success"),
        ];
    }

    public function sendEmailOtp(string $email, string $purpose = 'verify_email'): array
    {
        // Business Logic: Generate OTP
        $otp = (string)random_int(100000, 999999);
        
        // Database Operation: Store OTP via repository
        $this->otpRepository->create([
            'phone_or_email' => $email,
            'otp' => $otp,
            'type' => 'email',
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0,
        ]);

        // Business Logic: Send email with beautiful template
        Mail::to($email)->send(new OtpMail($otp, $purpose, 10));

        return ['message' => __("auth.otp_sent")];
    }

    public function verifyEmailOtp(string $email, string $otp, string $purpose = 'verify_email'): array
    {
        // Business Logic: Find and validate OTP via repository
        $record = $this->otpRepository->findLatestValid($email, $purpose);

        if (!$record) {
            throw new \Exception(__("auth.otp_not_found"));
        }
        if ($record->isLockedOut()) {
            throw new \Exception(__("auth.otp_locked"));
        }

        if ($record->otp !== $otp) {
            $this->otpRepository->incrementAttempts($record->id);
            throw new \Exception(__("auth.invalid_otp"));
        }

        // Business Logic: Mark OTP as verified via repository
        $this->otpRepository->markAsVerified($record->id);

        // Database Operation: Update email verification status
        $user = $this->authRepository->findByEmail($email);
        if ($user && !$user->email_verified_at) {
            $this->authRepository->update($user->id, ['email_verified_at' => now()]);
        }

        return ['message' => __("auth.email_verified_success")];
    }

    public function sendPasswordResetOtp(string $email): array
    {
        // Database Operation: Ensure user exists
        $user = $this->authRepository->findByEmail($email);
        if (!$user) {
            throw new \Exception(__("auth.user_not_found"));
        }
        
        // Business Logic: Send OTP for password reset
        return $this->sendEmailOtp($email, 'password_reset');
    }

    public function resetPasswordWithOtp(string $email, string $otp, string $password): array
    {
        // Business Logic: Validate OTP via repository
        $record = $this->otpRepository->findLatestValid($email, 'password_reset');

        if (!$record) {
            throw new \Exception('OTP not found or expired');
        }
        if ($record->isLockedOut()) {
            throw new \Exception('Too many invalid attempts. Please request a new OTP.');
        }
        if ($record->otp !== $otp) {
            $this->otpRepository->incrementAttempts($record->id);
            throw new \Exception('Invalid OTP');
        }
        
        // Business Logic: Mark OTP as verified via repository
        $this->otpRepository->markAsVerified($record->id);

        // Database Operation: Find user
        $user = $this->authRepository->findByEmail($email);
        if (!$user) {
            throw new \Exception('User not found');
        }
        
        // Business Logic: Hash new password and update
        $hashedPassword = Hash::make($password);
        $this->authRepository->update($user->id, ['password' => $hashedPassword]);

        // Business Logic: Revoke existing tokens for security
        $this->authRepository->revokeAllTokens($user);

        return ['message' => __("auth.password_reset_success")];
    }

    public function sendTestEmail(string $email): array
    {
        Mail::to($email)->send(new OtpMail('123456', 'verify_email', 10));

        return [
            'message' => __("auth.test_email_sent"),
            'to' => $email,
            'timestamp' => now()->toISOString(),
        ];
    }
}

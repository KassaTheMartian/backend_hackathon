<?php

namespace App\Services;

use App\Models\User;
use App\Models\OtpVerification;
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

        // Check if user is active
        if (!$user->is_active) {
            throw new \Exception('Account is inactive. Please contact support.');
        }

        // Require verified email
        if (!$user->email_verified_at) {
            throw new \Exception('Email not verified. Please verify your email.');
        }

        $token = $this->authRepository->createToken($user);
        
        // Update last login timestamp
        $user->update(['last_login_at' => now()]);

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $user->avatar,
                'language_preference' => $user->language_preference ?? 'vi',
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
        // Hash password if not already hashed
        if (isset($userData['password'])) {
            $userData['password'] = Hash::make($userData['password']);
        }
        
        // Set defaults
        $userData['is_active'] = true;
        $userData['language_preference'] = $userData['language_preference'] ?? 'vi';
        
        $user = $this->authRepository->create($userData);
        // Auto send OTP to email
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
            'message' => 'Registration successful. Please verify your email.',
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

    public function sendEmailOtp(string $email, string $purpose = 'verify_email'): array
    {
        $otp = (string)random_int(100000, 999999);
        OtpVerification::create([
            'phone_or_email' => $email,
            'otp' => $otp,
            'type' => 'email',
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0,
        ]);

        // Send simple email
        Mail::raw('Your verification code is: ' . $otp, function ($message) use ($email) {
            $message->to($email)
                ->subject('Your OTP Code')
                ->from(config('mail.from.address'), config('mail.from.name'));
        });

        return ['message' => 'OTP sent'];
    }

    public function verifyEmailOtp(string $email, string $otp, string $purpose = 'verify_email'): array
    {
        $record = OtpVerification::where('phone_or_email', $email)
            ->where('purpose', $purpose)
            ->unexpired()
            ->unverified()
            ->latest('id')
            ->first();

        if (!$record) {
            throw new \Exception('OTP not found or expired');
        }
        if ($record->isLockedOut()) {
            throw new \Exception('Too many invalid attempts. Please request a new OTP.');
        }

        if ($record->otp !== $otp) {
            $record->incrementAttempts();
            throw new \Exception('Invalid OTP');
        }

        $record->markAsVerified();

        $user = $this->authRepository->findByEmail($email);
        if ($user && !$user->email_verified_at) {
            $user->email_verified_at = now();
            $user->save();
        }

        return ['message' => 'Email verified successfully'];
    }

    public function sendPasswordResetOtp(string $email): array
    {
        // Ensure user exists via repository
        $user = $this->authRepository->findByEmail($email);
        if (!$user) {
            throw new \Exception('User not found');
        }
        return $this->sendEmailOtp($email, 'password_reset');
    }

    public function resetPasswordWithOtp(string $email, string $otp, string $password): array
    {
        $record = OtpVerification::where('phone_or_email', $email)
            ->where('purpose', 'password_reset')
            ->unexpired()
            ->unverified()
            ->latest('id')
            ->first();

        if (!$record) {
            throw new \Exception('OTP not found or expired');
        }
        if ($record->isLockedOut()) {
            throw new \Exception('Too many invalid attempts. Please request a new OTP.');
        }
        if ($record->otp !== $otp) {
            $record->incrementAttempts();
            throw new \Exception('Invalid OTP');
        }
        $record->markAsVerified();

        $user = $this->authRepository->findByEmail($email);
        if (!$user) {
            throw new \Exception('User not found');
        }
        $user->password = Hash::make($password);
        $user->save();

        // Revoke existing tokens
        $this->authRepository->revokeAllTokens($user);

        return ['message' => 'Password reset successfully'];
    }
}

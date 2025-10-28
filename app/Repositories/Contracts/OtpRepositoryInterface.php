<?php

namespace App\Repositories\Contracts;

use App\Models\OtpVerification;

interface OtpRepositoryInterface
{
    /**
     * Create a new OTP verification record
     *
     * @param array $data The OTP data (phone_or_email, otp, type, purpose, expires_at, attempts)
     * @return OtpVerification The created OTP verification record
     */
    public function create(array $data): OtpVerification;

    /**
     * Find the latest unverified and unexpired OTP for a given phone/email and purpose
     *
     * @param string $phoneOrEmail The phone number or email address
     * @param string $purpose The purpose of the OTP (e.g., 'verify_email', 'password_reset')
     * @return OtpVerification|null The OTP record if found, null otherwise
     */
    public function findLatestValid(string $phoneOrEmail, string $purpose): ?OtpVerification;

    /**
     * Mark an OTP as verified
     *
     * @param int $otpId The OTP ID
     * @return bool True if update was successful, false otherwise
     */
    public function markAsVerified(int $otpId): bool;

    /**
     * Increment the attempts count for an OTP
     *
     * @param int $otpId The OTP ID
     * @return bool True if increment was successful, false otherwise
     */
    public function incrementAttempts(int $otpId): bool;

    /**
     * Delete expired OTP records (cleanup)
     *
     * @return int The number of deleted records
     */
    public function deleteExpired(): int;

    /**
     * Delete all OTP records for a given phone/email and purpose
     *
     * @param string $phoneOrEmail The phone number or email address
     * @param string $purpose The purpose of the OTP
     * @return int The number of deleted records
     */
    public function deleteByPhoneOrEmailAndPurpose(string $phoneOrEmail, string $purpose): int;
}

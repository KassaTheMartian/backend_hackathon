<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtpVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone_or_email',
        'otp',
        'type',
        'purpose',
        'expires_at',
        'verified_at',
        'attempts',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'attempts' => 'integer',
    ];

    /**
     * Scope a query to only include unexpired OTPs.
     */
    public function scopeUnexpired($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Scope a query to only include unverified OTPs.
     */
    public function scopeUnverified($query)
    {
        return $query->whereNull('verified_at');
    }

    /**
     * Check if the OTP is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at < now();
    }

    /**
     * Check if the OTP is verified.
     */
    public function isVerified(): bool
    {
        return !is_null($this->verified_at);
    }

    /**
     * Mark the OTP as verified.
     */
    public function markAsVerified(): void
    {
        $this->update(['verified_at' => now()]);
    }

    /**
     * Increment the attempt count.
     */
    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }

    /**
     * Determine if attempts have exceeded a lockout threshold.
     */
    public function isLockedOut(int $maxAttempts = 5): bool
    {
        return $this->attempts >= $maxAttempts;
    }
}
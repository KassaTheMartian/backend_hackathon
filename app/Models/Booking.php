<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_code',
        'user_id',
        'guest_name',
        'guest_email',
        'guest_phone',
        'branch_id',
        'service_id',
        'staff_id',
        'booking_date',
        'booking_time',
        'duration',
        'status',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at',
        'service_price',
        'discount_amount',
        'total_amount',
        'payment_status',
        'payment_method',
        'notes',
        'admin_notes',
        'reminder_sent',
        'confirmation_sent',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'booking_time' => 'datetime:H:i',
        'service_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'cancelled_at' => 'datetime',
        'reminder_sent' => 'boolean',
        'confirmation_sent' => 'boolean',
    ];

    /**
     * Get the user who made the booking.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the branch where the booking is scheduled.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the service for this booking.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the staff member assigned to this booking.
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Get the payment for this booking.
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Get the reviews for this booking.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the status history for this booking.
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(BookingStatusHistory::class);
    }

    /**
     * Scope a query to only include bookings for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include bookings for a specific branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope a query to only include bookings for a specific date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->where('booking_date', $date);
    }

    /**
     * Scope a query to only include bookings with a specific status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if the booking can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']) && 
               $this->booking_date > now()->addHours(2);
    }

    /**
     * Check if the booking can be modified.
     */
    public function canBeModified(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']) && 
               $this->booking_date > now()->addHours(4);
    }

    /**
     * Generate a unique booking code.
     */
    public static function generateBookingCode(): string
    {
        do {
            $code = 'BK' . date('Ymd') . strtoupper(Str::random(5));
        } while (static::where('booking_code', $code)->exists());

        return $code;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (!$booking->booking_code) {
                $booking->booking_code = static::generateBookingCode();
            }
        });
    }
}

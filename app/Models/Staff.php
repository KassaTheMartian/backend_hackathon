<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_id',
        'position',
        'specialization',
        'bio',
        'years_of_experience',
        'certifications',
        'rating',
        'total_reviews',
        'is_active',
    ];

    protected $casts = [
        'specialization' => 'array',
        'bio' => 'array',
        'certifications' => 'array',
        'rating' => 'decimal:2',
        'total_reviews' => 'integer',
        'years_of_experience' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user associated with this staff member.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the branch where this staff member works.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the services this staff member can provide.
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'staff_services');
    }

    /**
     * Get the bookings assigned to this staff member.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the reviews for this staff member.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Scope a query to only include active staff.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope a query to filter by service.
     */
    public function scopeForService($query, $serviceId)
    {
        return $query->whereHas('services', function ($q) use ($serviceId) {
            $q->where('services.id', $serviceId);
        });
    }

    /**
     * Update rating when a new review is added.
     */
    public function updateRating(): void
    {
        $reviews = $this->reviews()->where('is_approved', true);
        $this->total_reviews = $reviews->count();
        $this->rating = $reviews->avg('rating') ?? 0;
        $this->save();
    }

    /**
     * Accessor for name - forwards to user relationship
     */
    public function getNameAttribute(): ?string
    {
        return $this->user?->name;
    }

    /**
     * Accessor for email - forwards to user relationship
     */
    public function getEmailAttribute(): ?string
    {
        return $this->user?->email;
    }

    /**
     * Accessor for phone - forwards to user relationship
     */
    public function getPhoneAttribute(): ?string
    {
        return $this->user?->phone;
    }

    /**
     * Accessor for avatar - forwards to user relationship
     */
    public function getAvatarAttribute(): ?string
    {
        return $this->user?->avatar;
    }
}

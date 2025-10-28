<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'address',
        'phone',
        'email',
        'latitude',
        'longitude',
        'opening_hours',
        'images',
        'description',
        'amenities',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'name' => 'array',
        'address' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'opening_hours' => 'array',
        'images' => 'array',
        'description' => 'array',
        'amenities' => 'array',
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Get the services offered at this branch.
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'branch_services')
            ->withPivot(['is_available', 'custom_price'])
            ->withTimestamps();
    }

    /**
     * Get the staff members working at this branch.
     */
    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    /**
     * Get the bookings for this branch.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Scope a query to only include active branches.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }

    /**
     * Calculate distance from given coordinates.
     */
    public function distanceFrom(float $latitude, float $longitude): float
    {
        if (!$this->latitude || !$this->longitude) {
            return 0;
        }

        $earthRadius = 6371; // Earth's radius in kilometers

        $branchLat = (float) $this->latitude;
        $branchLon = (float) $this->longitude;

        $latDiff = deg2rad($latitude - $branchLat);
        $lonDiff = deg2rad($longitude - $branchLon);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
            cos(deg2rad($branchLat)) * cos(deg2rad($latitude)) *
            sin($lonDiff / 2) * sin($lonDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}

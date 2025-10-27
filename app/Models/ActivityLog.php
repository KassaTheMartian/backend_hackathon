<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    /**
     * Get the subject of the activity.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the causer of the activity.
     */
    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who performed the activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include activities for a specific log name.
     */
    public function scopeLogName($query, $logName)
    {
        return $query->where('log_name', $logName);
    }

    /**
     * Scope a query to only include activities for a specific subject.
     */
    public function scopeSubject($query, $subject)
    {
        return $query->where('subject_type', get_class($subject))
                    ->where('subject_id', $subject->id);
    }

    /**
     * Scope a query to only include activities for a specific causer.
     */
    public function scopeCauser($query, $causer)
    {
        return $query->where('causer_type', get_class($causer))
                    ->where('causer_id', $causer->id);
    }

    /**
     * Scope a query to only include activities for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
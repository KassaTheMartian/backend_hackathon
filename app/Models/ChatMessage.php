<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'sender_id',
        'sender_type',
        'message',
        'message_type',
        'is_bot',
        'bot_confidence',
        'metadata',
        'read_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_bot' => 'boolean',
        'bot_confidence' => 'decimal:2',
        'read_at' => 'datetime',
    ];

    /**
     * Get the session that owns the message.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class, 'session_id');
    }

    /**
     * Get the sender of the message.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Scope a query to only include bot messages.
     */
    public function scopeBot($query)
    {
        return $query->where('is_bot', true);
    }

    /**
     * Scope a query to only include user messages.
     */
    public function scopeUser($query)
    {
        return $query->where('is_bot', false);
    }

    /**
     * Scope a query to only include staff messages.
     */
    public function scopeStaff($query)
    {
        return $query->where('sender_type', 'staff');
    }

    /**
     * Scope a query to only include unread messages.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope a query to only include read messages.
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Mark the message as read.
     */
    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    /**
     * Check if the message is from a bot.
     */
    public function isFromBot(): bool
    {
        return $this->is_bot;
    }

    /**
     * Check if the message is from a user.
     */
    public function isFromUser(): bool
    {
        return $this->sender_type === 'user' && !$this->is_bot;
    }

    /**
     * Check if the message is from staff.
     */
    public function isFromStaff(): bool
    {
        return $this->sender_type === 'staff';
    }

    /**
     * Check if the message has been read.
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }
}

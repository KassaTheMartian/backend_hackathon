<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_key',
        'meta',
        'last_activity',
        'is_active',
        'assigned_user_id',
        'assigned_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'last_activity' => 'datetime',
        'is_active' => 'boolean',
        'assigned_at' => 'datetime',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at', 'desc');
    }
}

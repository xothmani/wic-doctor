<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationParticipant extends Model
{
    use HasFactory;

    protected $table = 'conversation_participants';

    protected $fillable = [
        'conversation_id',
        'user_id',
        'joined_at',
        'left_at',
        'is_active'
    ];

    protected $casts = [
        'conversation_id' => 'integer',
        'user_id' => 'integer',
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    public static array $rules = [
        'conversation_id' => 'required|exists:conversations,id',
        'user_id' => 'required|exists:users,id'
    ];

    // Relationships
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForConversation($query, $conversationId)
    {
        return $query->where('conversation_id', $conversationId);
    }

    // Helper methods
    public function leave(): bool
    {
        $this->is_active = false;
        $this->left_at = now();
        return $this->save();
    }

    public function rejoin(): bool
    {
        $this->is_active = true;
        $this->left_at = null;
        return $this->save();
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }
}
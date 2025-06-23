<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Friend extends Model
{
    use HasFactory;

    protected $table = 'friends';

    protected $fillable = [
        'user_id',
        'friend_id',
        'status'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'friend_id' => 'integer',
        'status' => 'string'
    ];

    public static array $rules = [
        'user_id' => 'required|exists:users,id',
        'friend_id' => 'required|exists:users,id|different:user_id',
        'status' => 'required|in:pending,accepted,blocked'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function friend(): BelongsTo
    {
        return $this->belongsTo(User::class, 'friend_id');
    }

    // Scopes
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeBlocked($query)
    {
        return $query->where('status', 'blocked');
    }

    // Helper methods
    public static function areFriends($userId, $friendId): bool
    {
        return self::where(function ($query) use ($userId, $friendId) {
            $query->where('user_id', $userId)->where('friend_id', $friendId);
        })->orWhere(function ($query) use ($userId, $friendId) {
            $query->where('user_id', $friendId)->where('friend_id', $userId);
        })->where('status', 'accepted')->exists();
    }

    public static function getFriendship($userId, $friendId)
    {
        return self::where(function ($query) use ($userId, $friendId) {
            $query->where('user_id', $userId)->where('friend_id', $friendId);
        })->orWhere(function ($query) use ($userId, $friendId) {
            $query->where('user_id', $friendId)->where('friend_id', $userId);
        })->first();
    }
}
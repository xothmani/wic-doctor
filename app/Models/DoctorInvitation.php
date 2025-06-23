<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorInvitation extends Model
{
    use HasFactory;

    protected $table = 'doctor_invitations';

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'status'
    ];

    protected $casts = [
        'sender_id' => 'integer',
        'receiver_id' => 'integer',
        'message' => 'string',
        'status' => 'string'
    ];

    public static array $rules = [
        'sender_id' => 'required|exists:users,id',
        'receiver_id' => 'required|exists:users,id|different:sender_id',
        'message' => 'nullable|string|max:500',
        'status' => 'required|in:pending,accepted,declined,cancelled'
    ];

    // Relationships
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeDeclined($query)
    {
        return $query->where('status', 'declined');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('receiver_id', $userId);
    }

    public function scopeFromUser($query, $userId)
    {
        return $query->where('sender_id', $userId);
    }

    // Helper methods
    public function accept()
    {
        $this->status = 'accepted';
        $this->save();

        // Create friendship
        Friend::create([
            'user_id' => $this->sender_id,
            'friend_id' => $this->receiver_id,
            'status' => 'accepted'
        ]);

        return $this;
    }

    public function decline()
    {
        $this->status = 'declined';
        $this->save();

        return $this;
    }

    public function cancel()
    {
        $this->status = 'cancelled';
        $this->save();

        return $this;
    }

    public static function hasExistingInvitation($senderId, $receiverId): bool
    {
        return self::where('sender_id', $senderId)
            ->where('receiver_id', $receiverId)
            ->where('status', 'pending')
            ->exists();
    }
}
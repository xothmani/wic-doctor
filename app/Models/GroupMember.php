<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupMember extends Model
{
    use HasFactory;

    protected $table = 'group_members';

    protected $fillable = [
        'group_id',
        'user_id',
        'role',
        'joined_at',
        'is_active'
    ];

    protected $casts = [
        'group_id' => 'integer',
        'user_id' => 'integer',
        'role' => 'string',
        'joined_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    public static array $rules = [
        'group_id' => 'required|exists:groups,id',
        'user_id' => 'required|exists:users,id',
        'role' => 'required|in:admin,member'
    ];

    // Relationships
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
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

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin')->where('is_active', true);
    }

    public function scopeMembers($query)
    {
        return $query->where('role', 'member')->where('is_active', true);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForGroup($query, $groupId)
    {
        return $query->where('group_id', $groupId);
    }

    // Helper methods
    public function isAdmin(): bool
    {
        return $this->role === 'admin' && $this->is_active;
    }

    public function isMember(): bool
    {
        return $this->role === 'member' && $this->is_active;
    }

    public function leave(): bool
    {
        $this->is_active = false;
        return $this->save();
    }

    public function rejoin(): bool
    {
        $this->is_active = true;
        return $this->save();
    }

    public function promoteToAdmin(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $this->role = 'admin';
        return $this->save();
    }

    public function demoteToMember(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $this->role = 'member';
        return $this->save();
    }
}
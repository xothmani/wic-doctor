<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Manipulations;

class Group extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'groups';

    protected $fillable = [
        'name',
        'description',
        'avatar',
        'created_by',
        'is_active',
        'firebase_group_id'
    ];

    protected $casts = [
        'created_by' => 'integer',
        'is_active' => 'boolean'
    ];

    protected $appends = [
        'avatar_url',
        'members_count',
        'is_member'
    ];

    public static array $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'created_by' => 'required|exists:users,id'
    ];

    // Media conversions
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Manipulations::FIT_CROP, 200, 200)
            ->nonQueued();

        $this->addMediaConversion('icon')
            ->fit(Manipulations::FIT_CROP, 100, 100)
            ->nonQueued();
    }

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_members', 'group_id', 'user_id')
            ->withPivot(['role', 'joined_at', 'is_active'])
            ->withTimestamps()
            ->wherePivot('is_active', true);
    }

    public function groupMembers(): HasMany
    {
        return $this->hasMany(GroupMember::class);
    }

    public function activeMembers(): HasMany
    {
        return $this->hasMany(GroupMember::class)->where('is_active', true);
    }

    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_members', 'group_id', 'user_id')
            ->withPivot(['role', 'joined_at', 'is_active'])
            ->withTimestamps()
            ->wherePivot('role', 'admin')
            ->wherePivot('is_active', true);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->whereHas('members', function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->where('group_members.is_active', true);
        });
    }

    // Accessors
    public function getAvatarUrlAttribute(): string
    {
        if ($this->hasMedia('avatar')) {
            return $this->getFirstMediaUrl('avatar', 'thumb');
        }
        return asset('images/group_default.png');
    }

    public function getMembersCountAttribute(): int
    {
        return $this->activeMembers()->count();
    }

    public function getIsMemberAttribute(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        return $this->members()
            ->where('user_id', auth()->id())
            ->exists();
    }

    // Helper methods
    public function addMember($userId, $role = 'member'): GroupMember
    {
        return GroupMember::create([
            'group_id' => $this->id,
            'user_id' => $userId,
            'role' => $role,
            'is_active' => true
        ]);
    }

    public function removeMember($userId): bool
    {
        return GroupMember::where('group_id', $this->id)
            ->where('user_id', $userId)
            ->update(['is_active' => false]);
    }

    public function isAdmin($userId): bool
    {
        return $this->groupMembers()
            ->where('user_id', $userId)
            ->where('role', 'admin')
            ->where('is_active', true)
            ->exists();
    }

    public function isMember($userId): bool
    {
        return $this->groupMembers()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->exists();
    }

    public function promoteToAdmin($userId): bool
    {
        return GroupMember::where('group_id', $this->id)
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->update(['role' => 'admin']);
    }

    public function demoteFromAdmin($userId): bool
    {
        return GroupMember::where('group_id', $this->id)
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->update(['role' => 'member']);
    }
}
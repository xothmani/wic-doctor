<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Conversation extends Model
{
    use HasFactory;

    protected $table = 'conversations';

    protected $fillable = [
        'firebase_conversation_id',
        'type',
        'reference_id',
        'name',
        'is_archived'
    ];

    protected $casts = [
        'reference_id' => 'integer',
        'is_archived' => 'boolean'
    ];

    public static array $rules = [
        'firebase_conversation_id' => 'required|string|unique:conversations,firebase_conversation_id',
        'type' => 'required|in:direct,group,patient',
        'reference_id' => 'nullable|integer',
        'name' => 'nullable|string|max:255'
    ];

    // Relationships
    public function participants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function activeParticipants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class)->where('is_active', true);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants', 'conversation_id', 'user_id')
            ->withPivot(['joined_at', 'left_at', 'is_active'])
            ->withTimestamps()
            ->wherePivot('is_active', true);
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->whereHas('activeParticipants', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeDirect($query)
    {
        return $query->where('type', 'direct');
    }

    public function scopeGroup($query)
    {
        return $query->where('type', 'group');
    }

    public function scopePatient($query)
    {
        return $query->where('type', 'patient');
    }

    // Helper methods
    public function addParticipant($userId): ConversationParticipant
    {
        return ConversationParticipant::create([
            'conversation_id' => $this->id,
            'user_id' => $userId,
            'is_active' => true
        ]);
    }

    public function removeParticipant($userId): bool
    {
        return ConversationParticipant::where('conversation_id', $this->id)
            ->where('user_id', $userId)
            ->update([
                'is_active' => false,
                'left_at' => now()
            ]);
    }

    public function hasParticipant($userId): bool
    {
        return $this->activeParticipants()
            ->where('user_id', $userId)
            ->exists();
    }

    public function archive(): bool
    {
        $this->is_archived = true;
        return $this->save();
    }

    public function unarchive(): bool
    {
        $this->is_archived = false;
        return $this->save();
    }

    public function getReferencedModel()
    {
        if (!$this->reference_id) {
            return null;
        }

        switch ($this->type) {
            case 'group':
                return Group::find($this->reference_id);
            case 'patient':
                return Patient::find($this->reference_id);
            default:
                return null;
        }
    }

    public static function createForDirectChat($participantIds, $firebaseConversationId): self
    {
        $conversation = self::create([
            'firebase_conversation_id' => $firebaseConversationId,
            'type' => 'direct'
        ]);

        foreach ($participantIds as $userId) {
            $conversation->addParticipant($userId);
        }

        return $conversation;
    }

    public static function createForGroup($groupId, $firebaseConversationId): self
    {
        $group = Group::findOrFail($groupId);

        $conversation = self::create([
            'firebase_conversation_id' => $firebaseConversationId,
            'type' => 'group',
            'reference_id' => $groupId,
            'name' => $group->name
        ]);

        // Add all group members as participants
        foreach ($group->activeMembers as $member) {
            $conversation->addParticipant($member->user_id);
        }

        return $conversation;
    }

    public static function createForPatient($patientId, $doctorId, $firebaseConversationId): self
    {
        $patient = Patient::findOrFail($patientId);

        $conversation = self::create([
            'firebase_conversation_id' => $firebaseConversationId,
            'type' => 'patient',
            'reference_id' => $patientId,
            'name' => $patient->first_name . ' ' . $patient->last_name
        ]);

        // Add doctor and patient user (if exists)
        $conversation->addParticipant($doctorId);
        if ($patient->user_id) {
            $conversation->addParticipant($patient->user_id);
        }

        return $conversation;
    }
}
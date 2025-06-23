<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Friend;
use App\Models\DoctorInvitation;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Conversation;
use App\Models\Patient;
use App\Models\DoctorPatients;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MessengerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Generate a proper patient profile picture URL
     */
    private function getPatientProfilePictureUrl($patient, $default = 'images/patient_default.png'): string
    {
        // Check if patient has a profile_picture column value
        if ($patient->profile_picture) {
            // Extract filename from the database URL (e.g., "@https://wic-doctor.com:3004/uploads/1746022824749.png")
            $profilePictureUrl = $patient->profile_picture;

            // Remove the @ symbol if present
            $profilePictureUrl = ltrim($profilePictureUrl, '@');

            // Extract filename from URL
            $filename = basename(parse_url($profilePictureUrl, PHP_URL_PATH));

            if ($filename) {
                // Construct local file path
                $localPath = public_path('Back-NodeJS/Back-WIc-Doctors/src/uploads/' . $filename);

                // Check if file exists locally
                if (file_exists($localPath)) {
                    // Return the URL that can be accessed via web
                    return url('Back-NodeJS/Back-WIc-Doctors/src/uploads/' . $filename);
                }
            }
        }

        // Fallback to Spatie Media Library if no profile_picture column or file doesn't exist
        $mediaUrl = $patient->getFirstMediaUrl('image', 'thumb');
        if ($mediaUrl) {
            $mediaUrl = str_replace('localhoststorage', 'localhost/storage', $mediaUrl);
            return $mediaUrl;
        }

        return url($default);
    }

    /**
     * Generate a proper avatar URL that works correctly
     */
    private function getAvatarUrl($user, $collection = 'avatar', $conversion = 'thumb', $default = 'images/avatar_default.png'): string
    {
        $mediaUrl = $user->getFirstMediaUrl($collection, $conversion);

        if ($mediaUrl) {
            // Fix the URL if it contains 'localhoststorage' issue
            $mediaUrl = str_replace('localhoststorage', 'localhost/storage', $mediaUrl);
            return $mediaUrl;
        }

        return url($default);
    }

    // Authentication & User Management
    public function getUserProfile(): JsonResponse
    {
        $user = Auth::user();
        $user->load(['doctor']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'avatar' => $this->getAvatarUrl($user),
                'is_online' => $user->is_online,
                'last_activity' => $user->last_activity,
                'doctor' => $user->doctor ? [
                    'id' => $user->doctor->id,
                    'name' => $user->doctor->name,
                    'speciality' => $user->doctor->specialities->pluck('name')->join(', ')
                ] : null
            ]
        ]);
    }

    public function updateOnlineStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'is_online' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $user->update([
            'is_online' => $request->is_online,
            'last_activity' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Online status updated successfully'
        ]);
    }

    // Friend/Connection System
    public function sendInvitation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id|different:' . Auth::id(),
            'message' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $receiverId = $request->user_id;

        // Check if already friends
        if (Friend::areFriends(Auth::id(), $receiverId)) {
            return response()->json([
                'success' => false,
                'message' => 'You are already friends with this user'
            ], 400);
        }

        // Check if invitation already exists
        if (DoctorInvitation::hasExistingInvitation(Auth::id(), $receiverId)) {
            return response()->json([
                'success' => false,
                'message' => 'Invitation already sent'
            ], 400);
        }

        $invitation = DoctorInvitation::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $receiverId,
            'message' => $request->message,
            'status' => 'pending'
        ]);

        $invitation->load(['sender', 'receiver']);

        return response()->json([
            'success' => true,
            'message' => 'Invitation sent successfully',
            'data' => $invitation
        ]);
    }

    public function getReceivedInvitations(): JsonResponse
    {
        $invitations = DoctorInvitation::where('receiver_id', Auth::id())
            ->where('status', 'pending')
            ->with(['sender'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $invitations
        ]);
    }

    public function acceptInvitation($id): JsonResponse
    {
        $invitation = DoctorInvitation::where('id', $id)
            ->where('receiver_id', Auth::id())
            ->where('status', 'pending')
            ->first();

        if (!$invitation) {
            return response()->json([
                'success' => false,
                'message' => 'Invitation not found'
            ], 404);
        }

        DB::beginTransaction();
        try {
            $invitation->accept();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invitation accepted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error accepting invitation'
            ], 500);
        }
    }

    public function declineInvitation($id): JsonResponse
    {
        $invitation = DoctorInvitation::where('id', $id)
            ->where('receiver_id', Auth::id())
            ->where('status', 'pending')
            ->first();

        if (!$invitation) {
            return response()->json([
                'success' => false,
                'message' => 'Invitation not found'
            ], 404);
        }

        $invitation->decline();

        return response()->json([
            'success' => true,
            'message' => 'Invitation declined successfully'
        ]);
    }

    public function getFriends(): JsonResponse
    {
        $userId = Auth::id();

        $friends = User::whereIn('id', function ($query) use ($userId) {
            $query->select(DB::raw('CASE 
                WHEN user_id = ' . $userId . ' THEN friend_id 
                ELSE user_id 
                END'))
                ->from('friends')
                ->where(function ($q) use ($userId) {
                    $q->where('user_id', $userId)
                        ->orWhere('friend_id', $userId);
                })
                ->where('status', 'accepted');
        })
            ->with(['doctor'])
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'avatar' => $this->getAvatarUrl($user),
                    'is_online' => $user->is_online,
                    'last_activity' => $user->last_activity,
                    'doctor' => $user->doctor ? [
                        'name' => $user->doctor->name,
                        'speciality' => $user->doctor->specialities->pluck('name')->join(', ')
                    ] : null
                ];
            });

        return response()->json([
            'success' => true,
            'friends' => $friends
        ]);
    }

    public function removeFriend($id): JsonResponse
    {
        $friendship = Friend::getFriendship(Auth::id(), $id);

        if (!$friendship) {
            return response()->json([
                'success' => false,
                'message' => 'Friendship not found'
            ], 404);
        }

        $friendship->delete();

        return response()->json([
            'success' => true,
            'message' => 'Friend removed successfully'
        ]);
    }

    // Patient Access
    public function getPatients(): JsonResponse
    {
        $user = Auth::user();

        if (!$user->doctor) {
            return response()->json([
                'success' => false,
                'message' => 'User is not a doctor'
            ], 403);
        }

        $patients = Patient::whereHas('doctors', function ($query) use ($user) {
            $query->where('doctor_id', $user->doctor->id);
        })
            ->get()
            ->map(function ($patient) {
                return [
                    'id' => $patient->id,
                    'first_name' => $patient->first_name,
                    'last_name' => $patient->last_name,
                    'phone_number' => $patient->phone_number,
                    'email' => $patient->email,
                    'age' => $patient->age,
                    'gender' => $patient->gender,
                    'avatar' => $this->getPatientProfilePictureUrl($patient, 'images/patient_default.png'),
                    'last_appointment' => $patient->appointments()->latest()->first()?->created_at
                ];
            });

        return response()->json([
            'success' => true,
            'patients' => $patients
        ]);
    }

    // Group Management
    public function createGroup(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Generate Firebase group ID
            $firebaseGroupId = 'group_' . Str::uuid()->toString();

            $group = Group::create([
                'name' => $request->name,
                'description' => $request->description,
                'created_by' => Auth::id(),
                'firebase_group_id' => $firebaseGroupId
            ]);

            // Add creator as admin
            $group->addMember(Auth::id(), 'admin');

            // Add other members
            if ($request->has('members')) {
                foreach ($request->members as $memberId) {
                    if ($memberId != Auth::id()) {
                        $group->addMember($memberId, 'member');
                    }
                }
            }

            // Create conversation mapping
            Conversation::createForGroup($group->id, $firebaseGroupId);

            DB::commit();

            $group->load(['members', 'creator']);

            return response()->json([
                'success' => true,
                'message' => 'Group created successfully',
                'data' => $group
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating group'
            ], 500);
        }
    }

    public function getGroups(): JsonResponse
    {
        $groups = Group::forUser(Auth::id())
            ->active()
            ->with(['members', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'groups' => $groups
        ]);
    }

    public function addGroupMember(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $group = Group::findOrFail($id);

        if (!$group->isAdmin(Auth::id())) {
            return response()->json([
                'success' => false,
                'message' => 'You are not an admin of this group'
            ], 403);
        }

        if ($group->isMember($request->user_id)) {
            return response()->json([
                'success' => false,
                'message' => 'User is already a member of this group'
            ], 400);
        }

        $group->addMember($request->user_id);

        return response()->json([
            'success' => true,
            'message' => 'Member added successfully'
        ]);
    }

    public function removeGroupMember($id, $userId): JsonResponse
    {
        $group = Group::findOrFail($id);

        if (!$group->isAdmin(Auth::id()) && Auth::id() != $userId) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to remove this member'
            ], 403);
        }

        $group->removeMember($userId);

        return response()->json([
            'success' => true,
            'message' => 'Member removed successfully'
        ]);
    }

    // Search functionality
    public function searchDoctors(Request $request): JsonResponse
    {
        $email = $request->input('email');

        if (!$email) {
            return response()->json([
                'success' => false,
                'message' => 'Email parameter is required'
            ], 422);
        }

        $currentUserId = Auth::id();

        $doctors = User::whereHas('doctor')
            ->where('id', '!=', $currentUserId)
            ->where('email', 'LIKE', "%{$email}%")
            ->with(['doctor'])
            ->limit(20)
            ->get()
            ->map(function ($user) use ($currentUserId) {
                return [
                    'id' => $user->id,
                    'name' => $user->name . ' ' . $user->lastname,
                    'email' => $user->email,
                    'avatar' => $this->getAvatarUrl($user),
                    'is_online' => $user->is_online,
                    'is_friend' => Friend::areFriends($currentUserId, $user->id),
                    'doctor' => $user->doctor ? [
                        'name' => $user->doctor->name,
                        'speciality' => $user->doctor->specialities->pluck('name')->join(', ')
                    ] : null
                ];
            });

        return response()->json([
            'success' => true,
            'users' => $doctors
        ]);
    }

    // Get all conversations for current user
    public function getConversations(): JsonResponse
    {
        $conversations = Conversation::forUser(Auth::id())
            ->active()
            ->with(['users', 'activeParticipants.user'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($conversation) {
                $otherUser = null;
                $conversationName = $conversation->name;
                $conversationAvatar = null;

                if ($conversation->type === 'direct') {
                    $otherUser = $conversation->users->where('id', '!=', Auth::id())->first();
                    if ($otherUser) {
                        $conversationName = $otherUser->name . ' ' . $otherUser->lastname;
                        $conversationAvatar = $this->getAvatarUrl($otherUser);
                    }
                } elseif ($conversation->type === 'group') {
                    $group = $conversation->getReferencedModel();
                    if ($group) {
                        $conversationAvatar = $group->avatar_url;
                    }
                } elseif ($conversation->type === 'patient') {
                    $patient = $conversation->getReferencedModel();
                    if ($patient) {
                        $conversationAvatar = $this->getPatientProfilePictureUrl($patient, 'images/patient_default.png');
                    }
                }

                return [
                    'id' => $conversation->id,
                    'firebase_conversation_id' => $conversation->firebase_conversation_id,
                    'type' => $conversation->type,
                    'name' => $conversationName,
                    'avatar' => $conversationAvatar,
                    'participants_count' => $conversation->activeParticipants->count(),
                    'is_archived' => $conversation->is_archived,
                    'other_user' => $otherUser ? [
                        'id' => $otherUser->id,
                        'name' => $otherUser->name,
                        'lastname' => $otherUser->lastname,
                        'is_online' => $otherUser->is_online,
                        'last_activity' => $otherUser->last_activity
                    ] : null,
                    'created_at' => $conversation->created_at,
                    'updated_at' => $conversation->updated_at
                ];
            });

        return response()->json([
            'success' => true,
            'conversations' => $conversations
        ]);
    }

    // Create or get conversation for direct chat
    public function createDirectConversation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id|different:' . Auth::id()
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = $request->user_id;
        $currentUserId = Auth::id();

        // Check if conversation already exists
        $existingConversation = Conversation::where('type', 'direct')
            ->whereHas('activeParticipants', function ($q) use ($currentUserId) {
                $q->where('user_id', $currentUserId);
            })
            ->whereHas('activeParticipants', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->first();

        if ($existingConversation) {
            return response()->json([
                'success' => true,
                'data' => [
                    'conversation_id' => $existingConversation->id,
                    'firebase_conversation_id' => $existingConversation->firebase_conversation_id,
                    'exists' => true
                ]
            ]);
        }

        // Create new conversation
        $firebaseConversationId = 'direct_' . min($currentUserId, $userId) . '_' . max($currentUserId, $userId);

        $conversation = Conversation::createForDirectChat(
            [$currentUserId, $userId],
            $firebaseConversationId
        );

        return response()->json([
            'success' => true,
            'data' => [
                'conversation_id' => $conversation->id,
                'firebase_conversation_id' => $conversation->firebase_conversation_id,
                'exists' => false
            ]
        ]);
    }

    /**
     * Get notifications count for the current user
     */
    public function getNotificationsCount(): JsonResponse
    {
        $userId = Auth::id();

        // Count pending friend invitations
        $pendingInvitations = DoctorInvitation::where('receiver_id', $userId)
            ->where('status', 'pending')
            ->count();

        // Count unread messages (placeholder - would integrate with Firebase)
        $unreadMessages = 0; // This would be calculated from Firebase

        // Total notification count
        $totalCount = $pendingInvitations + $unreadMessages;

        return response()->json([
            'success' => true,
            'count' => $totalCount,
            'breakdown' => [
                'pending_invitations' => $pendingInvitations,
                'unread_messages' => $unreadMessages
            ]
        ]);
    }

    /**
     * 🔥 Save FCM token for user to fix sender ID issues
     */
    public function saveFCMToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'user_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        // Verify the user_id matches the authenticated user (security check)
        if ($user->id != $request->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: User ID mismatch'
            ], 403);
        }

        try {
            // Update user's FCM token
            $user->update([
                'fcm_token' => $request->token,
                'fcm_updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FCM token saved successfully',
                'data' => [
                    'user_id' => $user->id,
                    'token_length' => strlen($request->token)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving FCM token: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🧪 Test endpoint to verify authentication
     */
    public function testAuth(): JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'message' => 'Authentication working',
            'user_id' => $user ? $user->id : null,
            'user_name' => $user ? $user->name : null,
            'timestamp' => now()->toDateTimeString()
        ]);
    }
}
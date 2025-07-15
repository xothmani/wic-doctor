<?php

namespace App\Http\Controllers;

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

        $friendId = $request->user_id;
        $currentUserId = Auth::id();

        // Check if already friends or pending
        $existingFriendship = Friend::getFriendship($currentUserId, $friendId);

        if ($existingFriendship) {
            if ($existingFriendship->status === 'accepted') {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already friends with this user'
                ], 400);
            } elseif ($existingFriendship->status === 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Friend request already sent'
                ], 400);
            } elseif ($existingFriendship->status === 'blocked') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot send friend request to this user'
                ], 400);
            }
        }

        try {
            // 🔥 Create friend request directly in friends table
            $friendRequest = Friend::create([
                'user_id' => $currentUserId,      // Sender
                'friend_id' => $friendId,         // Receiver  
                'status' => 'pending'
            ]);

            // Get friend info for response
            $friendUser = User::find($friendId);

            // 📝 Log the friend request
            \Log::info("👥 Friend request sent", [
                'sender_id' => $currentUserId,
                'receiver_id' => $friendId,
                'receiver_name' => $friendUser ? $friendUser->name : 'Unknown',
                'friend_request_id' => $friendRequest->id,
                'timestamp' => now()->toDateTimeString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Friend request sent successfully',
                'data' => [
                    'friendship_id' => $friendRequest->id,
                    'sender_id' => $currentUserId,
                    'receiver_id' => $friendId,
                    'receiver_name' => $friendUser ? $friendUser->name : 'Unknown',
                    'status' => 'pending',
                    'sent_at' => $friendRequest->created_at
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error("❌ Error sending friend request", [
                'sender_id' => $currentUserId,
                'receiver_id' => $friendId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error sending friend request'
            ], 500);
        }
    }

    public function getReceivedInvitations(): JsonResponse
    {
        $currentUserId = Auth::id();

        // 🔥 Get pending friend requests from friends table
        $friendRequests = Friend::where('friend_id', $currentUserId)
            ->where('status', 'pending')
            ->with(['user']) // Load sender info
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($friendship) {
                return [
                    'id' => $friendship->id,
                    'sender_id' => $friendship->user_id,
                    'sender_name' => $friendship->user ? $friendship->user->name : 'Unknown',
                    'sender_email' => $friendship->user ? $friendship->user->email : null,
                    'sender_avatar' => $friendship->user ? $this->getAvatarUrl($friendship->user) : null,
                    'status' => $friendship->status,
                    'sent_at' => $friendship->created_at,
                    'message' => null // No message field in friends table
                ];
            });

        return response()->json([
            'success' => true,
            'invitations' => $friendRequests
        ]);
    }

    public function acceptInvitation($id): JsonResponse
    {
        $currentUserId = Auth::id();

        // 🔥 Find friend request in friends table
        $friendship = Friend::where('id', $id)
            ->where('friend_id', $currentUserId) // Current user is the receiver
            ->where('status', 'pending')
            ->first();

        if (!$friendship) {
            return response()->json([
                'success' => false,
                'message' => 'Friend request not found'
            ], 404);
        }

        try {
            // 🔥 Accept the friend request
            $friendship->update(['status' => 'accepted']);

            // Load sender info for response
            $friendship->load('user');

            // 📝 Log the acceptance
            \Log::info("✅ Friend request accepted", [
                'friendship_id' => $friendship->id,
                'sender_id' => $friendship->user_id,
                'receiver_id' => $currentUserId,
                'sender_name' => $friendship->user ? $friendship->user->name : 'Unknown',
                'timestamp' => now()->toDateTimeString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Friend request accepted successfully',
                'data' => [
                    'friendship_id' => $friendship->id,
                    'friend_name' => $friendship->user ? $friendship->user->name : 'Unknown',
                    'status' => 'accepted'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error("❌ Error accepting friend request", [
                'friendship_id' => $id,
                'receiver_id' => $currentUserId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error accepting friend request'
            ], 500);
        }
    }

    public function declineInvitation($id): JsonResponse
    {
        $currentUserId = Auth::id();

        // 🔥 Find friend request in friends table
        $friendship = Friend::where('id', $id)
            ->where('friend_id', $currentUserId) // Current user is the receiver
            ->where('status', 'pending')
            ->first();

        if (!$friendship) {
            return response()->json([
                'success' => false,
                'message' => 'Friend request not found'
            ], 404);
        }

        try {
            // 🔥 Delete the friend request (decline)
            $senderName = $friendship->user ? $friendship->user->name : 'Unknown';
            $senderId = $friendship->user_id;

            $friendship->delete();

            // 📝 Log the decline
            \Log::info("❌ Friend request declined", [
                'friendship_id' => $id,
                'sender_id' => $senderId,
                'receiver_id' => $currentUserId,
                'sender_name' => $senderName,
                'timestamp' => now()->toDateTimeString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Friend request declined successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error("❌ Error declining friend request", [
                'friendship_id' => $id,
                'receiver_id' => $currentUserId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error declining friend request'
            ], 500);
        }
    }

    /**
     * Accept friend request by friend_id (for messenger auto-accept)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function acceptFriend(Request $request): JsonResponse
    {
        $currentUserId = Auth::id();
        $friendId = $request->input('friend_id');

        if (!$friendId) {
            return response()->json([
                'success' => false,
                'message' => 'Friend ID is required'
            ], 400);
        }

        // Find the friendship
        $friendship = Friend::where(function ($query) use ($currentUserId, $friendId) {
            $query->where([
                'user_id' => $friendId,
                'friend_id' => $currentUserId,
                'status' => 'pending'
            ]);
        })->first();

        if (!$friendship) {
            return response()->json([
                'success' => false,
                'message' => 'Pending friend request not found'
            ], 404);
        }

        try {
            // Accept the friend request
            $friendship->update(['status' => 'accepted']);

            // Get friend name for response
            $friend = User::find($friendId);
            $friendName = $friend ? $friend->name : 'Unknown';

            // Log the acceptance
            \Log::info("✅ Friend request auto-accepted from chat", [
                'friendship_id' => $friendship->id,
                'sender_id' => $friendId,
                'receiver_id' => $currentUserId,
                'sender_name' => $friendName,
                'timestamp' => now()->toDateTimeString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Friend request accepted successfully',
                'data' => [
                    'friendship_id' => $friendship->id,
                    'status' => 'accepted',
                    'friend_name' => $friendName
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error("❌ Error auto-accepting friend request", [
                'sender_id' => $friendId,
                'receiver_id' => $currentUserId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error accepting friend request'
            ], 500);
        }
    }

    public function getFriends(): JsonResponse
    {
        $userId = Auth::id();

        // 🔥 Get ALL friendship relationships (pending and accepted)
        $friendships = Friend::where(function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->orWhere('friend_id', $userId);
        })
            ->whereIn('status', ['pending', 'accepted'])
            ->with(['user', 'friend'])
            ->orderBy('status') // Show accepted first
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($friendship) use ($userId) {
                // Determine which user is the "other" user
                $isCurrentUserSender = $friendship->user_id == $userId;
                $otherUser = $isCurrentUserSender ? $friendship->friend : $friendship->user;

                if (!$otherUser) {
                    return null; // Skip if user not found
                }

                // Load doctor info if available
                $otherUser->load('doctor.specialities');

                return [
                    'friendship_id' => $friendship->id,
                    'id' => $otherUser->id,
                    'name' => $otherUser->name . ' ' . ($otherUser->lastname ?? ''),
                    'lastname' => $otherUser->lastname,
                    'email' => $otherUser->email,
                    'avatar' => $this->getAvatarUrl($otherUser),
                    'is_online' => $otherUser->is_online ?? false,
                    'last_activity' => $otherUser->last_activity,
                    'doctor' => $otherUser->doctor ? [
                        'name' => $otherUser->doctor->name,
                        'speciality' => $otherUser->doctor->specialities->pluck('name')->join(', ')
                    ] : null,
                    // 🔥 Friendship status info
                    'friendship_status' => $friendship->status,
                    'friendship_label' => $this->getFriendshipLabel($friendship->status),
                    'is_sender' => $isCurrentUserSender,
                    'is_receiver' => !$isCurrentUserSender,
                    'can_message' => $friendship->status === 'accepted',
                    'can_accept' => $friendship->status === 'pending' && !$isCurrentUserSender,
                    'can_decline' => $friendship->status === 'pending' && !$isCurrentUserSender,
                    'show_status' => $friendship->status === 'pending',
                    'created_at' => $friendship->created_at,
                    'status_description' => $this->getStatusDescription($friendship->status, $isCurrentUserSender)
                ];
            })
            ->filter() // Remove null entries
            ->values(); // Reset array indexes

        // 📝 Log friends loading
        \Log::info("👥 Friends loaded", [
            'user_id' => $userId,
            'total_friendships' => $friendships->count(),
            'accepted_count' => $friendships->where('friendship_status', 'accepted')->count(),
            'pending_count' => $friendships->where('friendship_status', 'pending')->count(),
            'timestamp' => now()->toDateTimeString()
        ]);

        return response()->json([
            'success' => true,
            'friends' => $friendships
        ]);
    }

    /**
     * Get status description based on user role
     */
    private function getStatusDescription(string $status, bool $isCurrentUserSender): string
    {
        if ($status === 'accepted') {
            return 'Ami';
        }

        if ($status === 'pending') {
            return $isCurrentUserSender ? 'Demande envoyée' : 'Demande reçue';
        }

        return 'Statut inconnu';
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
    /**
     * Generate Firebase-style document ID (like kEkT2fad7AcqKM17XdLp)
     */
    private function generateFirebaseId(int $length = 20): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    public function createGroup(Request $request): JsonResponse
    {
        \Log::info('🔥 Creating group data for Firebase', ['request' => $request->all()]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id'
        ]);

        if ($validator->fails()) {
            \Log::error('❌ Group creation validation failed', [
                'errors' => $validator->errors()->toArray(),
                'input' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        \Log::info('✅ Group validation passed', [
            'name' => $request->name,
            'description' => $request->description,
            'members' => $request->members,
            'current_user' => Auth::id()
        ]);

        try {
            // 🔥 Generate Firebase-style document ID (like kEkT2fad7AcqKM17XdLp)
            $firebaseGroupId = $this->generateFirebaseId();
            \Log::info('🔥 Generated Firebase-style group ID', ['firebase_group_id' => $firebaseGroupId]);

            $currentUserId = Auth::id();
            $currentTimestamp = now()->timestamp * 1000; // Firebase timestamp format

            // 🔥 Prepare members object - Firebase format
            $membersObject = new \stdClass();
            $membersObject->{$currentUserId} = true; // Creator is always a member

            // Add other members
            if ($request->has('members') && is_array($request->members)) {
                \Log::info('👥 Adding members to group', ['members' => $request->members]);
                foreach ($request->members as $memberId) {
                    if ($memberId != $currentUserId) {
                        $membersObject->{$memberId} = true;
                        \Log::info('👤 Added member to object', ['member_id' => $memberId]);
                    }
                }
            }

            // 🔥 Prepare group data for Firebase
            $groupData = [
                'name' => $request->name,
                'description' => $request->description ?? '',
                'avatar' => '/images/default-group.png',
                'createdBy' => (string) $currentUserId,
                'createdAt' => $currentTimestamp,
                'updatedAt' => $currentTimestamp,
                'members' => $membersObject,
                'isActive' => true
            ];

            \Log::info('🔥 Prepared group data for Firebase', [
                'group_data' => $groupData,
                'members_count' => count((array) $membersObject),
                'firebase_id_format' => 'Firebase-style: ' . $firebaseGroupId
            ]);

            // 🔥 Return data for frontend to save to Firebase
            \Log::info('✅ Group data prepared for frontend Firebase save', [
                'firebase_group_id' => $firebaseGroupId,
                'firebase_path' => "/groups/{$firebaseGroupId}",
                'id_format' => 'Firebase-style (20 chars)'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Group data prepared successfully',
                'data' => [
                    'firebase_group_id' => $firebaseGroupId,
                    'firebase_path' => "/groups/{$firebaseGroupId}",
                    'group_data' => $groupData,
                    'created_by' => $currentUserId,
                    'members_list' => array_keys((array) $membersObject),
                    'should_save_to_firebase' => true,
                    'id_format' => 'firebase_style'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Error preparing group for Firebase', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'stack_trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error preparing group: ' . $e->getMessage(),
                'error_details' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function getGroups(): JsonResponse
    {
        try {
            $currentUserId = Auth::id();
            \Log::info('🔥 Loading groups from Firebase for user', ['user_id' => $currentUserId]);

            // 🔥 Since we can't query Firebase from Laravel backend easily,
            // we'll return instructions for frontend to query Firebase
            return response()->json([
                'success' => true,
                'groups' => [], // Empty - will be populated by frontend from Firebase
                'firebase_info' => [
                    'user_id' => $currentUserId,
                    'message' => 'Frontend should query Firebase for groups at /groups/',
                    'query_instructions' => [
                        '1. Query Firebase collection "groups"',
                        '2. Filter where members.' . $currentUserId . ' == true',
                        '3. Filter where isActive == true',
                        '4. Order by updatedAt desc',
                        '5. Build group list on frontend'
                    ],
                    'query_path' => '/groups',
                    'filter_condition' => "members.{$currentUserId} == true && isActive == true"
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Error in getGroups Firebase', [
                'error_message' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error loading groups: ' . $e->getMessage(),
                'groups' => []
            ], 500);
        }
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
            ->with(['doctor.specialities'])
            ->limit(20)
            ->get()
            ->map(function ($user) use ($currentUserId) {
                $doctor = $user->doctor;

                // 👥 Get friendship status
                $friendship = Friend::getFriendship($currentUserId, $user->id);
                $friendshipStatus = $friendship ? $friendship->status : 'none';

                // 📱 Determine if user can send friend request
                $canSendRequest = !$friendship || $friendship->status === 'blocked';

                return [
                    'id' => $user->id,
                    'name' => $user->name . ' ' . ($user->lastname ?? ''),
                    'email' => $user->email,
                    'specialty' => $doctor && $doctor->specialities ?
                        $doctor->specialities->pluck('name')->join(', ') :
                        'Spécialité non définie',
                    // 🔥 Friendship credentials
                    'friendship_status' => $friendshipStatus,
                    'friendship_label' => $this->getFriendshipLabel($friendshipStatus),
                    'can_send_friend_request' => $canSendRequest,
                    'is_friend' => $friendshipStatus === 'accepted'
                ];
            });

        // 📝 Log the search result with friendship info
        \Log::info("🔍 Doctor search completed", [
            'search_email' => $email,
            'searched_by_user_id' => $currentUserId,
            'total_found' => $doctors->count(),
            'results_with_friendship' => $doctors->toArray(),
            'timestamp' => now()->toDateTimeString()
        ]);

        return response()->json([
            'success' => true,
            'users' => $doctors
        ]);
    }

    /**
     * Get human-readable friendship status label
     */
    private function getFriendshipLabel(string $status): string
    {
        return match ($status) {
            'accepted' => 'Ami',
            'pending' => 'Demande envoyée',
            'blocked' => 'Bloqué',
            'none' => 'Pas d\'amitié',
            default => 'Statut inconnu'
        };
    }

    // Get all conversations for current user
    public function getConversations(): JsonResponse
    {
        try {
            $userId = Auth::id();

            // Since conversations are stored in Firebase, we can't query them from Laravel
            // Instead, we'll return the user info and let the frontend query Firebase directly

            return response()->json([
                'success' => true,
                'conversations' => [], // Empty - will be populated by frontend from Firebase
                'firebase_info' => [
                    'user_id' => $userId,
                    'message' => 'Frontend should query Firebase for conversations at /messages/{userId}-{otherUserId}/chats/',
                    'query_path_pattern' => '/messages/' . $userId . '-*/chats/',
                    'instructions' => [
                        '1. Query Firebase for all paths matching /messages/' . $userId . '-*/chats/',
                        '2. Also query /messages/*-' . $userId . '/chats/',
                        '3. For each existing conversation, get other user info from this API',
                        '4. Build conversation list on frontend'
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error in getConversations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Create or get conversation for direct chat
    public function createDirectConversation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|min:1'  // 🔧 Remove exists validation to allow both users and patients
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

        // 🔍 Check if it's a user or patient
        $otherUser = User::find($userId);
        $otherPatient = Patient::find($userId);

        if (!$otherUser && !$otherPatient) {
            return response()->json([
                'success' => false,
                'message' => 'User or patient not found',
                'searched_id' => $userId
            ], 404);
        }

        // 🔥 NORMALIZED CONVERSATION ID: Always use smaller ID first
        // This ensures the same conversation ID regardless of who sends the message
        $normalizedConversationId = min($currentUserId, $userId) . '-' . max($currentUserId, $userId);

        \Log::info('🔥 Normalized conversation ID', [
            'current_user' => $currentUserId,
            'other_user' => $userId,
            'normalized_id' => $normalizedConversationId,
            'logic' => 'smaller_id_first'
        ]);

        // 🔧 Prepare other user data (handle both User and Patient)
        if ($otherPatient) {
            // It's a patient
            $otherUserData = [
                'id' => $otherPatient->id,
                'name' => $otherPatient->first_name . ' ' . ($otherPatient->last_name ?? ''),
                'first_name' => $otherPatient->first_name,
                'last_name' => $otherPatient->last_name ?? '',
                'email' => $otherPatient->email,
                'phone_number' => $otherPatient->phone_number,
                'avatar' => $this->getPatientProfilePictureUrl($otherPatient, 'images/patient_default.png'),
                'type' => 'patient',
                'is_online' => false
            ];
        } else {
            // It's a regular user
            $otherUserData = [
                'id' => $otherUser->id,
                'name' => $otherUser->name . ' ' . ($otherUser->lastname ?? ''),
                'lastname' => $otherUser->lastname,
                'email' => $otherUser->email,
                'avatar' => $this->getAvatarUrl($otherUser),
                'type' => 'user',
                'is_online' => $otherUser->is_online ?? false
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'conversation_id' => $normalizedConversationId,
                'firebase_conversation_id' => $normalizedConversationId,
                'firebase_path' => '/messages/' . $normalizedConversationId . '/chats/',
                'type' => 'direct',
                'other_user' => $otherUserData,
                'participants' => [$currentUserId, $userId],
                'exists' => false, // Will be created in Firebase
                'message' => 'Normalized format: smaller_id-larger_id',
                'normalization_info' => [
                    'original_format' => $currentUserId . '-' . $userId,
                    'normalized_format' => $normalizedConversationId,
                    'ensures_same_id' => true
                ]
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
     * Get user information by ID for conversations
     */
    public function getUserInfo($userId): JsonResponse
    {
        try {
            \Log::info("🔍 MessengerController->getUserInfo called for userId: {$userId}");

            $user = User::find($userId);
            \Log::info("👤 User::find({$userId}) result: " . ($user ? 'Found' : 'NOT FOUND'));

            if (!$user) {
                \Log::warning("❌ User not found for ID: {$userId}");

                // Check if it's a patient instead
                $patient = Patient::find($userId);
                \Log::info("🏥 Patient::find({$userId}) result: " . ($patient ? 'Found' : 'NOT FOUND'));

                if ($patient) {
                    \Log::info("✅ Found patient for ID: {$userId}");
                    return response()->json([
                        'success' => true,
                        'user' => [
                            'id' => $patient->id,
                            'name' => $patient->first_name . ' ' . ($patient->last_name ?? ''),
                            'first_name' => $patient->first_name,
                            'last_name' => $patient->last_name ?? '',
                            'email' => $patient->email,
                            'phone_number' => $patient->phone_number,
                            'avatar' => $this->getPatientProfilePictureUrl($patient, 'images/patient_default.png'),
                            'type' => 'patient',
                            'is_online' => false,
                            'last_activity' => null
                        ]
                    ]);
                } else {
                    \Log::error("❌ Neither User nor Patient found for ID: {$userId}");
                    return response()->json([
                        'success' => false,
                        'message' => 'User not found',
                        'searched_id' => $userId,
                        'checked_tables' => ['users', 'patients']
                    ], 404);
                }
            }

            \Log::info("✅ Found user for ID: {$userId}, name: {$user->name}");

            // Check if it's a patient as well (user might be linked to patient)
            $patient = Patient::find($userId);
            \Log::info("🔍 Also checking Patient table for user {$userId}: " . ($patient ? 'Found' : 'Not found'));

            if ($patient) {
                \Log::info("✅ User {$userId} is also a patient");
                return response()->json([
                    'success' => true,
                    'user' => [
                        'id' => $patient->id,
                        'name' => $patient->first_name . ' ' . ($patient->last_name ?? ''),
                        'first_name' => $patient->first_name,
                        'last_name' => $patient->last_name ?? '',
                        'email' => $patient->email,
                        'phone_number' => $patient->phone_number,
                        'avatar' => $this->getPatientProfilePictureUrl($patient, 'images/patient_default.png'),
                        'type' => 'patient',
                        'is_online' => false,
                        'last_activity' => null
                    ]
                ]);
            }

            // Regular user
            \Log::info("✅ Returning regular user data for ID: {$userId}");
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name . ' ' . ($user->lastname ?? ''),
                    'first_name' => $user->name,
                    'last_name' => $user->lastname ?? '',
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'avatar' => $this->getAvatarUrl($user),
                    'type' => 'user',
                    'is_online' => $user->is_online ?? false,
                    'last_activity' => $user->last_activity
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error("❌ Exception in getUserInfo for userId {$userId}: " . $e->getMessage());
            \Log::error("❌ Stack trace: " . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error getting user info',
                'error' => $e->getMessage(),
                'user_id' => $userId
            ], 500);
        }
    }

    /**
     * Get potential conversation partners for the current user
     */
    public function getPotentialPartners(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $partners = [];

            // Get friends
            try {
                $friends = DB::table('friends')
                    ->where('user_id', $userId)
                    ->orWhere('friend_id', $userId)
                    ->get();

                foreach ($friends as $friend) {
                    $partnerId = ($friend->user_id == $userId) ? $friend->friend_id : $friend->user_id;
                    if (!in_array($partnerId, $partners)) {
                        $partners[] = $partnerId;
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Could not query friends table: ' . $e->getMessage());
            }

            // Get patients
            try {
                $patients = DB::table('doctor_patients')
                    ->where('doctor_id', $userId)
                    ->get();

                foreach ($patients as $relation) {
                    if (!in_array($relation->patient_id, $partners)) {
                        $partners[] = $relation->patient_id;
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Could not query doctor_patients table: ' . $e->getMessage());
            }

            // Add known conversation partners (like user 433 from Firebase)
            $knownPartners = [433]; // Add other known user IDs here
            foreach ($knownPartners as $knownId) {
                if (!in_array($knownId, $partners)) {
                    $partners[] = $knownId;
                }
            }

            return response()->json([
                'success' => true,
                'partners' => $partners,
                'count' => count($partners)
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting potential partners: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving potential partners',
                'partners' => [433] // Fallback to known partners
            ], 500);
        }
    }
}
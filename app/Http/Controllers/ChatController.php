<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Models\Group; // Assuming you have a Group model

class ChatController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function index($selectedUserId = null)
    {
        // Get current authenticated user
        $currentUser = auth()->user();

        // Get all users for the chat tab
        $users = User::where('id', '!=', auth()->id())
            ->when($currentUser->hasRole('doctor'), function ($query) {
                return $query;
            })
            ->when($currentUser->hasRole('patient'), function ($query) {
                return $query->whereHas('roles', function ($q) {
                    $q->where('name', 'doctor');
                });
            })
            ->take(50)
            ->get();

        // Get patients for the patients tab (only for doctors)
        $patients = collect([]);
        if ($currentUser->hasRole('doctor')) {
            $patients = User::whereHas('roles', function ($query) {
                $query->where('name', 'patient');
            })
                ->with('patient') // Load the patient relationship if you have one
                ->take(50)
                ->get();
        }

        // Initialize empty groups collection
        $groups = collect([]);

        // Get the selected user or first available user
        $user = null;
        if ($selectedUserId) {
            $user = User::with('patient')->findOrFail($selectedUserId);
        } else if ($patients->count() > 0 && $currentUser->hasRole('doctor')) {
            $user = $patients->first();
        } else if ($users->count() > 0) {
            $user = $users->first();
        }

        try {
            $firebaseToken = $this->firebaseService->createCustomToken(auth()->id());
        } catch (\Exception $e) {
            \Log::error("Firebase token error: " . $e->getMessage());
            $firebaseToken = "";
        }

        return view('chat.index', compact('users', 'patients', 'groups', 'user', 'currentUser', 'firebaseToken'));
    }
    public function show($userId)
    {
        $user = User::findOrFail($userId); // This will throw a 404 if user not found
        $currentUser = auth()->user();
        $firebaseToken = $this->firebaseService->createCustomToken(auth()->id());

        // Get all users for the sidebar
        $users = User::where('id', '!=', auth()->id())
            ->when(auth()->user()->hasRole('doctor'), function ($query) {
                return $query;
            })
            ->when(auth()->user()->hasRole('patient'), function ($query) {
                return $query->whereHas('roles', function ($q) {
                    $q->where('name', 'doctor');
                });
            })
            ->take(50)
            ->get();

        return view('chat.show', compact('user', 'currentUser', 'users', 'firebaseToken'));
    }

    public function sendMessage(Request $request)
    {
        \Log::info('Send message request: ', $request->all());
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string'
        ]);

        $this->firebaseService->saveMessage(
            auth()->id(),
            $request->receiver_id,
            $request->message
        );

        return response()->json(['success' => true]);
    }

    public function markAsRead(Request $request)
    {
        $request->validate([
            'sender_id' => 'required|exists:users,id'
        ]);

        $this->firebaseService->markMessagesAsRead(
            auth()->id(),
            $request->sender_id
        );

        return response()->json(['success' => true]);
    }

    public function getUnreadCount()
    {
        $count = $this->firebaseService->getUnreadCount(auth()->id());
        return response()->json(['count' => $count]);
    }

    public function getUserStatus($userId)
    {
        $status = $this->firebaseService->getUserStatus($userId);
        return response()->json($status);
    }
}
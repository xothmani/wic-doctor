<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role; // Add this if you're using Spatie's permission package

class ChatController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function index()
    {
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

        try {
            $firebaseToken = $this->firebaseService->createCustomToken(auth()->id());
        } catch (\Exception $e) {
            \Log::error("Firebase token error: " . $e->getMessage());
            $firebaseToken = "";
        }

        return view('chat.index', compact('users', 'firebaseToken'));
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
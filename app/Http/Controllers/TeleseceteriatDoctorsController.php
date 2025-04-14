<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\DoctorTelesecretariat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Kreait\Firebase\Factory;
use App\Models\DoctorAssociate
;


use Illuminate\Support\Facades\Auth;

class TeleseceteriatDoctorsController extends Controller
{
    public function fetchMessages($receiverId)
    {
        $senderId = auth()->id();
        $chatId = $this->getChatId($senderId, $receiverId);
    
        $firebase_url = 'https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app/chatTE/' . $chatId . '/messages.json';
        $response = Http::get($firebase_url);
        $messages = $response->json();
    
        $messagesArray = [];
        if (is_array($messages)) {
            foreach ($messages as $firebaseKey => $message) { // Récupérer la clé Firebase
                $messagesArray[] = [
                    'firebaseKey' => $firebaseKey, // Ajouter la clé Firebase
                    'id' => $message['id'],
                    'sender_id' => $message['sender_id'],
                    'sender_name' => User::find($message['sender_id'])->name,
                    'receiver_id' => $message['receiver_id'],
                    'receiver_name' => User::find($message['receiver_id'])->name,
                    'content' => $message['content'],
                    'timestamp' => $message['timestamp'],
                    'file_url' => $message['file_url'] ?? null,
                ];
            }
        }
    
        // Trier les messages par timestamp
        usort($messagesArray, function ($a, $b) {
            return $a['timestamp'] <=> $b['timestamp'];
        });
    
        return response()->json(['messages' => $messagesArray]);
    }
    
    public function showForm(Request $request)
    {
        $user = auth()->user();
        $userId = $user->id;
        $userRole = $user->role;
    
        // Initialize lastMessages as an empty array
        $lastMessages = [];
    
        if ($userRole === 'doctor') {
            // Get associated tele-secretariats for the logged-in doctor
            $teleSecretariats = DoctorAssociate::where('doctor_id', $userId)
                ->with('telesecretariat')
                ->get()
                ->pluck('telesecretariat');
    
            // If no tele-secretariat is found, initialize an empty collection
            if ($teleSecretariats->isEmpty()) {
                $teleSecretariats = collect();
            }
    
            $doctors = null;
        } elseif ($userRole === 'telesecretariat') {
            // Get all doctors if the user is a tele-secretariat
            $doctors = Doctor::all();
            $teleSecretariats = null;
        } else {
            abort(403, 'Unauthorized action.');
        }
    
        // Firebase URL to fetch messages
        $firebase_url = 'https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app/chatTE.json';
        $response = Http::get($firebase_url);
        $messages = $response->json();
    
        // Process messages to get last messages and unread status
        if (!empty($messages)) {
            foreach ($messages as $chatId => $chat) {
                if (isset($chat['messages'])) {
                    foreach ($chat['messages'] as $message) {
                        // Determine the partner user
                        $partnerId = null;
                        $currentUserId = $userId;
    
                        if ($message['sender_id'] == $currentUserId) {
                            $partnerId = $message['receiver_id'];
                        } else {
                            $partnerId = $message['sender_id'];
                        }
    
                        $partnerUser = User::find($partnerId);
    
                        // For doctors: only with tele-secretariats
                        if ($userRole === 'doctor' && $partnerUser->role === 'telesecretariat') {
                            if (!isset($lastMessages[$partnerId])) {
                                $lastMessages[$partnerId] = [
                                    'content' => $message['content'],
                                    'timestamp' => $message['timestamp']
                                ];
                            } else {
                                if ($message['timestamp'] > $lastMessages[$partnerId]['timestamp']) {
                                    $lastMessages[$partnerId] = [
                                        'content' => $message['content'],
                                        'timestamp' => $message['timestamp']
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }
    
        // Return view with doctors, tele-secretariats, and the last messages
        return view('chatTe', [
            'doctors' => $doctors,
            'teleSecretariats' => $teleSecretariats,
            'lastMessages' => $lastMessages, // Ensure lastMessages is passed
            'userRole' => $userRole,
        ]);
    }
   // Remplacez les méthodes index() et showForm() par ceci :
   // Dans TeleseceteriatDoctorsController.php
   public function showChat($doctorUserId = null, $teleSecretariatUserId = null)
{
    $user = auth()->user();

    if (!$user->doctor && !$user->telesecretariat) {
        return redirect()->route('login')->with('error', 'Accès réservé.');
    }

    // Récupération des contacts selon le rôle
    if ($user->doctor) {
        // Pour les docteurs: récupère les télésecrétaires associés
        $partners = DoctorAssociate::where('doctor_id', $user->doctor->id)
            ->with(['user' => function($query) {
                $query->whereHas('telesecretariat');
            }])
            ->get()
            ->pluck('user')
            ->filter();
        \Log::info('Telesecretaries fetched for doctor', ['partners' => $partners]);
    } elseif ($user->telesecretariat) {
        // Pour les télésecrétaires: récupère seulement les médecins associés
        $partners = DoctorAssociate::where('user_id', $user->id)
    ->with(['doctorModel.user'])  // Utilise la nouvelle relation
    ->get()
    ->map(function ($assoc) {
        return $assoc->doctorModel->user ?? null;
    })
    ->filter();
    }

    // PARTIE 1: Derniers messages de toutes les conversations
    $lastMessages = [];
    $allChatsResponse = Http::get('https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app/chatTE.json');
    $allChats = $allChatsResponse->json() ?? [];

    foreach ($allChats as $chatId => $chat) {
        if (isset($chat['messages'])) {
            foreach ($chat['messages'] as $message) {
                $partnerId = ($message['sender_id'] == auth()->id()) 
                    ? $message['receiver_id'] 
                    : $message['sender_id'];

                if (!isset($lastMessages[$partnerId]) || 
                    $message['timestamp'] > $lastMessages[$partnerId]['timestamp']) {
                    $lastMessages[$partnerId] = [
                        'content' => $message['content'] ?? null,
                        'timestamp' => $message['timestamp']
                    ];
                }
            }
        }
    }

    // PARTIE 2: Messages de la conversation actuelle
    $messagesArray = [];
    $chatId = null;

    // Determine partner user ID based on role
    $partnerUserId = $user->doctor ? $teleSecretariatUserId : $doctorUserId;

    if ($partnerUserId) {
        $chatId = $this->getChatId(auth()->id(), $partnerUserId);
        $currentChatResponse = Http::get("https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app/chatTE/{$chatId}/messages.json");
        $currentChatMessages = $currentChatResponse->json() ?? [];

        $messagesArray = collect($currentChatMessages)->map(function ($message, $key) {
            return [
                'id' => $key,
                'sender_id' => $message['sender_id'],
                'sender_name' => User::find($message['sender_id'])->name,
                'receiver_id' => $message['receiver_id'],
                'receiver_name' => User::find($message['receiver_id'])->name,
                'content' => $message['content'] ?? null,
                'timestamp' => $message['timestamp'],
                'file_url' => $message['file_url'] ?? null,
            ];
        })->sortBy('timestamp')->values()->all();
    }

    return view('chatTe', [
        'partners' => $partners,
        'lastMessages' => $lastMessages,
        'messages' => $messagesArray,
        'chatId' => $chatId,
        'teleSecretariatUserId' => $teleSecretariatUserId,
        'doctorUserId' => $doctorUserId,
        'currentUser' => $user,
    ]);
}
   
private function getChatId($senderId, $receiverId)
{
    return $senderId < $receiverId
        ? $senderId . '-' . $receiverId
        : $receiverId . '-' . $senderId;
}




public function sendMessage(Request $request)
{
    $request->validate([
        'message' => 'nullable|string|max:255',
        'receiver_id' => 'required|exists:users,id',
        'file' => 'nullable|file|max:2048',
    ]);

    $senderId = auth()->id();
    $receiverId = $request->input('receiver_id');
    $chatId = $this->getChatId($senderId, $receiverId);

    $messageContent = $request->input('message');
    $fileUrl = null;

    // Gestion du fichier
    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $path = $file->store('chat_files', 'public'); // Stocker dans storage/app/public/chat_=s
        $fileUrl = asset('storage/' . $path); // Générer une URL comme http://yourdomain.com/storage/chat_e.ext
    }


    // Données du message
    $data = [
        'id' => Str::uuid()->toString(),
        'content' => $messageContent,
        'file_url' => $fileUrl,
        'timestamp' => now()->timestamp,
        'sender_id' => $senderId,
        'sender_name' => auth()->user()->name,
        'receiver_id' => $receiverId,
        'receiver_name' => User::find($receiverId)->name,
    ];

    // URL Firebase pour le chat spécifique
    $firebase_url = 'https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app/chatTE/' . $chatId . '/messages.json';

    // Envoyer le message à Firebase
    $response = Http::post($firebase_url, $data);

    if ($response->successful()) {
        return back()->with('success', 'Message envoyé avec succès');
    } else {
        return back()->with('error', 'Échec de l\'envoi du message');
    }
}



}
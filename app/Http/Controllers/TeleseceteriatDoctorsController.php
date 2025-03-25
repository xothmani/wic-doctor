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
    
        // Log pour déboguer
        \Log::info('Messages récupérés depuis Firebase:', [
            'chatId' => $chatId,
            'messages' => $messages
        ]);
    
        $messagesArray = [];
        if (is_array($messages)) {
            foreach ($messages as $key => $message) {
                $messagesArray[] = [
                    'id' => $key,
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
            $teleSecretariats = DoctorTelesecretariat::where('doctor_id', $userId)
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
    public function showChat($doctorUserId, $teleSecretariatUserId)
{
    // Vérification de l'autorisation
    if (auth()->id() != $doctorUserId) {
        abort(403, 'Unauthorized action.');
    }

    // Récupération des télésecrétariats associés
    $teleSecretariats = DoctorTelesecretariat::where('doctor_id', auth()->user()->doctor->id)
        ->with('telesecretariat')
        ->get();

    // PARTIE 1: Messages de la conversation actuelle
    $chatId = $this->getChatId($doctorUserId, $teleSecretariatUserId);
    
    // Récupération des messages depuis Firebase
    $currentChatResponse = Http::get("https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app/chatTE/{$chatId}/messages.json");
    $currentChatMessages = $currentChatResponse->json() ?? [];

    // Formatage des messages
    $messagesArray = collect($currentChatMessages)->map(function ($message, $key) {
        return [
            'id' => $key,
            'sender_id' => $message['sender_id'],
            'sender_name' => User::find($message['sender_id'])->name,
            'receiver_id' => $message['receiver_id'],
            'receiver_name' => User::find($message['receiver_id'])->name,
            'content' => $message['content'],
            'timestamp' => $message['timestamp'],
            'file_url' => $message['file_url'] ?? null,
        ];
    })->sortBy('timestamp')->values()->all();

    // PARTIE 2: Derniers messages de toutes les conversations
    $lastMessages = [];
    $allChatsResponse = Http::get('https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app/chatTE.json');
    $allChats = $allChatsResponse->json() ?? [];

    foreach ($allChats as $chatId => $chat) {
        if (isset($chat['messages'])) {
            foreach ($chat['messages'] as $message) {
                $partnerId = ($message['sender_id'] == auth()->id()) 
                    ? $message['receiver_id'] 
                    : $message['sender_id'];

                // Mise à jour du dernier message si plus récent
                if (!isset($lastMessages[$partnerId]) || 
                    $message['timestamp'] > $lastMessages[$partnerId]['timestamp']) {
                    $lastMessages[$partnerId] = [
                        'content' => $message['content'],
                        'timestamp' => $message['timestamp']
                    ];
                }
            }
        }
    }

    // Récupération des infos du télésecrétariat
    $teleSecretariatUser = User::find($teleSecretariatUserId);
    $teleSecretariat = DoctorTelesecretariat::where('telesecretariat_id', $teleSecretariatUserId)->first();

    return view('chatTe', [
        'chatId' => $chatId,
        'messages' => $messagesArray, // Messages de la conversation actuelle
        'lastMessages' => $lastMessages, // Derniers messages pour toutes les conversations
        'teleSecretariat' => $teleSecretariat,
        'teleSecretariatUser' => $teleSecretariatUser,
        'teleSecretariats' => $teleSecretariats,
        'doctorUserId' => $doctorUserId,
        'teleSecretariatUserId' => $teleSecretariatUserId,
    ]);
}
    
public function index()
{
    // Vérifiez si l'utilisateur authentifié a un profil docteur
    if (!Auth::user()->doctor) {
        return redirect()->route('login')->with('error', 'Vous devez être un docteur pour accéder à cette page.');
    }

    // Récupérer l'ID du docteur authentifié
    $authenticatedDoctorId = Auth::user()->doctor->id;

    // Filtrer les télésécrétariats du docteur authentifié et récupérer les informations sur les télésécrétariats
    $telesecretariats = DoctorTelesecretariat::where('doctor_id', $authenticatedDoctorId)
        ->with('telesecretariat')
        ->get();

    // Initialiser Firebase

    // Tableau pour stocker les derniers messages
    $lastMessages = [];

    // Récupérer les derniers messages pour chaque télésécrétariat
    foreach ($telesecretariats as $telesecretariat) {
        $partnerId = $telesecretariat->telesecretariat->id; // ID du télésécrétariat (partenaire)

        // Récupérer les messages entre le docteur et le télésécrétariat depuis Firestore

        // Extraire les données du dernier message
     

        // Récupérer le dernier message
    }

    // Passer les données à la vue
    return view('chatTe', compact('telesecretariats', 'lastMessages'));
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
        'file' => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
    ]);

    $senderId = auth()->id();
    $receiverId = $request->input('receiver_id');
    $chatId = $this->getChatId($senderId, $receiverId);

    $messageContent = $request->input('message');
    $fileUrl = null;

    // Gestion du fichier
    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $path = $file->store('chat_files');
        $fileUrl = Storage::url($path);
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
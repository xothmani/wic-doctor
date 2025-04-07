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
   // Remplacez les méthodes index() et showForm() par ceci :
public function showChat($doctorUserId = null, $teleSecretariatUserId = null)
{
    // Vérification si l'utilisateur est un docteur
    $user = auth()->user();
    if (!$user->doctor) {
        return redirect()->route('login')->with('error', 'Accès réservé aux docteurs.');
    }

    // Récupération des télésecrétariats associés (TOUJOURS chargés)
    $teleSecretariats = DoctorTelesecretariat::where('doctor_id', $user->doctor->id)
        ->with('telesecretariat')
        ->get();

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

    // PARTIE 2: Messages de la conversation actuelle (si ID présent)
    $messagesArray = [];
    if ($teleSecretariatUserId) {
        $chatId = $this->getChatId(auth()->id(), $teleSecretariatUserId);
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
        'teleSecretariats' => $teleSecretariats,
        'lastMessages' => $lastMessages,
        'messages' => $messagesArray,
        'chatId' => $chatId, // Ajoutez cette ligne pour passer le chatId à la vue

        'teleSecretariatUserId' => $teleSecretariatUserId,
        'doctorUserId' => auth()->id(),
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
        $path = $file->store('chat_files', 'public');
        $fileUrl = asset('storage/' . $path);
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
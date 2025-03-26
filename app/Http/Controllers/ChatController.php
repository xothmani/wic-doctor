<?php
namespace App\Http\Controllers;

use App\Models\Doctor; 
use App\Models\User; 

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage; // For storing file locally or on cloud
use Illuminate\Support\Str;
use Firebase\JWT\JWT;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Illuminate\Support\Facades\Auth;


class ChatController extends Controller
{
    // public function deleteMessage($id)
    // {
    //     // Construire l'URL pour accéder au message spécifique dans Firebase
    //     $firebaseMessageUrl = 'https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app/chats/' . $id . '.json';
    
    //     // Récupérer le message depuis Firebase afin de vérifier son existence et vérifier l'auteur
    //     $response = Http::get($firebaseMessageUrl);
    //     $message = $response->json();
    
    //     if (!$message) {
    //         return response()->json(['success' => false, 'message' => 'Message not found'], 404);
    //     }
    
    //     // Vérification que l'utilisateur connecté est bien l'auteur du message
    //     // (Assurez-vous que votre structure de message dans Firebase comporte bien une clé "sender_id")
    //     if (!isset($message['sender_id']) || $message['sender_id'] !== auth()->id()) {
    //         return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    //     }
    
    //     // Envoyer la requête DELETE à Firebase
    //     $deleteResponse = Http::delete($firebaseMessageUrl);
    
    //     // Vous pouvez vérifier ici la réponse si besoin (Firebase retourne généralement null pour une suppression réussie)
    //     if ($deleteResponse->successful()) {
    //         return response()->json(['success' => true, 'message' => 'Message deleted successfully']);
    //     } else {
    //         return response()->json(['success' => false, 'message' => 'Error deleting message'], $deleteResponse->status());
    //     }
    // }
   public function markNotificationsAsRead(Request $request)
{
    $userId = auth()->id();
    $firebaseUrl = 'https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app/chats.json';
    $response = Http::get($firebaseUrl);
    $chats = $response->json();

    if (!empty($chats)) {
        foreach ($chats as $chatId => $chat) {
            if (isset($chat['messages'])) {
                foreach ($chat['messages'] as $key => $message) {
                    if ($message['receiver_id'] == $userId && !isset($message['read'])) {
                        $chats[$chatId]['messages'][$key]['read'] = true; // Marquer comme lu
                    }
                }
            }
        }

        // Mettre à jour Firebase
        Http::put($firebaseUrl, $chats);
    }

    return response()->json(['success' => true]);
}
    public function getLastMessage(Request $request)
    {
        try {
            $userId = auth()->id(); // Récupérer l'ID de l'utilisateur connecté
            $firebaseUrl = 'https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app/chats.json';
    
            // Récupérer tous les chats depuis Firebase
            $response = Http::get($firebaseUrl);
            $chats = $response->json();
    
            if (!$chats) {
                return response()->json(['error' => 'Aucun chat trouvé'], 404);
            }
    
            // Trouver le dernier message destiné à l'utilisateur connecté
            $lastMessage = null;
            foreach ($chats as $chatId => $chat) {
                if (isset($chat['messages'])) {
                    foreach ($chat['messages'] as $message) {
                        if ($message['receiver_id'] == $userId) {
                            if (!$lastMessage || $message['timestamp'] > $lastMessage['timestamp']) {
                                $lastMessage = $message;
                            }
                        }
                    }
                }
            }
    
            if ($lastMessage) {
                return response()->json([
                    'content' => $lastMessage['content'],
                    'sender_name' => $lastMessage['sender_name'],
                ]);
            } else {
                return response()->json(['error' => 'Aucun message trouvé'], 404);
            }
        } catch (\Exception $e) {
            \Log::error('Erreur dans getLastMessage:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Erreur interne du serveur'], 500);
        }
    }
   
    
    
    
public function fetchMessages($doctorId)
{
    $firebase_url = 'https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app/chats/' . $doctorId . '/messages.json';
    $response = Http::get($firebase_url);
    $messages = $response->json();

    $messagesArray = [];
    // Si $messages est un objet avec des clés représentant les ids
    if(is_array($messages)) {
        foreach ($messages as $key => $message) {
            $message['id'] = $key; // Assigner la clé comme identifiant
            $sender = User::find($message['sender_id']);
            $senderName = $sender ? $sender->name : 'Unknown Sender';
            $receiver = Doctor::find($message['receiver_id']);
            $receiverName = $receiver ? $receiver->name : 'Unknown Doctor';

            $messagesArray[] = [
                'id' => $message['id'],
                'sender_id' => $message['sender_id'],
                'sender_name' => $senderName,
                'receiver_id' => $message['receiver_id'],
                'receiver_name' => $receiverName,
                'content' => $message['content'],
                'timestamp' => $message['timestamp'],
                'file_url' => $message['file_url'] ?? null,
            ];
        }
    }

    return response()->json(['messages' => $messagesArray]);
}

public function index() {
    // Initialiser Firebase
    $firebase = (new Factory)->withServiceAccount('/path/to/firebase/credentials.json');
    $firestore = $firebase->createFirestore();

    // Récupérer les médecins
    $doctors = User::where('role', 'doctor')->get();

    // Récupérer les derniers messages pour chaque conversation
    $lastMessages = [];

    foreach ($doctors as $doctor) {
        // Récupérer les messages de la conversation entre l'utilisateur connecté et le médecin depuis Firestore
        $messagesRef = $firestore->collection('messages')
            ->where('sender_id', 'in', [auth()->id(), $doctor->id])
            ->where('receiver_id', 'in', [auth()->id(), $doctor->id])
            ->orderBy('created_at', 'desc')
            ->limit(1); // Nous voulons juste le dernier message

        // Extraire les données du message
        $lastMessageSnapshot = $messagesRef->documents();
        if ($lastMessageSnapshot->isEmpty()) {
            continue;
        }

        $lastMessage = $lastMessageSnapshot->rows()[0];
        $lastMessages[$doctor->id] = $lastMessage->data(); // Ajoute les données du dernier message
    }

    // Passer les données à la vue
    return view('chat', compact('doctors', 'lastMessages'));
}


public function showChat($userId, $doctorUserId)
{
    if (auth()->id() != $userId) {
        abort(403, 'Unauthorized action.');
    }

    $chatId = $this->getChatId($userId, $doctorUserId);
    $firebaseUrl = 'https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app/chats/' . $chatId . '/messages.json';
    $response = Http::get($firebaseUrl);
    
    // Ajouter une validation des données
    $messages = $response->successful() ? $response->json() : [];

    $messagesArray = collect($messages)->map(function ($message, $key) {
        // Fournir des valeurs par défaut pour toutes les clés
        $message = array_merge([
            'id' => Str::uuid()->toString(),
            'sender_id' => null,
            'receiver_id' => null,
            'content' => '',
            'timestamp' => now()->timestamp,
            'file_url' => null
        ], $message);

        // Récupération sécurisée des noms
        $sender = User::find($message['sender_id']);
        $receiver = User::find($message['receiver_id']);

        return [
            'id' => $message['id'] ?? $key,
            'sender_id' => $message['sender_id'],
            'sender_name' => $sender?->name ?? 'Unknown',
            'receiver_name' => $receiver?->name ?? 'Unknown',
            'content' => $message['content'],
            'timestamp' => $message['timestamp'],
            'file_url' => $message['file_url']
        ];
    });

    // Récupération sécurisée des derniers messages
    $lastMessages = [];
    foreach (Doctor::all() as $doctorItem) {
        $chatIdForDoctor = $this->getChatId($userId, $doctorItem->user_id);
        $firebaseUrl = 'https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app/chats/' . $chatIdForDoctor . '/messages.json';
        $response = Http::get($firebaseUrl);
        
        if ($response->successful()) {
            $messages = $response->json() ?? [];
            $lastMessage = collect($messages)->sortByDesc('timestamp')->first();
            
            $lastMessages[$doctorItem->user_id] = $lastMessage ? [
                'content' => $lastMessage['content'] ?? '[Fichier joint]',
                'timestamp' => $lastMessage['timestamp'] ?? now()->timestamp
            ] : null;
        }
    }

    return view('chat', [
        'chatId' => $chatId,
        'messages' => $messagesArray,
        'doctor' => Doctor::where('user_id', $doctorUserId)->firstOrFail(),
        'doctorUser' => User::findOrFail($doctorUserId),
        'userId' => $userId,
        'doctorUserId' => $doctorUserId,
        'doctors' => Doctor::all(),
        'lastMessages' => $lastMessages
    ]);
}   
private function getChatId($senderId, $receiverId)
    {
        return $senderId < $receiverId
            ? $senderId . '-' . $receiverId
            : $receiverId . '-' . $senderId;
    }
   
    public function showForm(Request $request)
    {
        // Fetch all doctors
        $doctors = Doctor::all();
        
        // Get the ID of the authenticated user
        $userId = auth()->id();
        
        // Firebase URL to fech messages
        $firebase_url = 'https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app/chats.json';
        $response = Http::get($firebase_url);
        $messages = $response->json();
        
        // Tableau pour stocker les derniers messages par médecin
        $lastMessages = [];
        $unreadMessages = [];
    
        if (!empty($messages)) {
            foreach ($messages as $chatId => $chat) {
                // Vérifier si l'utilisateur connecté est impliqué dans ce chat
                if (isset($chat['messages'])) {
                    $lastMessage = null;
                    foreach ($chat['messages'] as $message) {
                        // Vérifier si l'utilisateur est le destinataire ou l'expéditeur
                        if ($message['sender_id'] == $userId || $message['receiver_id'] == $userId) {
                            // Garder le dernier message
                            if (!$lastMessage || $message['timestamp'] > $lastMessage['timestamp']) {
                                $lastMessage = $message;
                            }
    
                            // Marquer les messages non lus
                            if ($message['receiver_id'] == $userId && !isset($message['read'])) {
                                $unreadMessages[$message['sender_id']] = true;
                            }
                        }
                    }
    
                    // Stocker le dernier message pour ce chat
                    if ($lastMessage) {
                        $lastMessages[$lastMessage['sender_id']] = $lastMessage;
                    }
                }
            }
        }
    
        // Trier les médecins en fonction du timestamp du dernier message
        $doctors = $doctors->sortByDesc(function ($doctor) use ($lastMessages) {
            return $lastMessages[$doctor->user_id]['timestamp'] ?? 0;
        });
    
        // Retourner la vue avec les médecins triés et les messages non lus
        return view('chat', compact('doctors', 'unreadMessages', 'lastMessages'));
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
    
        // Garantir un contenu même vide
        $messageContent = $request->input('message') ?? '';
    
        $fileUrl = null;
    
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('chat_files', 'public');
            $fileUrl = asset('storage/' . $path);
        }
    
        $firebase_url = 'https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app/chats/' . $chatId . '/messages.json';
    
        $data = [
            'id' => Str::uuid()->toString(),
            'content' => $messageContent, // Toujours présent (même vide)
            'file_url' => $fileUrl,
            'timestamp' => now()->timestamp,
            'sender_id' => $senderId,
            'sender_name' => auth()->user()->name,
            'receiver_id' => $receiverId,
            'receiver_name' => User::find($receiverId)->name,
        ];
    
        $response = Http::post($firebase_url, $data);
    
        return $response->successful()
            ? back()->with('success', 'Message envoyé avec succès')
            : back()->with('error', 'Échec de l\'envoi du message');
    }
}
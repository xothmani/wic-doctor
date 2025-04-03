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

    public function deleteMessage($chatId, $messageId)
    {
        // Récupérer l'ID de l'utilisateur connecté
        $userId = auth()->id();
    
        // URL Firebase pour récupérer les messages du chat correspondant
        $firebaseUrl = "https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app/chats/{$chatId}/messages.json";
    
        // Récupérer tous les messages depuis Firebase
        $response = Http::get($firebaseUrl);
        $messages = $response->json();
    
        // Vérifier si les messages existent
        if (!$messages) {
            return response()->json(['success' => false, 'message' => 'Aucun message trouvé'], 404);
        }
    
        // Trouver l'ID Firebase correspondant au messageId
        $firebaseMessageId = null;
        foreach ($messages as $key => $message) {
            if (isset($message['id']) && $message['id'] === $messageId && $message['sender_id'] === $userId) {
                $firebaseMessageId = $key;
                break;
            }
        }
    
        if ($firebaseMessageId) {
            // URL pour supprimer le message
            $deleteUrl = "https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app/chats/{$chatId}/messages/{$firebaseMessageId}.json";
    
            // Envoyer la requête DELETE
            $deleteResponse = Http::delete($deleteUrl);
    
            if ($deleteResponse->successful()) {
                return response()->json(['success' => true, 'message' => 'Message deleted successfully']);
            } else {
                return response()->json(['success' => false, 'message' => 'Failed to delete message'], 500);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Message not found'], 404);
        }
    }       public function markNotificationsAsRead(Request $request)
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
    // Vérification de l'autorisation
    if (auth()->id() != $userId) {
        abort(403, 'Unauthorized action.');
    }

    // Récupérer la liste des médecins avec leurs derniers messages (comme dans la page principale)
    $doctorsData = $this->getDoctorsWithLastMessages($userId);
    $sortedDoctors = $doctorsData['sortedDoctors'];
    $lastMessages = $doctorsData['lastMessages'];

    // Récupérer les messages du chat actuel
    $chatId = $this->getChatId($userId, $doctorUserId);
    $currentMessages = $this->getChatMessages($chatId);

    // Récupérer les infos du médecin actuel
    $currentDoctor = Doctor::where('user_id', $doctorUserId)->first();
    $currentDoctorUser = User::find($doctorUserId);

    return view('chat', [
        'chatId' => $chatId,
        'messages' => $currentMessages,
        'doctor' => $currentDoctor,
        'doctorUser' => $currentDoctorUser,
        'userId' => $userId,
        'doctorUserId' => $doctorUserId,
        'doctors' => $sortedDoctors,
        'lastMessages' => $lastMessages,
        'activeDoctorId' => $doctorUserId // Nouvelle variable pour la vue
    ]);
}

private function getDoctorsWithLastMessages($userId)
{
    $doctors = Doctor::with('user')->get();
    $lastMessages = [];

    foreach ($doctors as $doctor) {
        $chatId = $this->getChatId($userId, $doctor->user_id);
        $firebaseUrl = "https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app/chats/$chatId/messages.json";
        $response = Http::get($firebaseUrl);
        $messages = $response->json() ?? [];
        $lastMessages[$doctor->user_id] = collect($messages)->sortByDesc('timestamp')->first();
    }

    $sortedDoctors = $doctors->sortByDesc(function ($doctor) use ($lastMessages) {
        return $lastMessages[$doctor->user_id]['timestamp'] ?? 0;
    });

    return [
        'sortedDoctors' => $sortedDoctors,
        'lastMessages' => $lastMessages
    ];
}

private function getChatMessages($chatId)
{
    $firebaseUrl = "https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app/chats/$chatId/messages.json";
    $response = Http::get($firebaseUrl);
    $messages = $response->json() ?? [];

    return collect($messages)->map(function ($message, $key) {
        $sender = User::find($message['sender_id']);
        return [
            'id' => $message['id'] ?? $key,
            'sender_id' => $message['sender_id'],
            'sender_name' => $sender->name ?? 'Unknown',
            'content' => $message['content'],
            'timestamp' => $message['timestamp'],
            'file_url' => $message['file_url'] ?? null,
        ];
    });
}
private function getChatId($senderId, $receiverId)
    {
        return $senderId < $receiverId
            ? $senderId . '-' . $receiverId
            : $receiverId . '-' . $senderId;
    }
   
   // Dans ChatController.php
public function showForm()
{
    $userId = auth()->id();
    
    // Utiliser la même méthode que showChat
    $doctorsData = $this->getDoctorsWithLastMessages($userId);
    
    return view('chat', [
        'doctors' => $doctorsData['sortedDoctors'],
        'lastMessages' => $doctorsData['lastMessages'],
        'activeDoctorId' => null // Aucun médecin sélectionné
    ]);
}
    public function sendMessage(Request $request)
{
    $request->validate([
        'message' => 'nullable|string|max:255',
        'receiver_id' => 'required|exists:users,id',
        'file' => 'nullable|file|max:2048', // Ajoutez 'txt' ici
    ]);

    $senderId = auth()->id();
    $receiverId = $request->input('receiver_id');
    $chatId = $this->getChatId($senderId, $receiverId);

    $messageContent = $request->input('message');
    $fileUrl = null;

    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $path = $file->store('chat_files', 'public'); // Stocker dans storage/app/public/chat_files
        $fileUrl = asset('storage/' . $path); // Générer une URL comme http://yourdomain.com/storage/chat_files/filename.ext
    }

    $firebase_url = 'https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app/chats/' . $chatId . '/messages.json';

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

    $response = Http::post($firebase_url, $data);

    if ($response->successful()) {
        return back()->with('success', 'Message envoyé avec succès');
    } else {
        return back()->with('error', 'Échec de l\'envoi du message');
    }
}
}





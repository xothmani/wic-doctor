<?php

namespace App\Http\Controllers;

use App\Services\FirestoreService;
use Illuminate\Support\Str;

use Illuminate\Http\Request;
use App\Models\DoctorPatients;
use App\Models\Doctor;
use Google\Cloud\Firestore\FieldValue;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Google\Cloud\Firestore\FirestoreClient;

class PatientDoctorChatController extends Controller
{
    protected $firestore;

    public function __construct(FirestoreService $firestore)
    {
        $this->firestore = $firestore;
    }
    public function showForm(Request $request)
    {
        $user = auth()->user();
        $userId = $user->id;
        $isDoctor = $user->doctor !== null;
    
        // Récupérer les relations
        $relationships = $isDoctor 
            ? DoctorPatients::where('doctor_id', $user->doctor->id)->with('patient.user')->get()
            : DoctorPatients::where('patient_id', $user->patient->id)->with('doctor.user')->get();
    
        $conversations = [];
        $unreadMessages = [];
    
        foreach ($relationships as $rel) {
            $otherUser = $isDoctor ? $rel->patient->user : $rel->doctor->user;
            $chatId = $this->getChatId($userId, $otherUser->id);
    
            // Récupérer les messages depuis Firestore
            $messages = $this->firestore->getDocuments("messages/$chatId/chats");
            $lastMessage = null;
    
            foreach ($messages as $messageDoc) {
                $fields = $messageDoc['fields'] ?? [];
                $message = [
                    'sender_id' => $fields['sender_id']['stringValue'] ?? '',
                    'receiver_id' => $fields['receiver_id']['stringValue'] ?? '',
                    'timestamp' => $fields['timestamp']['integerValue'] ?? 0,
                    'read' => $fields['read']['booleanValue'] ?? false
                ];
    
                if ($message['receiver_id'] == $userId && !$message['read']) {
                    $unreadMessages[$message['sender_id']] = true;
                }
    
                if (!$lastMessage || $message['timestamp'] > $lastMessage['timestamp']) {
                    $lastMessage = $message;
                }
            }
    
            $conversations[] = [
                'user_id' => $otherUser->id,
                'name' => $otherUser->name,
                'last_message' => $lastMessage
            ];
        }
    
        // Trier par timestamp
        usort($conversations, function ($a, $b) {
            return ($b['last_message']['timestamp'] ?? 0) <=> ($a['last_message']['timestamp'] ?? 0);
        });
    
        return view('chatPD', compact('conversations', 'isDoctor'));
    }
    private function generateChatId($id1, $id2)
    {
        $sorted = [$id1, $id2];
        sort($sorted);
        return hash('sha256', implode('_', $sorted));
    }
    public function showChat($doctorUserId, $patientUserId)
    {
        $user = auth()->user();
    
        // Vérifier l'authentification
        if (!$user) {
            abort(403, 'Unauthorized access.');
        }
    
        // Déterminer le rôle de l'utilisateur
        $isDoctor = $user->doctor !== null;
        $isPatient = $user->patient !== null;
    
        if (!$isDoctor && !$isPatient) {
            abort(403, 'Unauthorized access. You must be a doctor or a patient.');
        }
    
        // Récupérer les relations associées
        if ($isDoctor) {
            $patients = DoctorPatients::where('doctor_id', $user->doctor->id)
                ->with('patient')
                ->get();
            $doctors = null;
        } else {
            $doctors = DoctorPatients::where('patient_id', $user->patient->id)
                ->with('doctor')
                ->get();
    
            $patients = DoctorPatients::where('patient_id', $user->patient->id)
                ->with('doctor')
                ->get();
        }
    
        // Générer le chat ID
        $chatId = $this->getChatId($doctorUserId, $patientUserId);
    
        // Récupérer les messages depuis Firestore
        $messages = $this->firestore->getDocuments("messages/$chatId/chats");
    
        // Formatage et tri des messages
        $messagesArray = collect($messages)->map(function ($message) {
            $fields = $message['fields'] ?? [];
    
            // Récupérer et convertir les IDs en chaînes pour une comparaison fiable
            $senderId = isset($fields['sender_id']['stringValue'])
                ? $fields['sender_id']['stringValue']
                : (isset($fields['sender_id']['integerValue']) ? (string)$fields['sender_id']['integerValue'] : null);
            $receiverId = isset($fields['receiver_id']['stringValue'])
                ? $fields['receiver_id']['stringValue']
                : (isset($fields['receiver_id']['integerValue']) ? (string)$fields['receiver_id']['integerValue'] : null);
    
            $timestamp = isset($fields['timestamp']['integerValue']) ? (int)$fields['timestamp']['integerValue'] : 0;
    
            return [
                'id'            => $message['name'] ?? null, // ID du document Firestore
                'sender_id'     => $senderId,
                'sender_name'   => $fields['sender_name']['stringValue'] ?? 'Unknown',
                'receiver_id'   => $receiverId,
                'receiver_name' => $fields['receiver_name']['stringValue'] ?? 'Unknown',
                'content'       => $fields['text']['stringValue'] ?? '',
                'timestamp'     => $timestamp,
                'file_url'      => $fields['file_url']['stringValue'] ?? null,
            ];
        })->sortBy('timestamp')->values()->all();
    
        // Récupérer les informations du patient sélectionné
        $patientUser = User::find($patientUserId);
        $patient = DoctorPatients::where('patient_id', $patientUserId)->first();
    
        return view('chatDP', [
            'chatId' => $chatId,
            'messages' => $messagesArray,
            'patient' => $patient,
            'patientUser' => $patientUser,
            'patients' => $patients,
            'doctors' => $doctors,
            'doctorUserId' => $doctorUserId,
            'patientUserId' => $patientUserId,
        ]);
    }
    
        private function getChatId($senderId, $receiverId)
    {
        return $senderId < $receiverId
            ? $senderId . '-' . $receiverId
            : $receiverId . '-' . $senderId;
    }

    // Dans PatientDoctorChatController.php
public function fetchMessages($receiverId)
{
    $senderId = auth()->id();
    $chatId = $senderId < $receiverId ? "{$senderId}-{$receiverId}" : "{$receiverId}-{$senderId}";

    // CHEMIN CORRIGÉ : 'messages/{chatId}/chats'
    $firestoreUrl = "https://firestore.googleapis.com/v1/projects/wic-doctor-b83e0/databases/(default)/documents/messages/{$chatId}/chats";
    
    $response = Http::get($firestoreUrl);
    $messages = [];

    if ($response->successful()) {
        $data = $response->json();
        if (isset($data['documents'])) {
            foreach ($data['documents'] as $doc) {
                $fields = $doc['fields'];
                $messages[] = [
                    'id' => $doc['name'], // ID Firestore
                    'content' => $fields['text']['stringValue'] ?? '',
                    'sender_id' => (int)$fields['sender_id']['integerValue'] ?? '',
                    'timestamp' => $fields['timestamp']['integerValue'] ?? 0,
                    'sender_name' => $fields['sender_name']['stringValue'] ?? '',
                    'file_url' => $fields['file_url']['stringValue'] ?? null
                ];
            }
        }
    }

    return response()->json(['messages' => $messages]);
}
        public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string|max:255',
            'receiver_id' => 'required|exists:users,id',
            'file' => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
        ]);
    
        try {
            $sender = auth()->user();
            $receiver = User::findOrFail($request->receiver_id);
    
            $data = [
                'id' => Str::uuid()->toString(),
                'text' => $request->input('message'),
                'file_url' => $request->hasFile('file') 
                    ? Storage::url($request->file('file')->store('chat_files'))
                    : null,
                'timestamp' => time(), // Remplacement de FieldValue::serverTimestamp()
                'sender_id' => $sender->id,
                'sender_name' => $sender->name,
                'receiver_id' => $receiver->id,
                'receiver_name' => $receiver->name,
            ];
    
            $conversationId = $this->getChatId($sender->id, $receiver->id);
            
            // Utilisation correcte de addDocument
            $this->firestore->addDocument("messages/$conversationId/chats", $data);
    
            return response()->json(['status' => 'Message sent!']);
    
        } catch (\Exception $e) {
            \Log::error('Message send error: '.$e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function index(Request $request)
    {
        $user = auth()->user();
        $isDoctor = $user->doctor !== null;
        $isPatient = $user->patient !== null;
        $formattedConversations = [];
    
        // Récupérer les relations (patients ou médecins)
        $relationships = $isDoctor 
            ? DoctorPatients::where('doctor_id', $user->doctor->id)->with('patient')->get()
            : DoctorPatients::where('patient_id', $user->patient->id)->with('doctor')->get();
    
        foreach ($relationships as $rel) {
            $target = $isDoctor ? $rel->patient : $rel->doctor;
            $otherUser = $target->user ?? null;
    
            if ($otherUser) {
                $chatId = $this->getChatId($user->id, $otherUser->id);
    
                // Récupération des messages depuis Firestore
                $messages = $this->firestore->getDocuments("messages/$chatId/chats");
                $lastMessage = null;
    
                foreach ($messages as $messageDoc) {
                    $fields = $messageDoc['fields'] ?? [];
                    $message = [
                        'content' => $fields['text']['stringValue'] ?? '...',
                        'timestamp' => $fields['timestamp']['integerValue'] ?? 0
                    ];
    
                    if (!$lastMessage || $message['timestamp'] > $lastMessage['timestamp']) {
                        $lastMessage = $message;
                    }
                }
    
                $formattedConversations[] = [
                    'id' => $target->id,
                    'user_id' => $otherUser->id,
                    'name' => $otherUser->name,
                    'last_message' => $lastMessage ? [
                        'content' => $lastMessage['content'],
                        'timestamp' => $lastMessage['timestamp'],
                        'time' => date('H:i', $lastMessage['timestamp'])
                    ] : null
                ];
            }
        }
    
        // Tri des conversations
        usort($formattedConversations, function ($a, $b) {
            return ($b['last_message']['timestamp'] ?? 0) <=> ($a['last_message']['timestamp'] ?? 0);
        });
    
        // Passer les données à la vue
        return view('chatDP', [
            'formattedConversations' => $formattedConversations,
            'isDoctor' => $isDoctor,
            'isPatient' => $isPatient,
            'user' => $user,
        ]);
    }
    
}
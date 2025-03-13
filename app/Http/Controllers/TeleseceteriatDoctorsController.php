<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Telesecretariat;

use App\Models\DoctorTelesecretariat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
    
        if ($userRole === 'doctor') {
            // Récupérer les télésecrétariats associés au médecin
            $teleSecretariats = DoctorTelesecretariat::where('doctor_id', $userId)
                ->with('telesecretariat')
                ->get()
                ->pluck('telesecretariat');
    
            if ($teleSecretariats->isEmpty()) {
                $teleSecretariats = collect();
            }
    
            $doctors = null;
        } elseif ($userRole === 'telesecretariat') {
            // Récupérer les médecins associés à ce télésecrétariat
            $doctors = DoctorTelesecretariat::where('telesecretariat_id', $userId)
                ->with('doctor')
                ->get()
                ->pluck('doctor');
    
            if ($doctors->isEmpty()) {
                $doctors = collect();
            }
    
            $teleSecretariats = null;
        } else {
            abort(403, 'Unauthorized action.');
        }
    
        return view('chatTe', [
            'doctors' => $doctors,
            'teleSecretariats' => $teleSecretariats,
            'userRole' => $userRole,
        ]);
    }
   
   
   
    public function index(Request $request)
    {
        $user = auth()->user();
    
        // Vérifier si l'utilisateur est authentifié
        if (!$user) {
            abort(403, 'Unauthorized access.');
        }
    
        // Vérifier si l'utilisateur est un médecin ou un télésecrétariat
        $isDoctor = $user->doctor !== null;
        $isTeleSecretariat = $user->telesecretariat !== null;
    
        if (!$isDoctor && !$isTeleSecretariat) {
            abort(403, 'Unauthorized access. You must be a doctor or a tele-secretary.');
        }
    
        // Récupérer les conversations associées à l'utilisateur
        if ($isDoctor) {
            // Pour un médecin, récupérer les télésecrétariats associés
            $conversations = DoctorTelesecretariat::where('doctor_id', $user->doctor->id)
                ->with('telesecretariat')
                ->get();
        } else {
            // Pour un télésecrétariat, récupérer les médecins associés
            $conversations = DoctorTelesecretariat::where('telesecretariat_id', $user->telesecretariat->id)
                ->with('doctor')
                ->get();
        }
    
        // Tableau pour stocker les conversations avec le dernier message
        $formattedConversations = [];
    
        foreach ($conversations as $conversation) {
            // Déterminer l'utilisateur de la conversation (médecin ou télésecrétariat)
            $otherUser = $isDoctor ? $conversation->telesecretariat : $conversation->doctor;
    
            // Générer l'ID de la conversation
            $chatId = $this->getChatId(
                $isDoctor ? $user->id : $otherUser->user_id,
                $isDoctor ? $otherUser->user_id : $user->id
            );
    
            // Récupérer les messages de Firebase
            $firebaseUrl = 'https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app/chatTE/' . $chatId . '/messages.json';
            $response = Http::get($firebaseUrl);
            $messages = $response->json() ?? [];
    
            // Récupérer le dernier message
            $lastMessage = null;
            if (!empty($messages)) {
                $lastMessage = collect($messages)->sortByDesc('timestamp')->first();
            }
    
            // Formater la conversation
            $formattedConversations[] = [
                'id' => $otherUser->id,
                'name' => $isDoctor ? $otherUser->nomCentre : $otherUser->name,
                'last_message' => $lastMessage ? [
                    'content' => $lastMessage['content'],
                    'timestamp' => $lastMessage['timestamp'],
                    'time' => date('H:i', $lastMessage['timestamp']),
                ] : null,
                'user_id' => $otherUser->user_id, // Ajouter l'ID de l'utilisateur pour les liens
            ];
        }
    
        // Trier les conversations par timestamp du dernier message (du plus récent au plus ancien)
        usort($formattedConversations, function ($a, $b) {
            $timeA = $a['last_message']['timestamp'] ?? 0;
            $timeB = $b['last_message']['timestamp'] ?? 0;
            return $timeB <=> $timeA;
        });
    
        // Retourner la vue chatTe avec les données formatées
        return view('chatTe', [
            'conversations' => $formattedConversations,
            'userRole' => $isDoctor ? 'doctor' : 'telesecretariat',
        ]);
    }
    public function showChat($doctorUserId, $teleSecretariatUserId)
    {
        $user = auth()->user();
        
        // Check if the user is authenticated
        if (!$user) {
            abort(403, 'Unauthorized access.');
        }
    
        // Check if the user is a doctor or a tele-secretary
        $isDoctor = $user->doctor !== null;
        $isTeleSecretariat = $user->telesecretariat !== null;
        
        if (!$isDoctor && !$isTeleSecretariat) {
            abort(403, 'Unauthorized access. You must be a doctor or a tele-secretary.');
        }
    
        // Fetch associated tele-secretariats if the user is a doctor
        if ($isDoctor) {
            $teleSecretariats = DoctorTelesecretariat::where('doctor_id', $user->doctor->id)
                ->with('telesecretariat')
                ->get();
            $doctors = null; // No need to fetch doctors for a doctor
        } else {
            // If the user is a tele-secretary, fetch associated doctors and their conversations
            $doctors = DoctorTelesecretariat::where('telesecretariat_id', $user->telesecretariat->id)
                ->with('doctor')
                ->get();
    
            // Fetch all the doctor-tele-secretariat relationships where the tele-secretariat is involved
            $teleSecretariats = DoctorTelesecretariat::where('telesecretariat_id', $user->telesecretariat->id)
                ->with('doctor') // This will give the associated doctor
                ->get();
        }
    
        // Generate the chat ID
        $chatId = $this->getChatId($doctorUserId, $teleSecretariatUserId);
    
        // Fetch messages from Firebase
        $firebaseUrl = 'https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app/chatTE/' . $chatId . '/messages.json';
        $response = Http::get($firebaseUrl);
        $messages = $response->json() ?? [];
    
        // Format and sort messages by timestamp
        $messagesArray = collect($messages)->map(function ($message, $key) {
            return [
                'id' => $key,
                'sender_id' => $message['sender_id'],
                'sender_name' => User::find($message['sender_id'])->name ?? 'Unknown',
                'receiver_id' => $message['receiver_id'],
                'receiver_name' => User::find($message['receiver_id'])->name ?? 'Unknown',
                'content' => $message['content'],
                'timestamp' => $message['timestamp'],
                'file_url' => $message['file_url'] ?? null,
            ];
        })->sortBy('timestamp')->values()->all();
    
        // Fetch the selected tele-secretariat's information
        $teleSecretariatUser = User::find($teleSecretariatUserId);
        $teleSecretariat = DoctorTelesecretariat::where('telesecretariat_id', $teleSecretariatUserId)->first();
    
        return view('chatTe', [
            'chatId' => $chatId,
            'messages' => $messagesArray,
            'teleSecretariat' => $teleSecretariat,
            'teleSecretariatUser' => $teleSecretariatUser,
            'teleSecretariats' => $teleSecretariats,
            'doctors' => $doctors, // Pass doctors to the view if the user is a tele-secretary
            'doctorUserId' => $doctorUserId,
            'teleSecretariatUserId' => $teleSecretariatUserId,
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
            return response()->json(['status' => 'Message sent!']);
        } else {
            return response()->json(['error' => 'Failed to send message.'], 500);
        }
    }
}
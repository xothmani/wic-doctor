<?php

namespace App\Http\Controllers;

use App\Services\FirestoreService;

use Google\Client;
use Google\Service\Firestore;
use Illuminate\Support\Str;

use Illuminate\Http\Request;
use App\Models\DoctorPatients;
use App\Models\Doctor;
use Google\Cloud\Firestore\FieldValue;
use Illuminate\Support\Facades\Http;

use App\Models\User;
use Illuminate\Support\Facades\Validator;

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
                
                // Nouvelle structure de données
                $message = [
                    'id' => $fields['id']['stringValue'] ?? '',
                    'text' => $fields['text']['stringValue'] ?? '',
                    'fileUrl' => $fields['fileUrl']['stringValue'] ?? null,
                    'time' => $fields['time']['integerValue'] ?? 0,
                    'sender' => [
                        'id' => $fields['sender']['mapValue']['fields']['id']['stringValue'] ?? '',
                        'name' => $fields['sender']['mapValue']['fields']['name']['stringValue'] ?? '',
                        'imageUrl' => $fields['sender']['mapValue']['fields']['imageUrl']['stringValue'] ?? ''
                    ],
                    'receiver' => [
                        'id' => $fields['receiver']['mapValue']['fields']['id']['stringValue'] ?? '',
                        'name' => $fields['receiver']['mapValue']['fields']['name']['stringValue'] ?? '',
                        'imageUrl' => $fields['receiver']['mapValue']['fields']['imageUrl']['stringValue'] ?? ''
                    ]
                ];
    
                // Vérifier les messages non lus
                if ($message['receiver']['id'] == $userId && !isset($message['read'])) {
                    $unreadMessages[$message['sender']['id']] = true;
                }
    
                // Trouver le dernier message
                if (!$lastMessage || $message['time'] > $lastMessage['time']) {
                    $lastMessage = $message;
                }
            }
    
            $conversations[] = [
                'user_id' => $otherUser->id,
                'name' => $otherUser->name,
                'imageUrl' => $otherUser->profile_photo_url ?? asset('img/default-avatar.png'),
                'last_message' => $lastMessage ? [
                    'text' => $lastMessage['text'],
                    'time' => date('H:i', $lastMessage['time']),
                    'sender_name' => $lastMessage['sender']['name'],
                    'receiver_name' => $lastMessage['receiver']['name']
                ] : null,
                'unread' => isset($unreadMessages[$otherUser->id])
            ];
        }
    
        // Trier les conversations par time du dernier message
        usort($conversations, function ($a, $b) {
            return ($b['last_message']['time'] ?? 0) <=> ($a['last_message']['time'] ?? 0);
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
        $patients = $patients ?? collect();
    $doctors = $doctors ?? collect();
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
    
            // Nouvelle structure de données
            return [
                'id' => $fields['id']['stringValue'] ?? '',
                'text' => $fields['text']['stringValue'] ?? '',
                'fileUrl' => $fields['fileUrl']['stringValue'] ?? null,
                'time' => $fields['time']['integerValue'] ?? 0,
                'sender' => [
                    'id' => $fields['sender']['mapValue']['fields']['id']['stringValue'] ?? '',
                    'name' => $fields['sender']['mapValue']['fields']['name']['stringValue'] ?? '',
                    'imageUrl' => $fields['sender']['mapValue']['fields']['imageUrl']['stringValue'] ?? ''
                ],
                'receiver' => [
                    'id' => $fields['receiver']['mapValue']['fields']['id']['stringValue'] ?? '',
                    'name' => $fields['receiver']['mapValue']['fields']['name']['stringValue'] ?? '',
                    'imageUrl' => $fields['receiver']['mapValue']['fields']['imageUrl']['stringValue'] ?? ''
                ]
            ];
        })->sortBy('time')->values()->all();
    
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
    }      private function getChatId($senderId, $receiverId)
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
    
        $firestoreUrl = "https://firestore.googleapis.com/v1/projects/wic-doctor-b83e0/databases/(default)/documents/messages/{$chatId}/chats";
    
        $response = Http::get($firestoreUrl);
        $messages = [];
    
        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['documents'])) {
                foreach ($data['documents'] as $doc) {
                    $fields = $doc['fields'];
    
                    // Extraction des informations du sender
                    $senderFields = $fields['sender']['mapValue']['fields'];
                    $sender = [
                        'id' => $senderFields['id']['stringValue'] ?? '',
                        'name' => $senderFields['name']['stringValue'] ?? '',
                        'imageUrl' => $senderFields['imageUrl']['stringValue'] ?? '',
                    ];
    
                    // Extraction des informations du receiver
                    $receiverFields = $fields['receiver']['mapValue']['fields'];
                    $receiver = [
                        'id' => $receiverFields['id']['stringValue'] ?? '',
                        'name' => $receiverFields['name']['stringValue'] ?? '',
                        'imageUrl' => $receiverFields['imageUrl']['stringValue'] ?? '',
                    ];
    
                    $messages[] = [
                        'id' => $fields['id']['stringValue'] ?? '', // Extraction de l'ID unique
                        'text' => $fields['text']['stringValue'] ?? '',
                        'fileUrl' => $fields['fileUrl']['stringValue'] ?? null,
                        'time' => $fields['time']['integerValue'] ?? 0,
                        'sender' => $sender,
                        'receiver' => $receiver,
                        'firestore_id' => $doc['name'], // ID Firestore
                    ];
                }
            }
        }
    
        return response()->json(['messages' => $messages]);
    }

    
    public function deleteMessage($chatId, $messageId)
    {
        try {
            // Authentification avec Service Account
            $client = new Client();
            $client->setAuthConfig(storage_path(env('FIREBASE_CREDENTIALS', 'app/firebase-credentials.json')));
            $client->addScope(Firestore::CLOUD_PLATFORM);
            $token = $client->fetchAccessTokenWithAssertion();
    
            $firestoreUrl = "https://firestore.googleapis.com/v1/projects/wic-doctor-b83e0/databases/(default)/documents/messages/".urlencode($chatId)."/chats/".urlencode($messageId);
    
            // Vérifier l'existence
            $response = Http::withToken($token['access_token'])->get($firestoreUrl);
            
            if ($response->status() === 404) {
                return response()->json(['success' => false, 'message' => 'Message non trouvé'], 404);
            }
    
            // Suppression
            $deleteResponse = Http::withToken($token['access_token'])->delete($firestoreUrl);
    
            return $deleteResponse->successful() 
                ? response()->json(['success' => true])
                : response()->json(['success' => false, 'message' => 'Erreur Firestore'], 500);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string|max:255',
            'receiver_id' => 'required|exists:users,id',
            'file' => 'nullable|file|max:2048',
        ]);

        try {
            $sender = auth()->user();
            $receiver = User::findOrFail($request->receiver_id);

            $messageId = '[#' . mt_rand(10000, 99999) . ']';

            $fileUrl = null;
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = 'chat_files/' . time() . '_' . $file->getClientOriginalName();
                Storage::disk('public')->put($fileName, file_get_contents($file));
                $fileUrl = asset('storage/' . $fileName);
            }

            $data = [
                'fields' => [
                    'id' => ['stringValue' => $messageId],
                    'text' => ['stringValue' => $request->input('message') ?? ''],
                    'fileUrl' => ['stringValue' => $fileUrl ?? ''],
                    'time' => ['integerValue' => time()],
                    'sender' => [
                        'mapValue' => [
                            'fields' => [
                                'id' => ['stringValue' => (string)$sender->id],
                                'name' => ['stringValue' => $sender->name],
                                'auth' => ['booleanValue' => true],
                                'deviceToken' => ['nullValue' => null],
                                'imageUrl' => ['stringValue' => $sender->profile_photo_url ?? '']
                            ]
                        ]
                    ],
                    'receiver' => [
                        'mapValue' => [
                            'fields' => [
                                'id' => ['stringValue' => (string)$receiver->id],
                                'name' => ['stringValue' => $receiver->name],
                                'auth' => ['booleanValue' => true],
                                'deviceToken' => ['nullValue' => null],
                                'imageUrl' => ['stringValue' => $receiver->profile_photo_url ?? '']
                            ]
                        ]
                    ]
                ]
            ];

            $chatId = $this->getChatId($sender->id, $receiver->id);

            $response = Http::post(
                "https://firestore.googleapis.com/v1/projects/wic-doctor-b83e0/databases/(default)/documents/messages/{$chatId}/chats",
                $data
            );

            if (!$response->successful()) {
                throw new \Exception('Firestore error: ' . $response->body());
            }

            if ($request->isXmlHttpRequest()) {
                return response()->json([
                    'success' => true,
                    'message' => [
                        'id' => $messageId,
                        'text' => $request->input('message'),
                        'fileUrl' => $fileUrl,
                        'time' => date('H:i', time()),
                        'sender' => [
                            'id' => (string)$sender->id,
                            'name' => $sender->name,
                            'imageUrl' => $sender->profile_photo_url ?? ''
                        ],
                        'receiver' => [
                            'id' => (string)$receiver->id,
                            'name' => $receiver->name,
                            'imageUrl' => $receiver->profile_photo_url ?? ''
                        ]
                    ]
                ]);
            } else {
                return back()->with('success', 'Message envoyé avec succès');
            }

        } catch (\Exception $e) {
            \Log::error('Message send error: ' . $e->getMessage());

            if ($request->isXmlHttpRequest()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Échec de l\'envoi du message: ' . $e->getMessage()
                ], 500);
            } else {
                return back()->with('error', 'Échec de l\'envoi du message');
            }
        }
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
   
    
    public function index(Request $request)
{
    $user = auth()->user();
    $isDoctor = $user->doctor !== null;

    // Récupération des conversations avec dernier message
    $conversations = [];

    $patients = collect(); // Initialize the $patients variable

    if ($isDoctor) {
        $relationships = DoctorPatients::where('doctor_id', $user->doctor->id)
            ->with(['patient.user'])
            ->get();
        
        // Assign patients to the variable
        $patients = $relationships->map(function ($rel) {
            return $rel->patient;
        });
    } else {
        $relationships = DoctorPatients::where('patient_id', $user->patient->id)
            ->with(['doctor.user'])
            ->get();
    }

    foreach ($relationships as $rel) {
        $target = $isDoctor ? $rel->patient : $rel->doctor;
        $otherUser = $target->user;

        // Récupération dernier message depuis Firestore
        $chatId = $user->id < $otherUser->id 
            ? $user->id . '-' . $otherUser->id 
            : $otherUser->id . '-' . $user->id;

        $messages = $this->firestore->getDocuments("messages/{$chatId}/chats");

        $lastMessage = null;
        foreach ($messages as $message) {
            $time = $message['fields']['time']['integerValue'] ?? 0;
            if (!$lastMessage || $time > $lastMessage['time']) {
                $lastMessage = [
                    'text' => $message['fields']['text']['stringValue'] ?? '',
                    'time' => $time
                ];
            }
        }

        $conversations[] = [
            'user_id' => $otherUser->id,
            'name' => $otherUser->name,
            'last_message' => $lastMessage
        ];
    }

    // Tri par dernier message
    usort($conversations, function ($a, $b) {
        return ($b['last_message']['time'] ?? 0) <=> ($a['last_message']['time'] ?? 0);
    });

    return view('chatDP', [
        'conversations' => $conversations,
        'patients' => $isDoctor ? $patients : collect(),
        'isDoctor' => $isDoctor
    ]);
}

    
}

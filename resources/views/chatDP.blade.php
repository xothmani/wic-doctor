@extends('layouts.app')

@section('content')
<div class="chat-container">

    <!-- Inclusion de Font Awesome pour les icônes -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js"></script>
    <!-- Chargement de Firebase avec compatibilité -->
<script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-auth-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-database.js"></script>


<script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-auth-compat.js"></script>
<script src="https.gstatic.com/firebasejs/9.6.10/firebase-firestore-compat.js"></script>

    <input type="hidden" id="chatId" value="{{ $chatId ?? '' }}">

    <!-- En-tête du Chat -->
    <div class="chat-header">

        <img src="{{ asset('storage/images/iconn.png') }}" alt="Icône discussion médicale" class="custom-icon" width="40" height="40">
        <h1>Docteur & Patient Discussion </h1>
    </div>

    <!-- Contenu Principal -->
    <div class="chat-main">
        <!-- Liste des Conversations -->
        <div class="conversation-list">
            <div class="conversation-header">
                @if(auth()->user()->doctor)
                    <h4>Listes des Patients</h4>
                @elseif(auth()->user()->patient)
                    <h3>Doctors</h3>
                @endif
            </div>

            @php
                // Récupérer l'utilisateur authentifié et déterminer son rôle
                $user = auth()->user();
                $isDoctor = $user->doctor !== null;
                $isPatient = $user->patient !== null;
                $formattedConversations = [];

                if ($isDoctor && $patients->count() > 0) {
                    // Pour un médecin, récupérer les patients associés
                    foreach ($patients as $doctorPatient) {
                        if ($doctorPatient->patient) {
                            // Générer le chatId en fonction des IDs
                            $chatId = $user->id . '-' . $doctorPatient->patient->user_id;
                            if ($user->id > $doctorPatient->patient->user_id) {
                                $chatId = $doctorPatient->patient->user_id . '-' . $user->id;
                            }
                            // URL Firestore pour récupérer les messages dans le chat
                            $firestore_url = 'https://firestore.googleapis.com/v1/projects/wic-doctor-b83e0/databases/(default)/documents/messages/' . $chatId . '/chats';
                            $response = Http::get($firestore_url);
                            $data = $response->json();
                            // Les documents se trouvent dans "documents"
                            $messagesData = $data['documents'] ?? [];

                            // Trouver le dernier message
                            $lastMessage = null;
                            foreach ($messagesData as $messageDoc) {
                                $fields = $messageDoc['fields'] ?? [];
                                $time = isset($fields['time']['integerValue']) ? (int)$fields['time']['integerValue'] : 0;
                                $content = $fields['text']['stringValue'] ?? '';
                                if (!$lastMessage || $time > $lastMessage['time']) {
                                    $lastMessage = [
                                        'content' => $content,
                                        'time' => date('H:i', $time)
                                    ];
                                }
                            }

                            $formattedConversations[] = [
                                'id' => $doctorPatient->patient->id,
                                'user_id' => $doctorPatient->patient->user_id,
                                'name' => $doctorPatient->patient->user->name,
                                'last_message' => $lastMessage
                            ];
                        }
                    }
                } elseif ($isPatient && $doctors->count() > 0) {
                    // Pour un patient, récupérer les médecins associés
                    foreach ($doctors as $doctor) {
                        if ($doctor->doctor) {
                            $chatId = $user->id . '-' . $doctor->doctor->user_id;
                            if ($user->id > $doctor->doctor->user_id) {
                                $chatId = $doctor->doctor->user_id . '-' . $user->id;
                            }
                            // Corriger la faute de frappe : $firestore_url (et non $firstore_url)
                            $firestore_url = 'https://firestore.googleapis.com/v1/projects/wic-doctor-b83e0/databases/(default)/documents/chats/' . $chatId . '/messages';
                            $response = Http::get($firestore_url);
                            $data = $response->json();
                            $messagesData = $data['documents'] ?? [];

                            $lastMessage = null;
                            foreach ($messagesData as $messageDoc) {
                                $fields = $messageDoc['fields'] ?? [];
                                $time = isset($fields['time']['integerValue']) ? (int)$fields['time']['integerValue'] : 0;
                                $content = $fields['text']['stringValue'] ?? '';
                                if (!$lastMessage || $time > $lastMessage['time']) {
                                    $lastMessage = [
                                        'content' => $content,
                                        'time' => $time,
                                        'time' => date('H:i', $time)
                                    ];
                                }
                            }

                            $formattedConversations[] = [
                                'id' => $doctor->doctor->id,
                                'user_id' => $doctor->doctor->user_id,
                                'name' => $doctor->doctor->user->name,
                                'last_message' => $lastMessage
                            ];
                        }
                    }
                }

                // Trier les conversations par time du dernier message (du plus récent au plus ancien)
                usort($formattedConversations, function ($a, $b) {
                    return ($b['last_message']['time'] ?? 0) <=> ($a['last_message']['time'] ?? 0);
                });
            @endphp
@if(count($formattedConversations) > 0)
    <div class="conversation-items">
        @foreach($formattedConversations as $conv)
            <a href="{{ route('chatDP.show', [
                'doctorUserId' => $isDoctor ? auth()->id() : $conv['user_id'],
                'patientUserId' => $isDoctor ? $conv['user_id'] : auth()->id()
            ]) }}" class="conversation-item">
                <div class="avatar">
                    <i class="fas fa-user-md"></i>
                </div>
                <div class="info">
                    <span class="name">{{ $conv['name'] }}</span>
                    @if($conv['last_message'])
                        <div class="last-message">
                            <p>{{ Str::limit($conv['last_message']['content'], 30) }}</p>
                            <span>{{ $conv['last_message']['time'] }}</span>
                        </div>
                    @endif
                </div>
            </a>
        @endforeach
    </div>
@else
@if(!empty($conversations)  && count($conversations) > 0)

<div class="conversation-items">
    @foreach($conversations as $conv)
        <a href="{{ route('chatDP.show', [
            'doctorUserId' => $isDoctor ? auth()->id() : $conv['user_id'],
            'patientUserId' => $isDoctor ? $conv['user_id'] : auth()->id()
        ]) }}" class="conversation-item">
            <div class="avatar">
                <i class="fas fa-user-md"></i>
            </div>
            <div class="info">
                <span class="name">{{ $conv['name'] }}</span>
                @if($conv['last_message'])
                    <div class="last-message">
                        <p>{{ Str::limit($conv['last_message']['text'], 30) }}</p>
                        <span>{{ date('H:i', $conv['last_message']['time']) }}</span>
                    </div>
                @endif
            </div>
        </a>
    @endforeach
</div>
@else
            <div class="empty-state">
                <i class="fas fa-comment-slash"></i>
                <p>Aucun message</p>
            </div>
        @endif
@endif
</div>

                                                        <!-- Zone de Chat Principale -->
        <div class="chat-area">
    <div class="chat-messages" id="chat-messages">
        @if(isset($messages) && count($messages) > 0)
            @foreach($messages as $message)
            <div class="message {{ ((string)$message['sender']['id'] === (string)auth()->id()) ? 'sent' : 'received' }}" 
     data-time="{{ $message['time'] }}" 
     data-message-id="{{ $message['firestoreId'] }}">
                    <div class="message-content">
                        <div class="message-header">
                            <span class="sender">{{ $message['sender']['name'] }}</span>
                            @if(((string)$message['sender']['id'] !== (string)auth()->id()))
                                <span class="receiver">{{ $patientUser->name }}</span>
                            @endif
                            <span class="time">{{ \Carbon\Carbon::createFromTimestamp($message['time'])->format('H:i') }}</span>
                            @if(((string)$message['sender']['id']) === ((string)auth()->id()))
                            <!-- REMPLACER TOUS LES onclick PAR data-attributes -->
                            <button class="delete-btn" 
        data-message-id="{{ $message['firestoreId'] }}"
        title="Supprimer le message">
    <i class="fas fa-trash-alt"></i>
</button>        @endif
                        </div>
                        <p class="text">{{ $message['text'] }}</p>
                        @if(!empty($message['fileUrl']))
                            <div class="message-file">
                                @php
                                    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                                    $textExtensions = ['txt'];
                                    $fileExtension = pathinfo($message['fileUrl'], PATHINFO_EXTENSION);
                                @endphp

                                @if(in_array(strtolower($fileExtension), $imageExtensions))
                                    <div class="message-image">
                                        <img src="{{ $message['fileUrl'] }}" alt="Image envoyée" class="chat-image">
                                        <div class="file-actions">
                                            <a href="{{ $message['fileUrl'] }}" download class="download-btn">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                @elseif(in_array(strtolower($fileExtension), $textExtensions))
                                    <div class="file-preview">
                                        <i class="fas fa-file-alt"></i>
                                        <a href="{{ $message['fileUrl'] }}" target="_blank" class="file-link">
                                            Voir le fichier 
                                        </a>
                                        <a href="{{ $message['fileUrl'] }}" download class="download-link">
                                            <i class="fas fa-download"></i> 
                                        </a>
                                    </div>
                                @else
                                    <div class="file-preview">
                                        <i class="fas fa-file-alt"></i>
                                        <a href="{{ $message['fileUrl'] }}" target="_blank" class="file-link">
                                            Voir le fichier
                                        </a>
                                        <a href="{{ $message['fileUrl'] }}" download class="download-link">
                                            <i class="fas fa-download"></i> 
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        @else
           
        @endif
</div>

            <!-- Formulaire d'envoi de message -->
            <div class="chat-input">
            <form id="chat-form" action="{{ route('chatDP.send') }}" method="POST" enctype="multipart/form-data">
            @csrf
        <input type="hidden" name="receiver_id" id="receiver_id" value="{{ $patientUserId ?? '' }}" required>
        
        <div class="input-container">
            <input type="text" name="message" id="message-input" placeholder="Écrire un message..." >
            <label for="file-input" class="file-icon">
                <i class="fas fa-paperclip"></i>
            </label>
            <input type="file" name="file" id="file-input" style="display: none;" accept="image/*, .pdf, .docx">
            <button type="submit"><i class="fas fa-paper-plane"></i></button>
        </div>
    </form>
</div>

                
                <div id="output"></div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-database.js"></script>

@section('scripts')
<script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-firestore-compat.js"></script>

@endsection
@endsection
@section('scripts')
<script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-database.js"></script>
@section('scripts')
<script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-firestore-compat.js"></script>
<script>
    // Configuration Firebase
    const firebaseConfig = {
        apiKey: "AIzaSyA8T2qplVSv9ZwXBW_rKOkOFjQv2hA1UKY",
        authDomain: "wic-doctor-b83e0.firebaseapp.com",
        projectId: "wic-doctor-b83e0",
        storageBucket: "wic-doctor-b83e0.appspot.com",
        messagingSenderId: "895957208558",
        appId: "1:895957208558:web:322c25347af966f5f512ff"
    };

    // Initialiser Firebase
    const app = firebase.initializeApp(firebaseConfig);
    const firestore = firebase.firestore();

    // Fonction de suppression pour Firestore
   
</script>
@endsection
<script>
  // Configuration Firebase
  const firebaseConfig = {
        apiKey: "AIzaSyA8T2qplVSv9ZwXBW_rKOkOFjQv2hA1UKY",
        authDomain: "wic-doctor-b83e0.firebaseapp.com",
        projectId: "wic-doctor-b83e0",
        storageBucket: "wic-doctor-b83e0.appspot.com",
        messagingSenderId: "895957208558",
        appId: "1:895957208558:web:322c25347af966f5f512ff"
    };

    // Initialiser Firebase
    const app = firebase.initializeApp(firebaseConfig);
    const firestore = firebase.firestore();

  

        // Écoute des suppressions en temps réel
        const chatId = document.getElementById('chatId')?.value;
        if (chatId) {
            firestore.collection(`messages/${chatId}/chats`)
                .onSnapshot((snapshot) => {
                    snapshot.docChanges().forEach((change) => {
                        if (change.type === "removed") {
                            const messageElement = document.querySelector(
                                `[data-message-id="${change.doc.id}"]`
                            );
                            if (messageElement) messageElement.remove();
                        }
                    });
                });
        }
    });
    // Configuration Firebase
    const firebaseConfig = {
        apiKey: "AIzaSyA8T2qplVSv9ZwXBW_rKOkOFjQv2hA1UKY",
        authDomain: "wic-doctor-b83e0.firebaseapp.com",
        databaseURL: "https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app",
        projectId: "wic-doctor-b83e0",
        storageBucket: "wic-doctor-b83e0.appspot.com",
        messagingSenderId: "895957208558",
        appId: "1:895957208558:web:322c25347af966f5f512ff"
    };

    // Initialiser Firebase
    const app = firebase.initializeApp(firebaseConfig);
    const database = firebase.database();

    // Gestion de l'envoi de message
    document.getElementById('chat-formE').addEventListener('submit', async function(e) {
    e.preventDefault();
    console.log('Form submitted'); // Ajout de cette ligne

    const formData = new FormData(this);

    try {
        const response = await fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });

        console.log('Response received', response); // Ajout de cette ligne

        if (response.ok) {
            const data = await response.json();
            console.log('Data received', data); // Ajout de cette ligne
            if (data.success) {
                // ... votre code de traitement des messages ...
            } else {
                alert('Erreur: ' + (data.error || 'Échec de l\'envoi du message'));
            }
        } else {
            alert('Une erreur s\'est produite lors de l\'envoi du message.');
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Une erreur s\'est produite lors de l\'envoi du message.');
    }
});



    // Fonctions utilitaires
    function addMessageToUI(message, type) {
        const messagesContainer = document.getElementById('chat-messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${type}`;
        messageDiv.innerHTML = `
            <div class="message-content">
                <div class="message-header">
                    <span class="sender">${message.sender_name}</span>
                    <span class="time">${message.time}</span>
                </div>
                <p class="text">${message.content}</p>
                ${message.file_url ? `
                    <div class="message-file">
                        <a href="${message.file_url}" target="_blank">Fichier joint</a>
                    </div>
                ` : ''}
            </div>
        `;
        messagesContainer.appendChild(messageDiv);
    }

    function scrollToBottom() {
        const chatMessages = document.getElementById('chat-messages');
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

   // Écoute des nouveaux messages Firebase
    function initFirebaseListener() {
        const userId = {{ auth()->user()->id }};
        const doctorUserId = "{{ $doctorUserId ?? '' }}";
        
        if (!doctorUserId) return;

        const chatId = userId < doctorUserId ? `${userId}-${doctorUserId}` : `${doctorUserId}-${userId}`;
        const chatRef = database.ref(`chats/${chatId}/messages`);

        chatRef.on('child_added', (snapshot) => {
            const message = snapshot.val();
            if (message.receiver_id == userId) {
                addMessageToUI({
                    ...message,
                    time: new Date(message.timestamp * 1000).toLocaleTimeString()
                }, 'received');
                scrollToBottom();
            }
        });
    }


   
    // Initialisation

</script>

<script>
// Configuration IMPÉRATIVE
const firebaseConfig = {
    apiKey: "AIzaSyA8T2qplVSv9ZwXBW_rKOkOFjQv2hA1UKY",
    authDomain: "wic-doctor-b83e0.firebaseapp.com",
    projectId: "wic-doctor-b83e0",
    storageBucket: "wic-doctor-b83e0.appspot.com",
    messagingSenderId: "895957208558",
    appId: "1:895957208558:web:322c25347af966f5f512ff"
};

// Initialisation EXPLICITE
const firebaseApp = firebase.initializeApp(firebaseConfig);
const auth = firebase.auth(firebaseApp); // <-- Spécifier l'instance
const firestore = firebase.firestore(firebaseApp); // <-- Spécifier l'instance

// 3. Déclarer la fonction AVANT son utilisation

// 4. Gestionnaire d'événements MODERN

// 5. Handler séparé pour meilleur contrôle

// 6. Initialisation GARANTIE
document.addEventListener('DOMContentLoaded', () => {
    setupDeleteHandlers();
});
</script>

<script>
// Configuration Firebase
// Configuration Firebase pour Firestore
const firebaseConfig = {
    apiKey: "AIzaSyA8T2qplVSv9ZwXBW_rKOkOFjQv2hA1UKY",
    authDomain: "wic-doctor-b83e0.firebaseapp.com",
    projectId: "wic-doctor-b83e0",
    storageBucket: "wic-doctor-b83e0.appspot.com",
    messagingSenderId: "895957208558",
    appId: "1:895957208558:web:322c25347af966f5f512ff"
};

// Initialiser Firebase avec Firestore
const app = firebase.initializeApp(firebaseConfig);
const firestore = firebase.firestore();

// Fonction de suppression pour Firestore

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    const chatId = document.getElementById('chatId')?.value;
    if (chatId) {
        listenForDeletedMessages(chatId);
    }
});

// Initialisation après chargement
document.addEventListener('DOMContentLoaded', () => {
    const chatId = document.getElementById('chatId').value;
    if (chatId) setupRealtimeDeletionListener(chatId);
});
</script>
@section('scripts')
<script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-firestore-compat.js"></script>
<script>
    // Configuration Firebase
    const firebaseConfig = {
        apiKey: "AIzaSyA8T2qplVSv9ZwXBW_rKOkOFjQv2hA1UKY",
        authDomain: "wic-doctor-b83e0.firebaseapp.com",
        projectId: "wic-doctor-b83e0",
        storageBucket: "wic-doctor-b83e0.appspot.com",
        messagingSenderId: "895957208558",
        appId: "1:895957208558:web:322c25347af966f5f512ff"
    };

    // Initialiser Firebase une seule fois
    const app = firebase.initializeApp(firebaseConfig);
    const firestore = firebase.firestore();

    // Fonction de suppression
    async function deleteMessage(chatId, messageId, buttonElement) {
        if (!confirm('Voulez-vous vraiment supprimer ce message ?')) {
            return;
        }

        try {
            console.log(`Tentative de suppression: messages/${chatId}/chats/${messageId}`);
            
            // Référence au document
            const docRef = firestore.doc(`messages/${chatId}/chats/${messageId}`);
            
            // Suppression
            await docRef.delete();
            
            // Suppression visuelle
            const messageElement = buttonElement.closest('.message');
            if (messageElement) {
                messageElement.remove();
                console.log('Message supprimé avec succès');
            }
        } catch (error) {
            console.error('Erreur de suppression:', error);
            alert('Erreur lors de la suppression: ' + error.message);
        }
    }

    // Gestionnaire d'événements
    document.addEventListener('DOMContentLoaded', () => {
        // Écouteur délégué pour tous les boutons de suppression
        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete-btn')) {
                const button = e.target.closest('.delete-btn');
                const chatId = document.getElementById('chatId').value;
                const messageId = button.getAttribute('data-message-id');
                
                console.log('Clic sur suppression:', {chatId, messageId});
                
                if (chatId && messageId) {
                    deleteMessage(chatId, messageId, button);
                }
            }
        });

        // Écoute des suppressions en temps réel
        const chatId = document.getElementById('chatId')?.value;
        if (chatId) {
            firestore.collection(`messages/${chatId}/chats`)
                .onSnapshot((snapshot) => {
                    snapshot.docChanges().forEach((change) => {
                        if (change.type === "removed") {
                            const deletedId = change.doc.id;
                            const messageElement = document.querySelector(`[data-message-id="${deletedId}"]`);
                            if (messageElement) {
                                messageElement.remove();
                            }
                        }
                    });
                });
        }
    });
</script>
@endsection
@endsection
@section('styles')
<style>
    .chat-container {
        display: flex;
        flex-direction: column;
        height: 100vh;
        background-color: #f0f2f5;
    }
    .chat-header h1 {
    font-size: 1.4rem;
    color: #2c3e50; /* Couleur professionnelle */
    margin: 0; /* Supprime la marge par défaut */
    font-weight: 600;
}
    .chat-header {
        position: sticky;
    top: 0;
    z-index: 1000;
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    height: 60px;
    display: flex; /* Ajouté */
    align-items: center; /* Ajouté */
    justify-content: center; /* Ajouté */
    width: 100%; /* Assure la pleine largeur */
    }

    .last-message {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }

    .last-message-text {
        margin: 0;
    }

    .last-message-time {
        font-size: 10px;
        color: #999;
    }

    .no-message {
        font-size: 12px;
        color: #999;
        font-style: italic;
    }

    .chat-main {
        display: flex;
        flex: 1;
        overflow: hidden;
    }.header-content {
    display: flex;
    align-items: center;
    gap: 15px;
    width: 100%;
}

.delete-btn {
    background: transparent;
    border: none;
    color: rgba(255, 255, 255, 0.7); /* Couleur discrète pour les messages envoyés */
    cursor: pointer;
    margin-left: 10px;
    font-size: 14px;
    transition: color 0.3s ease;
}.message.received .delete-btn {
    color: rgba(0, 0, 0, 0.5); /* Couleur discrète pour les messages reçus */
}

/* Effet au survol */
.delete-btn:hover {
    color: #ff4444; /* Rouge plus vif au survol pour indiquer l'action de suppression */
}
    
    .conversation-list {
        width: 300px;
        background-color: white;
        border-right: 1px solid #ddd;
        overflow-y: auto;
    }
    .conversation-header {
                                                    padding: 15px;
                                                    background-color: #f8f9fa;
                                                    border-bottom: 1px solid #ddd;
                                                }

                                                .conversation-item {
                                                    display: flex;
                                                    align-items: center;
                                                    padding: 10px;
                                                    cursor: pointer;
                                                    transition: background-color 0.3s;
                                                }

                                                .conversation-item:hover {
                                                    background-color: #f0f2f5;
                                                }

                                                .conversation-item.active {
                                                    background-color: #e9ecef;
                                                }
                                                .friendly-title {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 32px;
    font-weight: 600;
    color:rgb(53, 53, 56); /* A soft green for a friendly touch */
    text-align: center;
    text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.1);
    letter-spacing: 1px;
    margin-bottom: 20px;
    line-height: 1.5;
}

   

    .conversation-item:hover {
        background-color: #f0f2f5;
    }

    .avatar {
        width: 40px;
        height: 40px;
        background-color: rgb(51, 99, 151);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-right: 10px;
    }.chat-image {
    max-width: 300px;
    max-height: 200px;
    border-radius: 8px;
    margin-top: 8px;
    cursor: pointer;
    transition: transform 0.3s ease;
}

.chat-image:hover {
    transform: scale(1.03);
}

.file-actions {
    display: flex;
    gap: 10px;
    margin-top: 8px;
}

.file-actions a {
    color: #666;
    transition: color 0.3s ease;
}

.file-actions a:hover {
    color: #007bff;
}

    .info {
        flex: 1;
    }

    .info .name {
        font-weight: bold;
    }

    .info .status {
        font-size: 12px;
        color: green;
    }
    .message-image img {
    max-width: 100%;
    max-height: 200px;
    border-radius: 8px;
    margin-top: 8px;
    cursor: pointer;
    transition: transform 0.3s ease;
}

.message-image img:hover {
    transform: scale(1.03);
}

.file-actions {
    display: flex;
    gap: 10px;
    margin-top: 8px;
}

.file-actions a {
    color: #666;
    transition: color 0.3s ease;
}

.file-actions a:hover {
    color: #007bff;
}
    .chat-area {
        flex: 1;
        display: flex;
        flex-direction: column;
        background-color: white;
    }

    .chat-messages {
        flex: 1;
        padding: 15px;
        overflow-y: auto;
        background-color: #f0f2f5;
    }

  
/* Styles pour le conteneur de l'input */

/* Style de l'input texte */
#message-input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 14px;
    padding: 10px 0;
    margin-left: 10px;
}

/* Style de l'icône de fichier */
.file-icon {
    cursor: pointer;
    font-size: 20px;
    color: #007bff;
    padding: 12px;
    background: #f0f4ff;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.file-icon:hover {
    background: #007bff;
    color: white;
    transform: rotate(15deg);
}

/* Bouton d'envoi */
.chat-input button {
        background-color: #007bff;
        border: none;
        color: white;
        padding: 10px;
        border-radius: 5px;
        cursor: pointer;
    }

.chat-input button:hover {
    background-color: #0056b3;
}

/* Prévisualisation du fichier */
.file-preview {
    display: flex;
    align-items: center;
    gap: 15px;
    margin: 15px 0;
    padding: 15px;
    background: #f8f9ff;
    border: 2px dashed #e0e7ff;
    border-radius: 15px;
}

/* Cacher l'input file par défaut */
input[type="file"] {
    display: none;
}
.input-container {
                                                position: relative;
                                                flex: 1;
                                                display: flex;
                                                align-items: center;
                                                background-color: white;
                                                border: 1px solid #ddd;
                                                border-radius: 25px; /* Arrondir les coins */
                                                padding: 5px 10px; /* Espace interne */
                                            }

                                            

/* Conteneur flex pour aligner les éléments */

.input-container {
                                                position: relative;
                                                flex: 1;
                                                margin-right: 10px;
                                            }
                                            .chat-input input[type="file"] {
                                                    margin-right: 10px;
                                                }

.input-container {
                                                position: relative;
                                                flex: 1;
                                                margin-right: 10px;
                                            }

                                            
                                            .input-container {
                                                position: relative;
                                                flex: 1;
                                                display: flex;
                                                align-items: center;
                                                background-color: white;
                                                border: 1px solid #ddd;
                                                border-radius: 25px; /* Arrondir les coins */
                                                padding: 5px 10px; /* Espace interne */
                                            }



/* Animation au survol des boutons */
.download-link {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.download-link:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}


/* Styles pour les messages envoyés (à droite, bleu) */
.message.sent {
    justify-content: flex-end;
}

.message.sent .message-content {
    background-color: #007bff;
    color: white;
    border-radius: 15px 15px 0 15px;
}                                                                                                                                                                                                                                                                                                                           

/* Styles pour les messages reçus (à gauche, gris) */
.message.received {
    justify-content: flex-start;
}

.message.received .message-content {
    background-color: #f1f1f1;
    color: black;
    border-radius: 15px 15px 15px 0;
}

/* Styles communs pour les messages */
.message {
    display: flex;
    margin-bottom: 10px;
}

.message-content {
    max-width: 70%;
    padding: 10px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.message-header {
    font-size: 12px;
    color: #666;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
}

.message.sent .message-header {
    color: rgba(255, 255, 255, 0.7);
}

.message p.text {
    font-size: 14px;
    margin: 0;
}

.chat-input {
    position: sticky;
    bottom: 30px;
    
    background: white;
    padding: 15px;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
    z-index: 1000;
}

.chat-input input[type="text"] {
                                                    flex: 1;
                                                    width: 450px;
                                                    padding: 10px;
                                                    border: 1px solid #ddd;
                                                    border-radius: 5px;
                                                    margin-right: 10px;
                                                }

</style>
@endsection

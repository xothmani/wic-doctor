@extends('layouts.app')

@section('content')
<div class="chat-container">

    <!-- Inclusion de Font Awesome pour les icônes -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>


    <input type="hidden" id="chatId" value="{{ $chatId ?? '' }}">

    <!-- En-tête du Chat -->
    <div class="chat-header">

        <img src="{{ asset('images/icons/iconn.png') }}" alt="Icône discussion médicale" class="custom-icon" width="40" height="40">
        <h1>Docteur & Patient Discussion </h1>
    </div>

    <!-- Contenu Principal -->
    <div class="chat-main">
        <!-- Liste des Conversations -->
        <div class="conversation-list">
            <div class="conversation-header">
                @if(auth()->user()->doctor)
                    <h5>Listes des Patients</h5>
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
                        if ($doctorPatient->patient != null) { // Vérifie si patient n'est pas null
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
'name' => $doctorPatient->patient ? $doctorPatient->patient->user ? $doctorPatient->patient->user->name : 'N/A' : 'N/A',
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
                <p>Pas encore de messages !</p>
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
                     data-message-id="{{ $message['id'] }}">
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
        onclick="deleteMessage('{{ $chatId }}', '{{ $message['id'] }}', this)"
        data-message-id="{{ $message['id'] }}">
    <i class="fas fa-trash-alt"></i>
</button>

                            @endif
                        </div>
                        <p class="text">{{ $message['text'] }}</p>
                        @if(!empty($message['fileUrl']))
<div class="message-file">
    @php
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $fileExtension = pathinfo($message['fileUrl'], PATHINFO_EXTENSION);
    @endphp

    @if(in_array(strtolower($fileExtension), $imageExtensions))
        <div class="message-image">
            <a href="{{ $message['fileUrl'] }}" data-lightbox="image-{{ $message['id'] }}" data-title="Fichier joint">
                <img src="{{ $message['fileUrl'] }}" alt="Fichier image">
            </a>
        </div>
    @else
        <div class="file-details">
            <i class="fas fa-file-alt"></i>
            <a href="{{ $message['fileUrl'] }}" target="_blank">Voir le fichier</a>
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
    <form action="{{ route('chatDP.send') }}" method="POST" enctype="multipart/form-data" id="chat-form">
        @csrf
        <input type="hidden" name="receiver_id" value="{{ $patientUserId ?? '' }}">
        
        <div class="input-container">
            <!-- Icône pour attacher un fichier -->
     
            <input type="file" name="file" id="file-input" >
            <!-- Zone d'affichage des fichiers attachés -->
            <div id="output"></div>
            
            <!-- Champ de message -->
            <input type="text" name="message" id="message-input" placeholder="Écrire un message...">
            <div id="output"></div>

            <!-- Bouton d'envoi -->
            <button type="submit" class="send-button">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </form>
</div>

                
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-firestore-compat.js"></script>

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

    // Initialisation Firebase
    const app = firebase.initializeApp(firebaseConfig);
    const db = firebase.firestore();

    async function deleteMessage(chatId, messageId, buttonElement) {
        if (!confirm('Voulez-vous vraiment supprimer ce message définitivement ?')) return;

        try {
            // Référence exacte selon votre structure Firestore
            const messageRef = db.collection("messages") // Collection principale
                               .doc(chatId)             // Document du chat
                               .collection("chats")     // Sous-collection des messages
                               .doc(messageId);         // Document du message

            // Suppression avec vérification
            await messageRef.delete();
            
            // Suppression visuelle
            buttonElement.closest('.message').remove();
            
            Toastify({
                text: "Message supprimé avec succès",
                duration: 3000,
                backgroundColor: "#4CAF50",
            }).showToast();
        } catch (error) {
            console.error("Erreur complète:", {
                code: error.code,
                message: error.message,
                stack: error.stack
            });
            Toastify({
                text: "Échec de la suppression: " + error.message,
                duration: 5000,
                backgroundColor: "#F44336",
            }).showToast();
        }
    }

    // Écouteur pour les suppressions en temps réel
    function setupRealTimeListener(chatId) {
        db.collection("messages")
          .doc(chatId)
          .collection("chats")
          .onSnapshot((snapshot) => {
              snapshot.docChanges().forEach((change) => {
                  if (change.type === "removed") {
                      const messageElement = document.querySelector(`[data-message-id="${change.doc.id}"]`);
                      if (messageElement) messageElement.remove();
                  }
              });
          });
    }

    // Initialisation au chargement
    document.addEventListener('DOMContentLoaded', () => {
        const chatId = document.getElementById('chatId')?.value;
        if (chatId) setupRealTimeListener(chatId);
    });
</script>
@endsection
@section('scripts')

<script>
 

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

function deleteMessage(chatId, messageId, btnElement) {
    // Supprimer dans Firestore
    const messageRef = firebase.database().ref(`chatTE/${chatId}/messages/${messageId}`);
    messageRef.remove()
        .then(() => {
            // Supprimer l'élément DOM côté frontend
            const messageElement = btnElement.closest('.message');
            if (messageElement) {
                messageElement.remove();
            }

            // Tu peux aussi appeler un endpoint Laravel ici si tu veux supprimer côté DB aussi
            console.log('Message supprimé de Firestore.');
        })
        .catch((error) => {
            console.error('Erreur suppression Firestore:', error);
        });
}

document.getElementById('file-input').addEventListener('change', function(event) {
    const file = event.target.files[0];
    const outputDiv = document.getElementById('output');
    outputDiv.innerHTML = '';

    if (file) {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                outputDiv.innerHTML = `
                    <div class="file-preview">
                        <img src="${e.target.result}" alt="Aperçu" class="preview-image">
                        <span>${file.name}</span>
                    </div>
                `;
            };
            reader.readAsDataURL(file);
        } else {
            outputDiv.innerHTML = `
                <div class="file-preview">
                    <i class="fas fa-file"></i>
                    <span>${file.name}</span>
                </div>
            `;
        }
    }
});

    function clearFileInput() {
        document.getElementById('file-input').value = '';
        document.getElementById('file-preview-container').innerHTML = '';
    }

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
@endsection
@section('styles')
<style>
                                             
                                             .file-icon {
                                                cursor: pointer;
                                                font-size: 20px;
                                                color: #007bff;
                                                margin-right: 10px; /* Espace entre l'icône et l'input */
                                            }
                                            .message-image img {
        max-width: 100px;
        height: auto;
        border-radius: 5px;
        margin-top: 5px;
    }

    /* Prévisualisation du fichier */
  
  
  
    .file-preview {
        color: #ff4444;
        cursor: pointer;
        margin-left: 10px;
    }            .file-icon:hover {
                                                color: #0056b3;
                                            }

                                            /* Chat Header */
                                          
                                            /* Suggestions de patients */

                                            /* Suggestions de patients */
                                            .patient-suggestions {
                                                position: absolute;
                                                bottom: 60px;
                                                left: 0;
                                                width: 100%;
                                                max-height: 150px;
                                                overflow-y: auto;
                                                background-color: white;
                                                border: 1px solid #ddd;
                                                border-radius: 5px;
                                                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                                                z-index: 1000;
                                                display: none;
                                            }
/* Fichiers et images - Style amélioré */
.message-file {
  padding: 8px;
  background: #f5f5f5;
  border-radius: 4px;
  margin-top: 5px;
}

.message-file:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

/* Prévisualisation fichier */
.file-preview {
    display: flex;
    align-items: center;
    gap: 15px;
    margin: 15px 0;
    padding: 15px;
    background: #f8f9ff;
    border: 2px dashed #e0e7ff;
    border-radius: 15px;
    position: relative;
    overflow: hidden;
}

.file-preview::before {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 50%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    animation: shine 1.5s infinite;
}

@keyframes shine {
    100% {
        left: 200%;
    }
}

.file-preview img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 10px;
    border: 2px solid #fff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.file-info {
    flex: 1;
    position: relative;
}

.file-info div {
    font-size: 14px;
    font-weight: 600;
    color: #2d3748;
    word-break: break-all;
}

.file-info small {
    display: block;
    font-size: 12px;
    color: #718096;
    margin-top: 4px;
}

.remove-file {
    color: #ff4444;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.remove-file:hover {
    transform: scale(1.2);
}.file-link:hover {
  text-decoration: underline;
}

/* Animation de téléchargement */
.download-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 15px;
    background: #007bff;
    color: white !important;
    border-radius: 25px;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.download-link::before {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(120deg, transparent, rgba(255,255,255,0.4), transparent);
    transition: 0.5s;
}

.download-link:hover::before {
    left: 100%;
}

.download-link:hover {
    background: #0056b3;
    box-shadow: 0 4px 12px rgba(0,123,255,0.3);
}

/* Input file stylisé */
.file-icon {
    cursor: pointer;
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

/* Message images */
.message-image {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    transition: transform 0.3s ease;
}

.message-image img {
    width: 100%;
    max-width: 300px;
    height: auto;
    border-radius: 12px;
    cursor: zoom-in;
    transition: transform 0.3s ease;
}

.message-image:hover img {
    transform: scale(1.03);
}

/* Fichiers non-images */
.message-file .file-icon {
    font-size: 24px;
    color: #007bff;
    min-width: 40px;
    text-align: center;
}

.file-details {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 12px;
    background: #f8f9ff;
    border-radius: 10px;
}

/* Progress bar animation */
@keyframes upload-progress {
    0% { width: 0%; }
    100% { width: 100%; }
}

.upload-progress {
    height: 3px;
    background: #007bff;
    animation: upload-progress 2s ease-out;
    position: absolute;
    bottom: 0;
    left: 0;
}

/* Responsive design */
@media (max-width: 768px) {
    .file-preview {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .message-image img {
        max-width: 200px;
    }
    
    .download-link {
        width: 100%;    
        justify-content: center;
    }
}
                                            .patient-suggestions .patient-item {
                                                padding: 10px;
                                                cursor: pointer;
                                                transition: background-color 0.3s;
                                            }

/* Animation for the badge */
@keyframes bounce {
    0% {
        transform: translateY(0);
    }
    100% {
        transform: translateY(-20px); /* Badge will slightly bounce up and down */
    }
}

/* Show badge when there is a notification */
.notification-icon .badge.show {
    display: inline-block;  /* Show the badge when needed */
}
                                                .patient-suggestions .patient-item:hover {
                                                background-color: #f0f2f5;
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

.header-content {
    display: flex;
    align-items: center;
    gap: 15px; /* Espacement entre l'icône et le titre */
    max-width: 1200px; /* Limite la largeur du contenu */
    width: 100%; /* Prend toute la largeur disponible */
    padding: 0 20px; /* Marge interne latérale */
}

.custom-icon {
    filter: drop-shadow(0 2px 2px rgba(0,0,0,0.1)); /* Effet visuel subtil */
    object-fit: contain; /* Garantit une image bien proportionnée */
}

.chat-header h1 {
    font-size: 1.4rem;
    color: #2c3e50; /* Couleur professionnelle */
    margin: 0; /* Supprime la marge par défaut */
    font-weight: 600;
}
                                        
                                            .active-doctor-avatar {
                                                width: 40px;
                                                height: 40px;
                                                background-color:rgb(51, 99, 151); /* Bleu professionnel pour l'avatar */
                                                border-radius: 50%;
                                                display: flex;
                                                align-items: center;
                                                justify-content: center;
                                                color: white;
                                            }

                                       
                                            .active-doctor-info {
                                                flex: 1;
                                                display: flex;
                                                flex-direction: column;
                                                gap: 3px;
                                            }

                                            .active-doctor-info .name {
                                                font-weight: bold;
                                                font-size: 18px;
                                                color: #333; /* Texte foncé pour le titre */
                                            }
                                            .doctor-avatar {
    width: 50px; /* Ajuster selon besoin */
    height: 50px; /* Doit être identique à width pour un cercle parfait */
    border-radius: 50%;
    overflow: hidden; /* Coupe l'image qui dépasse */
    display: flex;
    align-items: center;
    justify-content: center;
}

.doctor-avatar img {
    width: 100%; /* Remplit tout l’espace du parent */
    height: 100%;
    object-fit: cover; /* S'assure que l'image couvre bien tout le cercle */
}

                                            .active-doctor-info .status {
                                                font-size: 12px;
                                                color: #28a745; /* Vert pour indiquer "Connecté" */
                                            }

                                            .header-actions {
                                                display: flex;
                                                align-items: center;
                                                gap: 10px;
                                            }

                                            .header-actions button {
                                                background: none;
                                                border: none;
                                                color: #007bff; /* Bleu pour les icônes */
                                                font-size: 18px;
                                                cursor: pointer;
                                                transition: color 0.3s ease;
                                            }

                                            .header-actions button:hover {
                                                color: #0056b3; /* Bleu plus foncé au survol */
                                            }
                                      

                                                /* Chat Main */
                                              
                                                /* Conversation List */
                                              

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
    background-color: white; /* Couleur unifiée */
    transition: none; /* Supprime la transition */
}

/* SUPPRIMER CE BLOC ENTIEREMENT */



                                  
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

.conversation-item {
    transition: background-color 0.2s ease;
}

.conversation-item.active {
    background-color: #f0f2f5;
    border-left: 4px solid #007bff;
    position: relative;
}

.conversation-item.active::after {
    content: "";
    position: absolute;
    right: -1px;
    top: 50%;
    transform: translateY(-50%);
    height: 60%;
    width: 2px;
    background-color: #007bff;
}

.conversation-item:hover {
    background-color: #f8f9fa;
    transform: translateX(3px);
}
                                              
                                                .doctor-info {
                                                    flex: 1;
                                                }

                                                .doctor-info .name {
                                                    font-weight: bold;
                                                }

                                                .doctor-info .specialty {
                                                    font-size: 12px;
                                                    color: #666;
                                                }

                                                .doctor-info .status {
                                                    font-size: 12px;
                                                    color: green;
                                                }

                                                /* Chat Area */
                                            
                            

/* Masquer l'input file */
.file-upload {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    padding: 8px 12px;
    background-color:rgb(10, 9, 9);
    border-radius: 8px;
    border: 1px solid #ccc;
    transition: all 0.3s ease;
}                                      .message {
                                                    display: flex;
                                                    margin-bottom: 10px;
                                                }

                                                .message.sent {
                                                    justify-content: flex-end;
                                                }

                                                .message.received {
                                                    justify-content: flex-start;
                                                }
                                            /* Bouton de suppression */
                                            .delete-btn {
                                                background: transparent;
                                                border: none;
                                                color: rgba(255, 255, 255, 0.7); /* Couleur discrète pour les messages envoyés */
                                                cursor: pointer;
                                                margin-left: 10px;
                                                font-size: 14px;
                                                transition: color 0.3s ease;
                                            }

                                            /* Style pour les messages reçus */
                                            .message.received .delete-btn {
                                                color: rgba(0, 0, 0, 0.5); /* Couleur discrète pour les messages reçus */
                                            }

                                            /* Effet au survol */
                                            .delete-btn:hover {
                                                color: #ff4444; /* Rouge plus vif au survol pour indiquer l'action de suppression */
                                            }
                                                .message-content {
                                                    max-width: 70%;
                                                    padding: 10px;
                                                    border-radius: 10px;
                                                    background-color: white;
                                                    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
                                                    position: relative;
                                                }

                                                .message.sent .message-content {
                                                    background-color: #007bff;
                                                    color: white;
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

                                                /* Bouton de suppression */




                                            .input-container {
                                                position: relative;
                                                flex: 1;
                                                margin-right: 10px;
                                            }
                                            .chat-input input[type="file"] {
                                                    margin-right: 10px;
                                                }
                                                .file-name-display {
        margin: 0 10px;
        color: #666;
        font-size: 14px;
        max-width: 100px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .download-link:hover {
        text-decoration: underline;
    }
 .chat-input button {
                                                    background-color: #007bff;
                                                    border: none;
                                                    color: white;
                                                    padding: 10px;
                                                    border-radius: 5px;
                                                    cursor: pointer;
                                                } 

.chat-input input[type="file"] {
        margin-right: 10px;
    }html::-webkit-scrollbar {
    display: none;
}

.chat-input {
position: sticky;
bottom: 0;
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
    
                                            .send-button {
                                                position: absolute;
                                                right: 10px;
                                                top: 50%;
                                                transform: translateY(-50%);
                                                background-color: #007bff;
                                                border: none;
                                                color: white;
                                                padding: 8px 12px;
                                                border-radius: 50%;
                                                cursor: pointer;
                                                font-size: 16px;
                                                display: flex;
                                                align-items: center;
                                                justify-content: center;
                                            }

                                            .send-button:hover {
                                                background-color: #0056b3;
                                            }

                                        

                                            .message-form {
                                                display: flex;
                                                align-items: center;
                                                width: 100%;
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

                                            
                                           

                                            #message-input {
                                                flex: 1;
                                                border: none;
                                                outline: none;
                                                font-size: 14px;
                                                padding: 10px 0; /* Ajustez le padding pour correspondre au design */
                                            }

                                            .send-button {
                                                background-color: #007bff;
                                                border: none;
                                                color: white;
                                                padding: 8px 12px;
                                                border-radius: 50%;
                                                cursor: pointer;
                                                font-size: 16px;
                                                display: flex;
                                                align-items: center;
                                                justify-content: center;
                                                margin-left: 10px; /* Espace entre l'input et le bouton */
                                            }

                                            .send-button:hover {
                                                background-color: #0056b3;
                                            }
                                            #patient-suggestions {
                                                border: 2px solid red; /* Pour visualiser le conteneur */
                                                background-color: white;
                                                z-index: 1000;
                                            }
                                               

/* Fixed header */


/* Main chat area that will scroll */


/* Messages container */


/* Fixed input area */


.chat-container {
    display: flex;
    flex-direction: column;
    height: 100vh;
    background-color: #f0f2f5;
}



.chat-main {
    flex: 1;
    display: flex;
    overflow: hidden;
}

.conversation-list {
    width: 300px;
    background-color: white;
    border-right: 1px solid #ddd;
    overflow-y: auto;
    height: calc(100vh - 60px); /* Hauteur totale - header */
}

.chat-area {
    flex: 1;
    display: flex;
    flex-direction: column;
}
.chat-messages {
        flex: 1;
        padding: 15px;
        padding-bottom: 60px; /* Réduit de 80px à 60px */

        overflow-y: auto;
        background-color: #f0f2f5;
    }


/* Supprimer les marges existantes */
.chat-main {
    margin-top: 0;
    margin-bottom: 0;
}

/* Ajuster le padding des messages pour l'espace vertical */

/* Assurer que les éléments sticky restent collés */
.conversation-list {
    position: sticky;
    top: 60px;
    height: calc(100vh - 60px);
}                                    

</style>
@endsection

@extends('layouts.app')

@section('content')
<div class="chat-container">
    <!-- Inclusion de Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- En-tête d  u Chat -->    
    <div class="chat-header">
        <div class="header-content">

                <img src="https://www.google.com/url?sa=i&url=https%3A%2F%2Fwww.freepik.com%2Fpremium-vector%2Fonline-consultation-with-doctor-virtual-medicine_16170762.htm&psig=AOvVaw2p37XET1e6HZ_JgbprNT1V&ust=1741787517802000&source=images&cd=vfe&opi=89978449&ved=0CBQQjRxqFwoTCLCmn6mWgowDFQAAAAAdAAAAABAT" 
            alt="Icône discussion médicale" 
            width="40" 
            height="40">
                 <h1>Docteur-Patient Chat</h1>
        </div>
        
    </div>

    <!-- Contenu Principal -->
    <div class="chat-main">
        <!-- Liste des Conversations -->
        <div class="conversation-list">
            <div class="conversation-header">
                @if(auth()->user()->doctor)
                    <h3>Patients</h3>
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
                $firestore_url = 'https://firestore.googleapis.com/v1/projects/wic-doctor-b83e0/databases/(default)/documents/chats/' . $chatId . '/messages';
                $response = Http::get($firestore_url);
                $data = $response->json();
                // Les documents se trouvent dans "documents"
                $messagesData = $data['documents'] ?? [];

                // Trouver le dernier message
                $lastMessage = null;
                foreach ($messagesData as $messageDoc) {
                    $fields = $messageDoc['fields'] ?? [];
                    $timestamp = isset($fields['timestamp']['integerValue']) ? (int)$fields['timestamp']['integerValue'] : 0;
                    $content = $fields['text']['stringValue'] ?? '';
                    if (!$lastMessage || $timestamp > $lastMessage['timestamp']) {
                        $lastMessage = [
                            'content' => $content,
                            'timestamp' => $timestamp,
                            'time' => date('H:i', $timestamp)
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
                    $timestamp = isset($fields['timestamp']['integerValue']) ? (int)$fields['timestamp']['integerValue'] : 0;
                    $content = $fields['text']['stringValue'] ?? '';
                    if (!$lastMessage || $timestamp > $lastMessage['timestamp']) {
                        $lastMessage = [
                            'content' => $content,
                            'timestamp' => $timestamp,
                            'time' => date('H:i', $timestamp)
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

    // Trier les conversations par timestamp du dernier message (du plus récent au plus ancien)
    usort($formattedConversations, function ($a, $b) {
        return ($b['last_message']['timestamp'] ?? 0) <=> ($a['last_message']['timestamp'] ?? 0);
    });
@endphp

            @if(count($formattedConversations) > 0)
                <div class="conversation-list">
                    @foreach($formattedConversations as $conversation)
                        <a href="{{ route('chatDP.show', [
                            'doctorUserId' => $isDoctor ? auth()->id() : $conversation['user_id'],
                            'patientUserId' => $isDoctor ? $conversation['user_id'] : auth()->id()
                        ]) }}" class="conversation-item" data-id="{{ $conversation['id'] }}" data-user-id="{{ $conversation['user_id'] }}">
                            <div class="avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="info">
                                <span class="name">{{ $conversation['name'] }}</span>
                                <span class="status online"></span>
                                @if($conversation['last_message'])
                                    <div class="last-message">
                                        <p class="last-message-text">{{ $conversation['last_message']['content'] }}</p>
                                        <span class="last-message-time">{{ $conversation['last_message']['time'] }}</span>
                                    </div>
                                @else
                                    <div class="no-message">No messages yet</div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-comment-slash"></i>
                    <p>No patients available</p>
                </div>
            @endif
        </div>

        <!-- Zone de Chat Principale -->
        <div class="chat-area">
        <div class="chat-messages" id="chat-messages">
    @if(isset($messages) && count($messages) > 0)
        @foreach($messages as $message)
            <div class="message {{ ((string)$message['sender_id'] === (string)auth()->id()) ? 'sent' : 'received' }}" 
                 data-timestamp="{{ $message['timestamp'] }}" 
                 data-message-id="{{ $message['id'] }}">
                <div class="message-content">
                    <div class="message-header">
                        <span class="sender">{{ $message['sender_name'] }}</span>
                        <span class="time">{{ \Carbon\Carbon::createFromTimestamp($message['timestamp'])->format('H:i') }}</span>
                        @if(((string)$message['sender_id']) === ((string)auth()->id()))
                            <button class="delete-btn" onclick="deleteMessage('{{ $message['id'] }}', this)" title="Supprimer le message">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        @endif
                    </div>
                    <p class="text">{{ $message['content'] }}</p>
                    @if(!empty($message['file_url']))
                        <a href="{{ $message['file_url'] }}" target="_blank" class="file-link">Voir le fichier</a>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <div class="empty-state">
            <i class="fas fa-comment-slash"></i>
            <p>No messages yet</p>
        </div>
    @endif
</div>

            <!-- Formulaire d'envoi de message -->
            <div class="chat-input">
                <form action="{{ route('chatDP.send') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="receiver_id" id="receiver_id" value="{{ $patientUserId ?? '' }}" required>
                    <input type="text" name="message" id="message-input" placeholder="Écrire un message..." required>
                    <label for="file-input" class="file-icon">
                        <i class="fas fa-paperclip"></i>
                    </label>
                    <input type="file" name="file" id="file-input" style="display: none;">
                    <button type="submit"><i class="fas fa-paper-plane"></i></button>
                </form>
                <div id="output"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    const patientUserId = {{ $patientUserId ?? 'null' }};
    if (patientUserId) {
        loadMessages(patientUserId);
    }

    // Fonction pour charger les messages
   
    // Vérifier les nouveaux messages toutes les 5 secondes
    setInterval(() => {
        const patientUserId = {{ $patientUserId ?? 'null' }};
        if (patientUserId) {
            loadMessages(patientUserId);
        }
    }, 5000);
});
function loadMessages(receiverId) {
    const senderId = {{ auth()->id() }};
    const chatId = senderId < receiverId ? `${senderId}-${receiverId}` : `${receiverId}-${senderId}`;

    $.get(`/chatDP/fetch-messages/${receiverId}`, function(response) {
        console.log('Messages reçus:', response); // Debug
        const chatMessages = $('#chat-messages');
        chatMessages.empty();

        if (response.messages && response.messages.length > 0) {
            response.messages.forEach(message => {
                const messageClass = message.sender_id == senderId ? 'sent' : 'received';
                const messageTime = new Date(message.timestamp * 1000).toLocaleTimeString('fr-FR', { 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });
                
                const messageHtml = `
                    <div class="message ${messageClass}" data-message-id="${message.id}">
                        <div class="message-content">
                            <div class="message-header">
                                <span class="sender">${message.sender_name}</span>
                                <span class="time">${messageTime}</span>
                                ${message.sender_id == senderId ? `
                                    <button class="delete-btn" onclick="deleteMessage('${message.id}', this)" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                ` : ''}
                            </div>
                            <p class="text">${message.content}</p>
                            ${message.file_url ? `<a href="${message.file_url}" target="_blank" class="file-link">Voir le fichier</a>` : ''}
                        </div>
                    </div>
                `;
                chatMessages.append(messageHtml);
            });
        } else {
            chatMessages.append('<div class="empty-state"><i class="fas fa-comment-slash"></i><p>Aucun message pour le moment</p></div>');
        }
    }).fail(function(error) {
        console.error('Erreur lors de la récupération des messages:', error);
    });
}

function deleteMessage(messageId, buttonElement) {
    console.log("Deleting message:", { messageId });

    // Récupérer l'ID du chat depuis la vue
    const chatId = document.getElementById('chatId').value; // Ensure this is correctly set in your Blade template

    // Envoyer une requête DELETE à votre backend
    fetch(`/messages/${chatId}/${messageId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), // CSRF token for Laravel
        },
    })
    .then(response => {
        if (response.ok) {
            console.log("Message supprimé avec succès.");
            // Supprimer le message de l'interface utilisateur
            buttonElement.closest('.message').remove();
        } else {
            console.error("Erreur lors de la suppression :", response.statusText);
        }
    })
    .catch(error => {
        console.error("Erreur :", error);
    });
}
</script>
@endsection
@section('styles')
<style>
    .chat-container {
        display: flex;
        flex-direction: column;
        height: 100vh;
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
        padding: 15px;
        background-color: #f8f9fa;
        border-top: 1px solid #ddd;
        display: flex;
        align-items: center;
    }

    .chat-input input[type="text"] {
        flex: 1;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        margin-right: 10px;
    }

    .chat-input button {
        background-color: #007bff;
        border: none;
        color: white;
        padding: 10px;
        border-radius: 5px;
        cursor: pointer;
    }
</style>
@endsection
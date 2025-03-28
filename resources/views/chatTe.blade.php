@extends('layouts.app')

@section('content')
<div class="chat-container">
    <!-- Inclusion de Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- En-tête du Chat -->
  <div class="chat-header">

        <img src="{{ asset('images/icons/a.png') }}" alt="Icône discussion médicale" class="custom-icon" width="40" height="40">
        <h2>Docteur & Télésecrétariat Discussion </h2>
    </div>


    <!-- Contenu Principal -->
    <div class="chat-main">
        <!-- Liste des Conversations (Doctors) -->
        <div class="conversation-list">
    <div class="conversation-header">
        <h6>La liste des télésecrétariats</h6>
    </div>
    @if(isset($teleSecretariats) && $teleSecretariats->count() > 0)
        @foreach($teleSecretariats as $doctorTelesecretariat)
            @if($doctorTelesecretariat->telesecretariat)
            @php
                    // Correction clé d'accès avec user_id
                    $teleUserId = $doctorTelesecretariat->telesecretariat->user_id;
                    $lastMessageForTele = $lastMessages[$teleUserId] ?? null; // <-- Clé correcte
                    @endphp
<a href="{{ route('chatT.show', ['doctorUserId' => auth()->id(), 'teleSecretariatUserId' => $teleUserId]) }}" 
   class="conversation-item" 
   data-id="{{ $doctorTelesecretariat->telesecretariat->id }}" 
   data-user-id="{{ $teleUserId }}">
    <div class="telesecretariat-avatar">
        <i class="fas fa-user"></i>
    </div>
    <div class="telesecretariat-info">
        <span class="name">{{ $doctorTelesecretariat->telesecretariat->nomCentre }}</span>
        <span class="status online"></span>
        @if($lastMessageForTele)
    <div class="last-message">
        <p class="last-message-text">
            {{ $lastMessageForTele['content'] ?? '[Fichier joint]' }}
        </p>
        <span class="last-message-time">
            {{ \Carbon\Carbon::parse($lastMessageForTele['timestamp'])->format('H:i') }}
        </span>
    </div>
@else
    <div class="no-message">Pas encore de messages !</div>
@endif

    </div>
</a>

            @endif
        @endforeach
    @else
        <div class="empty-state">
            <i class="fas fa-comment-slash"></i>
            <p>No télésecrétariats available</p>
        </div>
    @endif
</div>
<div class="chat-area">
<div class="chat-messages" id="chat-messages">
    @if(isset($messages) && count($messages) > 0)
        @foreach($messages as $message)
            <!-- Log pour déboguer -->
            {{ \Log::info('Message affiché:', ['message' => $message]) }}

            <div class="message {{ $message['sender_id'] == auth()->id() ? 'sent' : 'received' }}" data-timestamp="{{ $message['timestamp'] }}" data-message-id="{{ $message['id'] }}">
                <div class="message-content">
                    <div class="message-header">
                        <span class="sender">{{ $message['sender_name'] }}</span>
                        <span class="time">{{ \Carbon\Carbon::createFromTimestamp($message['timestamp'])->format('H:i') }}</span>
                        @if($message['sender_id'] == auth()->id())
                        <button class="delete-btn" onclick="deleteMessage('{{ $chatId }}', '{{ $message['id'] }}', this)" title="Supprimer le message">
    <i class="fas fa-trash-alt"></i>
</button>
                        @endif
                    </div>
                    <p class="text">{{ $message['content'] }}</p>
                    @if(!empty($message['file_url']))
    @if(preg_match('/\.(jpg|jpeg|png|gif)$/i', $message['file_url']))
        <img src="{{ $message['file_url'] }}" alt="Image" class="file-image">
    @else
        <a href="{{ $message['file_url'] }}" target="_blank" class="file-link">Voir le fichier</a>
    @endif
@endif

                </div>
            </div>
        @endforeach
    @else
        <div class="empty-state">
            <i class="fas fa-comment-slash"></i>
            <p>Pas encore de messages !</p>
        </div>
    @endif
</div>

    <div class="chat-input">
        <form action="{{ route('chatT.send') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="receiver_id" id="receiver_id" value="{{ $teleSecretariatUserId ?? '' }}" required>
            <input type="text" name="message" id="message-input" placeholder="Écrire un message..." required>
            <label for="file-input" class="file-icon">
                <i class="fas fa-paperclip"></i>
            </label>
            <input type="file" name="file" id="file-input" style="display: none;">
            <button type="submit"><i class="fas fa-paper-plane"></i></button>
        </form>
        <div id="output"></div>
    </div>    </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    const teleSecretariatUserId = {{ $teleSecretariatUserId ?? 'null' }};
    if (teleSecretariatUserId) {
        loadMessages(teleSecretariatUserId);
    }

    // Fonction pour charger les messages
    function loadMessages(receiverId) {
        const senderId = {{ auth()->id() }};
        const chatId = senderId < receiverId ? `${senderId}-${receiverId}` : `${receiverId}-${senderId}`;

        $.get(`/chatT/fetch-messages/${receiverId}`, function(response) {
            const chatMessages = $('#chat-messages');
            chatMessages.empty();

            if (response.messages.length > 0) {
                response.messages.forEach(message => {
                    const messageClass = message.sender_id == senderId ? 'sent' : 'received';
                    const messageContent = `
                        <div class="message ${messageClass}">
                            <div class="message-content">
                                <div class="message-header">
                                    <span class="sender">${message.sender_name}</span>
                                    <span class="time">${new Date(message.timestamp * 1000).toLocaleTimeString()}</span>
                                    ${message.sender_id == senderId ? `
                                      
                                    ` : ''}
                                </div>
                                <p class="text">${message.content}</p>
                                ${message.file_url ? `<a href="${message.file_url}" target="_blank" class="file-link">Voir le fichier</a>` : ''}
                            </div>
                        </div>
                    `;
                    chatMessages.append(messageContent);
                });
            } else {
                chatMessages.html('<div class="empty-state"><i class="fas fa-comment-slash"></i><p>Pas encore de messages !</p></div>');
            }
            chatMessages.scrollTop(chatMessages[0].scrollHeight);
        });
    }

    // Vérifier les nouveaux messages toutes les 5 secondes
    setInterval(() => {
        const teleSecretariatUserId = {{ $teleSecretariatUserId ?? 'null' }};
        if (teleSecretariatUserId) {
            loadMessages(teleSecretariatUserId);
        }
    }, 5000);
});
function deleteMessage(chatId, firebaseMessageId, buttonElement) {
    const deleteUrl = `https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app/chatTE/${chatId}/messages/${firebaseMessageId}.json`;

    fetch(deleteUrl, { method: 'DELETE' })
    .then(response => {
        if (response.ok) {
            buttonElement.closest('.message').remove();
        } else {
            console.error("Échec de la suppression");
        }
    })
    .catch(error => console.error("Erreur:", error));
}
function listenForDeletedMessages(chatId) {
    const chatRef = firebase.database().ref(`chatTE/${chatId}/messages`);

    chatRef.on('child_removed', (snapshot) => {
        const deletedMessageId = snapshot.key;
        console.log("Message supprimé :", deletedMessageId);

        // Supprimer le message de l'interface utilisateur
        const messageElement = document.querySelector(`.message[data-id="${deletedMessageId}"]`);
        if (messageElement) {
            messageElement.remove();
        }
    });
}

function listenForDeletedMessages(doctorUserId) {
    const userId = {{ auth()->user()->id }}; // Assuming you're using Laravel Blade to inject this value
    const chatId = userId < doctorUserId ? `${userId}-${doctorUserId}` : `${doctorUserId}-${userId}`;

    const chatRef = firebase.database().ref(`chatTE/${chatId}/messages`);
    chatRef.on('child_removed', (snapshot) => {
        const messageId = snapshot.key;
        const messageElement = document.querySelector(`.message[data-id="${messageId}"]`);
        if (messageElement) {
            messageElement.remove();
        }
    });
}


    // Fonction pour vérifier les nouveaux messages
    function checkForNewMessages() {
        const teleSecretariatUserId = {{ $teleSecretariatUserId ?? 'null' }};
        if (teleSecretariatUserId) {
            loadMessages(teleSecretariatUserId);
        }
    }
</script>
@endsection

@section('styles')
<style>
    /* Chat Container */
      /* Chat Container */
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
@extends('layouts.app')

@section('content')
<div class="chat-container">
    <!-- Inclusion de Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- En-tête du Chat -->
  <div class="chat-header">

        <img src="{{ asset('storage/images/a.png') }}" alt="Icône discussion médicale" class="custom-icon" width="40" height="40">
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
    <div class="no-message">No messages yet</div>
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
                                        <button class="delete-btn" onclick="deleteMessage('${message.id}', this)" title="Supprimer le message">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
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
                chatMessages.html('<div class="empty-state"><i class="fas fa-comment-slash"></i><p>No messages yet</p></div>');
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

   

/* Chat Header */

.header-content {
    display: flex;
    align-items: center;
    gap: 15px;
    width: 100%;
}

.active-telesecretariat-avatar {
    width: 40px;
    height: 40px;
    background-color:rgb(51, 99, 151); /* Bleu professionnel pour l'avatar */
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.active-telesecretariat-avatar i {
    font-size: 20px;
}

.active-telesecretariat-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.active-telesecretariat-info .name {
    font-weight: bold;
    font-size: 18px;
    color: #333; /* Texte foncé pour le titre */
}

.active-telesecretariat-info .status {
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
    .chat-header .header-content {
        display: flex;
        align-items: center;
        justify-content: center;
    }   .chat-header {
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

    .chat-header i {
        font-size: 24px;
        margin-right: 10px;
    }

    /* Chat Main */
    .chat-main {
        display: flex;
        flex: 1;
        overflow: hidden;
    }

    /* Conversation List */
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

    .telesecretariat-avatar {
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

    .telesecretariat-info {
        flex: 1;
    }

    .telesecretariat-info .name {
        font-weight: bold;
    }

    .telesecretariat-info .specialty {
        font-size: 12px;
        color: #666;
    }

    .telesecretariat-info .status {
        font-size: 12px;
        color: green;
    }

    /* Chat Area */
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

    .message {
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

.chat-input {
    padding: 15px;
    background-color: #f8f9fa;
    border-top: 1px solid #ddd;
    display: flex;
    align-items: center;
}

.message-form {
    display: flex;
    align-items: center;
    width: 100%;
}



.file-icon {
    cursor: pointer;
    font-size: 20px;
    color: #007bff;
    margin-right: 10px; /* Espace entre l'icône et l'input */
}

.file-icon:hover {
    color: #0056b3;
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
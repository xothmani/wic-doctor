
3 of 2,946
d
Inbox

Samar Aouadi <samaraouadi7@gmail.com>
Attachments
Apr 4, 2025, 10:07 AM (3 days ago)
to me



 One attachment
  •  Scanned by Gmail
@extends('layouts.app')

@section('content')
<div class="chat-container">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <div class="chat-header">
        <img src="{{ asset('images/icons/a.png') }}" 
             alt="Icône discussion" 
             class="custom-icon"
             width="40" 
             height="40">
        <h1>Docteur & Télésecrétariat Discussion</h1>
    </div>

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
<div class="message-file">
    @php
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $fileExtension = pathinfo($message['file_url'], PATHINFO_EXTENSION);
    @endphp

    @if(in_array(strtolower($fileExtension), $imageExtensions))
        <div class="message-image">
            <a href="{{ $message['file_url'] }}" data-lightbox="image-{{ $message['id'] }}" data-title="Fichier joint">
                <img src="{{ $message['file_url'] }}" alt="Fichier image">
            </a>
        </div>
    @else
        <div class="file-details">
            <i class="fas fa-file-alt"></i>
            <a href="{{ $message['file_url'] }}" target="_blank">Voir le fichier</a>
            <a href="{{ $message['file_url'] }}" download class="download-link">
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
                <div class="empty-state">
                    <i class="fas fa-comment-slash"></i>
                    <p>Pas encore de messages !</p>
                </div>
            @endif
        </div>

        <div class="chat-input">
            <form action="{{ route('chatT.send') }}" method="POST" enctype="multipart/form-data" id="chat-form">
                @csrf
                <input type="hidden" name="receiver_id" id="receiver_id" value="{{ $teleSecretariatUserId ?? '' }}" >

                <div class="input-container">
                <input type="file" name="file" id="file-input" >
            <!-- Zone d'affichage des fichiers attachés -->
            <div id="output"></div>

                    
                    <input type="text" name="message" id="message-input" placeholder="Écrire un message..." >
                    
                    <button type="submit" class="send-button">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        </div>  </div>
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

        setTimeout(() => {
    location.reload(true); // Recharge la page depuis le serveur sans utiliser le cache
}, 100000); 

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
            // File preview handling
        document.getElementById('file-input').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const outputDiv = document.getElementById('output');
            outputDiv.innerHTML = '';

            if (file) {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        outputDiv.innerHTML = `<img src="${e.target.result}" alt="Aperçu" style="max-width: 100px; max-height: 100px;">`;
                    };
                    reader.readAsDataURL(file);
                } else {
                    outputDiv.innerHTML = `<p><i class="fas fa-file"></i> ${file.name}</p>`;
                }
            }
        });
        </script>
        @endsection

        @section('styles')

<style>
.file-icon { cursor: pointer; font-size: 20px; color: #007bff; margin-right: 10px; /* Espace entre l'icône et l'input */ } 
.message-image img { max-width: 200px; border-radius: 8px; margin-top: 5px; cursor: zoom-in; transition: transform 0.3s ease; } 
/* Prévisualisation du fichier */ .file-preview { color: #ff4444; cursor: pointer; margin-left: 10px; }        
    .file-icon:hover { color: #0056b3; } /* Chat Header */ /* Suggestions de patients */ /* Suggestions de patients */ 
    .patient-suggestions { position: absolute; bottom: 60px; left: 0; width: 100%; max-height: 150px; overflow-y: auto; background-color: white; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); z-index: 1000; display: none; } 
    /* Fichiers et images - Style amélioré */ .message-file { margin-top: 8px; padding: 10px; background: #f8f9fa; border-radius: 8px; } 
    .message-file:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1); } /* Prévisualisation fichier */ .file-preview { display: flex; align-items: center; gap: 15px; margin: 15px 0; padding: 15px; background: #f8f9ff; border: 2px dashed #e0e7ff; border-radius: 15px; position: relative; overflow: hidden; } .file-preview::before { content: ""; position: absolute; top: 0; left: -100%; width: 50%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent); animation: shine 1.5s infinite; } @keyframes shine { 100% { left: 200%; } } .file-preview img { width: 80px; height: 80px; object-fit: cover; border-radius: 10px; border: 2px solid #fff; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); } .file-info { flex: 1; position: relative; } .file-info div { font-size: 14px; font-weight: 600; color: #2d3748; word-break: break-all; } .file-info small { display: block; font-size: 12px; color: #718096; margin-top: 4px; } .remove-file { color: #ff4444; cursor: pointer; transition: transform 0.2s ease; } .remove-file:hover { transform: scale(1.2); }.file-link:hover { text-decoration: underline; } /* Animation de téléchargement */ .download-link { display: inline-flex; align-items: center; gap: 8px; padding: 8px 15px; background: #007bff; color: white !important; border-radius: 25px; text-decoration: none; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden; } .download-link::before { content: ""; position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(120deg, transparent, rgba(255,255,255,0.4), transparent); transition: 0.5s; } .download-link:hover::before { left: 100%; } .download-link:hover { background: #0056b3; box-shadow: 0 4px 12px rgba(0,123,255,0.3); } /* Input file stylisé */ .file-icon { cursor: pointer; padding: 12px; background: #f0f4ff; border-radius: 50%; transition: all 0.3s ease; } .file-icon:hover { background: #007bff; color: white; transform: rotate(15deg); } /* Message images */ .message-image { position: relative; border-radius: 12px; overflow: hidden; transition: transform 0.3s ease; } .message-image img { width: 100%; max-width: 300px; height: auto; border-radius: 12px; cursor: zoom-in; transition: transform 0.3s ease; } .message-image:hover img { transform: scale(1.03); } /* Fichiers non-images */ .message-file .file-icon { font-size: 24px; color: #007bff; min-width: 40px; text-align: center; } .file-details { display: flex; align-items: center; gap: 10px; color: #666; } /* Progress bar animation */ @keyframes upload-progress { 0% { width: 0%; } 100% { width: 100%; } } .upload-progress { height: 3px; background: #007bff; animation: upload-progress 2s ease-out; position: absolute; bottom: 0; left: 0; } /* Responsive design */ @media (max-width: 768px) { .file-preview { flex-direction: column; align-items: flex-start; } .message-image img { max-width: 200px; } .download-link { padding: 4px 8px; background: #e3f2fd; border-radius: 4px; display: flex; align-items: center; gap: 5px; transition: background 0.3s ease; } } .patient-suggestions .patient-item { padding: 10px; cursor: pointer; transition: background-color 0.3s; } /* Animation for the badge */ @keyframes bounce { 0% { transform: translateY(0); } 100% { transform: translateY(-20px); /* Badge will slightly bounce up and down */ } } /* Show badge when there is a notification */ .notification-icon .badge.show { display: inline-block;  /* Show the badge when needed */ } .patient-suggestions .patient-item:hover { background-color: #f0f2f5; } .chat-header { position: sticky; top: 0; z-index: 1000; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); height: 60px; display: flex; /* Ajouté */ align-items: center; /* Ajouté */ justify-content: center; /* Ajouté */ width: 100%; /* Assure la pleine largeur */ } .header-content { display: flex; align-items: center; gap: 15px; /* Espacement entre l'icône et le titre */ max-width: 1200px; /* Limite la largeur du contenu */ width: 100%; /* Prend toute la largeur disponible */ padding: 0 20px; /* Marge interne latérale */ } .custom-icon { filter: drop-shadow(0 2px 2px rgba(0,0,0,0.1)); /* Effet visuel subtil */ object-fit: contain; /* Garantit une image bien proportionnée */ } .chat-header h1 { font-size: 1.4rem; color: #2c3e50; /* Couleur professionnelle */ margin: 0; /* Supprime la marge par défaut */ font-weight: 600; } .active-doctor-avatar { width: 40px; height: 40px; background-color:rgb(51, 99, 151); /* Bleu professionnel pour l'avatar */ border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; } .active-doctor-info { flex: 1; display: flex; flex-direction: column; gap: 3px; } .active-doctor-info .name { font-weight: bold; font-size: 18px; color: #333; /* Texte foncé pour le titre */ } .doctor-avatar { width: 50px; /* Ajuster selon besoin */ height: 50px; /* Doit être identique à width pour un cercle parfait */ border-radius: 50%; overflow: hidden; /* Coupe l'image qui dépasse */ display: flex; align-items: center; justify-content: center; } .doctor-avatar img { width: 100%; /* Remplit tout l’espace du parent */ height: 100%; object-fit: cover; /* S'assure que l'image couvre bien tout le cercle */ } .active-doctor-info .status { font-size: 12px; color: #28a745; /* Vert pour indiquer "Connecté" */ } .header-actions { display: flex; align-items: center; gap: 10px; } .header-actions button { background: none; border: none; color: #007bff; /* Bleu pour les icônes */ font-size: 18px; cursor: pointer; transition: color 0.3s ease; } .header-actions button:hover { color: #0056b3; /* Bleu plus foncé au survol */ } /* Chat Main */ /* Conversation List */ .conversation-header { padding: 15px; background-color: #f8f9fa; border-bottom: 1px solid #ddd; } .conversation-item { display: flex; align-items: center; padding: 10px; cursor: pointer; background-color: white; /* Couleur unifiée */ transition: none; /* Supprime la transition */ } /* SUPPRIMER CE BLOC ENTIEREMENT */ .friendly-title { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 32px; font-weight: 600; color:rgb(53, 53, 56); /* A soft green for a friendly touch */ text-align: center; text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.1); letter-spacing: 1px; margin-bottom: 20px; line-height: 1.5; } .conversation-item { transition: background-color 0.2s ease; } .conversation-item.active { background-color: #f0f2f5; border-left: 4px solid #007bff; position: relative; } .conversation-item.active::after { content: ""; position: absolute; right: -1px; top: 50%; transform: translateY(-50%); height: 60%; width: 2px; background-color: #007bff; } .conversation-item:hover { background-color: #f8f9fa; transform: translateX(3px); } .doctor-info { flex: 1; } .doctor-info .name { font-weight: bold; } .doctor-info .specialty { font-size: 12px; color: #666; } .doctor-info .status { font-size: 12px; color: green; } /* Chat Area */ /* Masquer l'input file */ .file-upload { display: inline-flex; align-items: center; gap: 10px; cursor: pointer; padding: 8px 12px; background-color:rgb(10, 9, 9); border-radius: 8px; border: 1px solid #ccc; transition: all 0.3s ease; }                                      .message { display: flex; margin-bottom: 10px; } .message.sent { justify-content: flex-end; } .message.received { justify-content: flex-start; } /* Bouton de suppression */ .delete-btn { background: transparent; border: none; color: rgba(255, 255, 255, 0.7); /* Couleur discrète pour les messages envoyés */ cursor: pointer; margin-left: 10px; font-size: 14px; transition: color 0.3s ease; } /* Style pour les messages reçus */ .message.received .delete-btn { color: rgba(0, 0, 0, 0.5); /* Couleur discrète pour les messages reçus */ } /* Effet au survol */ .delete-btn:hover { color: #ff4444; /* Rouge plus vif au survol pour indiquer l'action de suppression */ } .message-content { max-width: 70%; padding: 10px; border-radius: 10px; background-color: white; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1); position: relative; } .message.sent .message-content { background-color: #007bff; color: white; } .message-header { font-size: 12px; color: #666; margin-bottom: 5px; display: flex; align-items: center; } .message.sent .message-header { color: rgba(255, 255, 255, 0.7); } .message p.text { font-size: 14px; margin: 0; } /* Bouton de suppression */ .input-container { position: relative; flex: 1; margin-right: 10px; } .chat-input input[type="file"] { margin-right: 10px; } .file-name-display { margin: 0 10px; color: #666; font-size: 14px; max-width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; } .download-link:hover { background: #bbdefb; text-decoration: none; } .chat-input button { background-color: #007bff; border: none; color: white; padding: 10px; border-radius: 5px; cursor: pointer; } .chat-input input[type="file"] { margin-right: 10px; }html::-webkit-scrollbar { display: none; } .chat-input { position: sticky; bottom: 0; background: white; padding: 15px; box-shadow: 0 -2px 10px rgba(0,0,0,0.1); z-index: 1000; } .chat-input input[type="text"] { flex: 1; width: 450px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; margin-right: 10px; } .send-button { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background-color: #007bff; border: none; color: white; padding: 8px 12px; border-radius: 50%; cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center; } .send-button:hover { background-color: #0056b3; } .message-form { display: flex; align-items: center; width: 100%; } .input-container { position: relative; flex: 1; display: flex; align-items: center; background-color: white; border: 1px solid #ddd; border-radius: 25px; 
    /* Arrondir les coins */ padding: 5px 10px; /* Espace interne */ } 
    #message-input { flex: 1; border: none; outline: none; font-size: 14px; padding: 10px 0; /* Ajustez le padding pour correspondre au design */ } 
    .send-button { background-color: #007bff; border: none; color: white; padding: 8px 12px; border-radius: 50%; cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center; margin-left: 10px; /* Espace entre l'input et le bouton */ } .send-button:hover { background-color: #0056b3; } #patient-suggestions { border: 2px solid red; /* Pour visualiser le conteneur */ background-color: white; z-index: 1000; } /* Fixed header */ /* Main chat area that will scroll */ /* Messages container */ /* Fixed input area */ .chat-container { display: flex; flex-direction: column; height: 100vh; background-color: #f0f2f5; } .chat-main { flex: 1; display: flex; overflow: hidden; } .conversation-list { width: 300px; background-color: white; border-right: 1px solid #ddd; overflow-y: auto; height: calc(100vh - 60px); /* Hauteur totale - header */ } .chat-area { flex: 1; display: flex; flex-direction: column; } .chat-messages { flex: 1; padding: 15px; padding-bottom: 60px; /* Réduit de 80px à 60px */ overflow-y: auto; background-color: #f0f2f5; } /* Supprimer les marges existantes */ .chat-main { margin-top: 0; margin-bottom: 0; } /* Ajuster le padding des messages pour l'espace vertical */ /* Assurer que les éléments sticky restent collés */ .conversation-list { position: sticky; top: 60px; height: calc(100vh - 60px); } </style>

        @endsection
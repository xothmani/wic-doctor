@extends('layouts.app')

@section('content')
<div class="chat-container">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
        <!-- Liste des Conversations -->
        <div class="conversation-list">
            <div class="conversation-header">
                <h6>
                    @if(auth()->user()->doctor)
                        Télésecrétaires 
                    @else
                        Docteurs 
                    @endif
                </h6>
            </div>

            @if($partners->count() > 0)
    @foreach($partners as $partner)
        @if(auth()->user()->doctor)
            <!-- Affichage pour les docteurs -->
            @php
                $teleUser = $partner;
                $lastMessage = $lastMessages[$teleUser->id] ?? null;
                $isActive = isset($teleSecretariatUserId) && $teleUser->id == $teleSecretariatUserId;
            @endphp
            <a href="{{ route('chatT.show', [
                'doctorUserId' => auth()->id(),
                'teleSecretariatUserId' => $teleUser->id
            ]) }}" class="conversation-item {{ $isActive ? 'active' : '' }}">
                <div class="doctor-avatar">
                    <i class="fas fa-user-md" style="width: 40px; height: 40px; background-color: rgb(51, 99, 151); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; margin-right: 10px;"></i>
                </div>
                <div class="doctor-info">
                    <span class="name">{{ $teleUser->name }}</span>
                    @if($lastMessage)
                        <div class="last-message">
                            <p class="last-message-text">
                                {{ $lastMessage['content'] ?? '[Fichier joint]' }}
                            </p>
                            <span class="last-message-time">
                                {{ \Carbon\Carbon::createFromTimestamp($lastMessage['timestamp'])->format('H:i') }}
                            </span>
                        </div>
                    @else
                        <div class="no-message">Pas encore de messages !</div>
                    @endif
                </div>
            </a>
        @elseif(auth()->user()->telesecretariat)
            <!-- Affichage pour les télésecrétaires -->
            @php
                $doctorUser = $partner;
                $lastMessage = $lastMessages[$doctorUser->id] ?? null;
                $isActive = isset($doctorUserId) && $doctorUser->id == $doctorUserId;
            @endphp
            <a href="{{ route('chatT.show', [
                'doctorUserId' => $doctorUser->id,
                'teleSecretariatUserId' => auth()->id()
            ]) }}" class="conversation-item {{ $isActive ? 'active' : '' }}">
                <div class="doctor-avatar">
                    <i class="fas fa-user-md" style="width: 40px; height: 40px; background-color: rgb(51, 99, 151); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; margin-right: 10px;"></i>
                </div>
                <div class="doctor-info">
                    <span class="name">{{ $doctorUser->name }}</span>
                    @if($lastMessage)
                        <div class="last-message">
                            <p class="last-message-text">
                                {{ $lastMessage['content'] ?? '[Fichier joint]' }}
                            </p>
                            <span class="last-message-time">
                                {{ \Carbon\Carbon::createFromTimestamp($lastMessage['timestamp'])->format('H:i') }}
                            </span>
                        </div>
                    @else
                        <div class="no-message">Pas encore de messages !</div>
                    @endif
                </div>
            </a>
        @endif
    @endforeach
@endif

        </div>

        <div class="chat-area">
            <div class="chat-messages" id="chat-messages">
                @if(isset($messages) && count($messages) > 0)
                    @foreach($messages as $message)
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
                    <input type="hidden" name="receiver_id" id="receiver_id" value="{{ auth()->user()->doctor ? $teleSecretariatUserId : $doctorUserId }}">

                    <div class="input-container">
                        <input type="file" name="file" id="file-input">
                        <!-- Zone d'affichage des fichiers attachés -->
                        <div id="output"></div>
                        
                        <input type="text" name="message" id="message-input" placeholder="Écrire un message...">
                        
                        <button type="submit" class="send-button">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialisation
    const teleSecretariatUserId = {{ $teleSecretariatUserId ?? 'null' }};
    const doctorUserId = {{ $doctorUserId ?? 'null' }};
    const partnerId = teleSecretariatUserId || doctorUserId;
    
    if (partnerId) {
        loadMessages(partnerId);
    }

    // Fonction pour charger les messages
    function loadMessages(userId) {
    // Mettre à jour l'ID du récepteur dans le formulaire
    document.getElementById('receiver_id').value = userId;

    // Charger les messages via une requête AJAX
    fetch(`/chat/${userId}/messages`)
        .then(response => response.json())
        .then(data => {
            const chatMessagesContainer = document.getElementById('chat-messages');
            chatMessagesContainer.innerHTML = ''; // Vider les messages actuels

            if (data.messages && data.messages.length > 0) {
                data.messages.forEach(msg => {
                    const isSent = msg.sender_id === parseInt(document.getElementById('auth_id').value);
                    const fileExtension = msg.file_url ? msg.file_url.split('.').pop().toLowerCase() : '';
                    const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExtension);
                    const isPdf = fileExtension === 'pdf';
                    const isTxt = fileExtension === 'txt';

                    const fileBlock = msg.file_url ? (
                        isImage ? `
                            <div class="message-image">
                                <a href="${msg.file_url}" data-lightbox="image-${msg.id}" data-title="Fichier joint">
                                    <img src="${msg.file_url}" alt="Fichier image">
                                </a>
                            </div>
                        ` : `
                            <div class="file-details">
                                <i class="fas ${isPdf ? 'fa-file-pdf' : isTxt ? 'fa-file-alt' : 'fa-file'}"></i>
                                <span class="file-name">${msg.file_name || msg.file_url.split('/').pop()}</span>
                                <a href="${msg.file_url}" target="_blank" class="view-file">${(isPdf || isTxt) ? 'Ouvrir' : 'Télécharger'}</a>
                                <a href="${msg.file_url}" download class="download-link" title="Télécharger le fichier">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        `
                    ) : '';

                    const messageElement = document.createElement('div');
                    messageElement.classList.add('message', isSent ? 'sent' : 'received');
                    messageElement.setAttribute('data-timestamp', msg.timestamp);
                    messageElement.innerHTML = `
                        <div class="message-content">
                            <div class="message-header">
                                <span class="sender">${msg.sender_name}</span>
                                <span class="time">${new Date(msg.timestamp * 1000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
                            </div>
                            <p class="text">${msg.content}</p>
                            ${fileBlock}
                        </div>
                    `;
                    chatMessagesContainer.appendChild(messageElement);
                });
            } else {
                // Afficher un message si aucun message n'est trouvé
                chatMessagesContainer.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-comment-slash"></i>
                        <p>Pas encore de messages !</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement des messages:', error);
        });
}

    // Vérifier les nouveaux messages toutes les 5 secondes
    setInterval(() => {
        const partnerId = teleSecretariatUserId || doctorUserId;
        if (partnerId) {
            loadMessages(partnerId);
        }
    }, 5000);

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
});

function deleteMessage(chatId, firebaseMessageId, buttonElement) {
    // URL Firebase pour supprimer le message
    const deleteUrl = `https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app/chatTE/${chatId}/messages/${firebaseMessageId}.json`;

    // Afficher la confirmation avec SweetAlert2 avant de supprimer
    Swal.fire({
        title: 'Supprimer ce message ?',
        text: "Cette action est irréversible.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            // Effectuer la suppression via l'URL
            fetch(deleteUrl, { method: 'DELETE' })
                .then(response => {
                    if (response.ok) {
                        // Supprimer le message de l'interface utilisateur
                        const messageElement = buttonElement.closest('.message');
                        if (messageElement) {
                            messageElement.remove();
                        }

                        // Afficher une notification de succès
                        Swal.fire('Supprimé !', 'Le message a été supprimé.', 'success');
                    } else {
                        // En cas d'erreur, afficher une alerte d'erreur
                        Swal.fire('Erreur', 'Une erreur est survenue.', 'error');
                    }
                })
                .catch(error => {
                    console.error("Erreur :", error);
                    Swal.fire('Erreur', 'Impossible de supprimer ce message.', 'error');
                });
        }
    });
}


// Rafraîchissement périodique
setTimeout(() => {
    location.reload(true);
}, 100000);
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
    .message-file:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1); } /* Prévisualisation fichier */ .file-preview { display: flex; align-items: center; gap: 15px; margin: 15px 0; padding: 15px; background: #f8f9ff; border: 2px dashed #e0e7ff; border-radius: 15px; position: relative; overflow: hidden; } .file-preview::before { content: ""; position: absolute; top: 0; left: -100%; width: 50%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent); animation: shine 1.5s infinite; } @keyframes shine { 100% { left: 200%; } } .file-preview img { width: 80px; height: 80px; object-fit: cover; border-radius: 10px; border: 2px solid #fff; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); } .file-info { flex: 1; position: relative; } .file-info div { font-size: 14px; font-weight: 600; color: #2d3748; word-break: break-all; } .file-info small { display: block; font-size: 12px; color: #718096; margin-top: 4px; } .remove-file { color: #ff4444; cursor: pointer; transition: transform 0.2s ease; } .remove-file:hover { transform: scale(1.2); }.file-link:hover { text-decoration: underline; } /*
     Animation de téléchargement */ .download-link { display: inline-flex; align-items: center; gap: 8px; padding: 8px 15px; background: #007bff; color: white !important; border-radius: 25px; text-decoration: none; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden; } .download-link::before { content: ""; position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(120deg, transparent, rgba(255,255,255,0.4), transparent); transition: 0.5s; } .download-link:hover::before { left: 100%; } .download-link:hover { background: #0056b3; box-shadow: 0 4px 12px rgba(0,123,255,0.3); } /* Input file stylisé */ .file-icon { cursor: pointer; padding: 12px; background: #f0f4ff; border-radius: 50%; transition: all 0.3s ease; } .file-icon:hover { background: #007bff; color: white; transform: rotate(15deg); } /* Message images */ .message-image { position: relative; border-radius: 12px; overflow: hidden; transition: transform 0.3s ease; } .message-image img { width: 100%; max-width: 300px; height: auto; border-radius: 12px; cursor: zoom-in; transition: transform 0.3s ease; } .message-image:hover img { transform: scale(1.03); } /* Fichiers non-images */ .message-file .file-icon { font-size: 24px; color: #007bff; min-width: 40px; text-align: center; } .file-details { display: flex; align-items: center; gap: 10px; color: #666; } /* Progress bar animation */ @keyframes upload-progress { 0% { width: 0%; } 100% { width: 100%; } } .upload-progress { height: 3px; background: #007bff; animation: upload-progress 2s ease-out; position: absolute; bottom: 0; left: 0; } /* Responsive design */ @media (max-width: 768px) { .file-preview { flex-direction: column; align-items: flex-start; } .message-image img { max-width: 200px; } .download-link { padding: 4px 8px; background: #e3f2fd; border-radius: 4px; display: flex; align-items: center; gap: 5px; transition: background 0.3s ease; } } .patient-suggestions .patient-item { padding: 10px; cursor: pointer; transition: background-color 0.3s; } /* Animation for the badge */ @keyframes bounce { 0% { transform: translateY(0); } 100% { transform: translateY(-20px); /* Badge will slightly bounce up and down */ } } /* Show badge when there is a notification */ .notification-icon .badge.show { display: inline-block;  /* Show the badge when needed */ } .patient-suggestions .patient-item:hover { background-color: #f0f2f5; } .chat-header { position: sticky; top: 0; z-index: 1000; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); height: 60px; display: flex; /* Ajouté */ align-items: center; /* Ajouté */ justify-content: center; /* Ajouté */ width: 100%; /* Assure la pleine largeur */ } .header-content { display: flex; align-items: center; gap: 15px; /* Espacement entre l'icône et le titre */ max-width: 1200px; /* Limite la largeur du contenu */ width: 100%; /* Prend toute la largeur disponible */ padding: 0 20px; /* Marge interne latérale */ } .custom-icon { filter: drop-shadow(0 2px 2px rgba(0,0,0,0.1)); /* Effet visuel subtil */ object-fit: contain; /* Garantit une image bien proportionnée */ } .chat-header h1 { font-size: 1.4rem; color: #2c3e50; /* Couleur professionnelle */ margin: 0; /* Supprime la marge par défaut */ font-weight: 600; } .active-doctor-avatar { width: 40px; height: 40px; background-color:rgb(51, 99, 151); /* Bleu professionnel pour l'avatar */ border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; } .active-doctor-info { flex: 1; display: flex; flex-direction: column; gap: 3px; } .active-doctor-info .name { font-weight: bold; font-size: 18px; color: #333; /* Texte foncé pour le titre */ } .doctor-avatar { width: 50px; /* Ajuster selon besoin */ height: 50px; /* Doit être identique à width pour un cercle parfait */ border-radius: 50%; overflow: hidden; /* Coupe l'image qui dépasse */ display: flex; align-items: center; justify-content: center; } .doctor-avatar img { width: 100%; /* Remplit tout l’espace du parent */ height: 100%; object-fit: cover; /* S'assure que l'image couvre bien tout le cercle */ } .active-doctor-info .status { font-size: 12px; color: #28a745; /* Vert pour indiquer "Connecté" */ } .header-actions { display: flex; align-items: center; gap: 10px; } .header-actions button { background: none; border: none; color: #007bff; /* Bleu pour les icônes */ font-size: 18px; cursor: pointer; transition: color 0.3s ease; } .header-actions button:hover { color: #0056b3; /* Bleu plus foncé au survol */ } /* Chat Main */ /* Conversation List */ .conversation-header { padding: 15px; background-color: #f8f9fa; border-bottom: 1px solid #ddd; }
     
     .conversation-item {
    display: flex;
    align-items: center;
    padding: 10px;
    cursor: pointer;
    background-color: white;
    transition: all 0.3s ease;
    text-decoration: none;
    color: inherit;
    border-left: 4px solid transparent;
}
        
        
        .friendly-title { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 32px; font-weight: 600; color:rgb(53, 53, 56); /* A soft green for a friendly touch */ text-align: center; text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.1); letter-spacing: 1px; margin-bottom: 20px; line-height: 1.5; } .conversation-item { transition: background-color 0.2s ease; } 
        
        
        .conversation-item.active {
    background-color: #f0f2f5;
    border-left: 4px solid #007bff;
}        
        .conversation-item.active::after { content: ""; position: absolute; right: -1px; top: 50%; transform: translateY(-50%); height: 60%; width: 2px; background-color: #007bff; } 
        

        .conversation-item:hover {
    background-color: #f0f2f5;
    transform: translateX(3px);
}        
        .doctor-info { flex: 1; } .doctor-info .name { font-weight: bold; } .doctor-info .specialty { font-size: 12px; color: #666; } .doctor-info .status { font-size: 12px; color: green; } /* Chat Area */ /* Masquer l'input file */ .file-upload { display: inline-flex; align-items: center; gap: 10px; cursor: pointer; padding: 8px 12px; background-color:rgb(10, 9, 9); border-radius: 8px; border: 1px solid #ccc; transition: all 0.3s ease; }                                      .message { display: flex; margin-bottom: 10px; } .message.sent { justify-content: flex-end; } .message.received { justify-content: flex-start; } /* Bouton de suppression */ .delete-btn { background: transparent; border: none; color: rgba(255, 255, 255, 0.7); /* Couleur discrète pour les messages envoyés */ cursor: pointer; margin-left: 10px; font-size: 14px; transition: color 0.3s ease; } /* Style pour les messages reçus */ .message.received .delete-btn { color: rgba(0, 0, 0, 0.5); /* Couleur discrète pour les messages reçus */ } /* Effet au survol */ .delete-btn:hover { color: #ff4444; /* Rouge plus vif au survol pour indiquer l'action de suppression */ } .message-content { max-width: 70%; padding: 10px; border-radius: 10px; background-color: white; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1); position: relative; } .message.sent .message-content { background-color: #007bff; color: white; } .message-header { font-size: 12px; color: #666; margin-bottom: 5px; display: flex; align-items: center; } .message.sent .message-header { color: rgba(255, 255, 255, 0.7); } .message p.text { font-size: 14px; margin: 0; } /* Bouton de suppression */ .input-container { position: relative; flex: 1; margin-right: 10px; } .chat-input input[type="file"] { margin-right: 10px; } .file-name-display { margin: 0 10px; color: #666; font-size: 14px; max-width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; } .download-link:hover { background: #bbdefb; text-decoration: none; } .chat-input button { background-color: #007bff; border: none; color: white; padding: 10px; border-radius: 5px; cursor: pointer; } .chat-input input[type="file"] { margin-right: 10px; }html::-webkit-scrollbar { display: none; } .chat-input { position: sticky; bottom: 0; background: white; padding: 15px; box-shadow: 0 -2px 10px rgba(0,0,0,0.1); z-index: 1000; } .chat-input input[type="text"] { flex: 1; width: 450px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; margin-right: 10px; } .send-button { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background-color: #007bff; border: none; color: white; padding: 8px 12px; border-radius: 50%; cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center; } .send-button:hover { background-color: #0056b3; } .message-form { display: flex; align-items: center; width: 100%; } .input-container { position: relative; flex: 1; display: flex; align-items: center; background-color: white; border: 1px solid #ddd; border-radius: 25px; 
    /* Arrondir les coins */ padding: 5px 10px; /* Espace interne */ } 
    #message-input { flex: 1; border: none; outline: none; font-size: 14px; padding: 10px 0; /* Ajustez le padding pour correspondre au design */ } 
    .send-button { background-color: #007bff; border: none; color: white; padding: 8px 12px; border-radius: 50%; cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center; margin-left: 10px; /* Espace entre l'input et le bouton */ } .send-button:hover { background-color: #0056b3; } #patient-suggestions { border: 2px solid red; /* Pour visualiser le conteneur */ background-color: white; z-index: 1000; } /* Fixed header */ /* Main chat area that will scroll */ /* Messages container */ /* Fixed input area */ .chat-container { display: flex; flex-direction: column; height: 100vh; background-color: #f0f2f5; } .chat-main { flex: 1; display: flex; overflow: hidden; } .conversation-list { width: 300px; background-color: white; border-right: 1px solid #ddd; overflow-y: auto; height: calc(100vh - 60px); /* Hauteur totale - header */ } .chat-area { flex: 1; display: flex; flex-direction: column; } .chat-messages { flex: 1; padding: 15px; padding-bottom: 60px; /* Réduit de 80px à 60px */ overflow-y: auto; background-color: #f0f2f5; } /* Supprimer les marges existantes */ .chat-main { margin-top: 0; margin-bottom: 0; } /* Ajuster le padding des messages pour l'espace vertical */ /* Assurer que les éléments sticky restent collés */ .conversation-list { position: sticky; top: 60px; height: calc(100vh - 60px); } </style>

        @endsection

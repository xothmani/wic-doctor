@extends('layouts.app')

@section('content')
<div class="chat-container">
<link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-database.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <div class="chat-header">

    <img src="{{ asset('storage/images/icon-msg-dr.png') }}" 
     alt="Icône discussion médicale" 
     class="custom-icon"
     width="40" 
     height="40">
     <h1>Discussion médicale</h1>

    </div>

    <!-- Contenu Principal -->
    <div class="chat-main">
   <!-- Liste des Conversations (Doctors) -->
<div class="conversation-list">
    <div class="conversation-header">
    <h4>Listes des médecins </h4> 
    </div>
    @foreach($doctors as $doctor)
        @php
            $lastMessage = $lastMessages[$doctor->user_id] ?? null;
        @endphp
 
        <div class="conversation-item {{ $loop->first ? 'active' : '' }}" data-id="{{ $doctor->id }}" data-user-id="{{ $doctor->user_id }}" onclick="loadMessages('{{ $doctor->user_id }}')">
        <div class="doctor-avatar">
  <i class="fas fa-user-md fa-3x p-3 rounded-circle" 
     style="color: #00008B; background-color: #00008B20;"></i>
</div>

            <div class="doctor-info">
                <span class="name">{{ $doctor->name }}</span>
                <span class="specialty">{{ $doctor->specialty }}</span>
                @if($lastMessage)
                    <div class="last-message">
                        <p class="last-message-text">{{ $lastMessage['content'] }}</p>
                        <span class="last-message-time">{{ date('H:i', $lastMessage['timestamp']) }}</span>
                    </div>
                @else
                    <div class="no-message">No messages yet</div>
                @endif
            </div>
        </div>
    @endforeach
</div>

        <!-- Zone de Chat Principale -->
   <!-- Zone de Chat Principale -->
<div class="chat-area">
    <!-- Messages -->
    <div class="chat-messages" id="chat-messages">
        @if(!empty($messages))
            @foreach($messages as $message)
                <div class="message {{ $message['sender_id'] == auth()->id() ? 'sent' : 'received' }}" data-timestamp="{{ $message['timestamp'] }}">
                    <div class="message-content">
                        <div class="message-header">
                            <span class="sender">{{ $message['sender_name'] }}</span>
                            <span class="time">{{ date('H:i', $message['timestamp']) }}</span>
                            @if($message['sender_id'] == auth()->id())
                    <!-- Bouton de suppression -->
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
            <!-- Affichage pour les images -->
            <div class="message-image">
                <a href="{{ $message['file_url'] }}" data-lightbox="image-{{ $message['id'] }}" data-title="Chat Image">
                    <img src="{{ $message['file_url'] }}" alt="Chat Image">
                </a>
                <a href="{{ route('download.file', ['filename' => basename($message['file_url'])]) }}" download="{{ basename($message['file_url']) }}" class="download-link">
    <i class="fas fa-download"></i> 
</a>    
                
            </div>
        @else
            <!-- Affichage pour les fichiers non-image -->
            <div class="message-file">
                <i class="fas fa-file-alt"></i> <!-- Icône pour fichier -->
                <a href="{{ $message['file_url'] }}" target="_blank">Voir le fichier</a>
                <a href="{{ $message['file_url'] }}" download="{{ basename($message['file_url']) }}" class="download-link">
                    <i class="fas fa-download"></i> Télécharger
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
                <p>No messages yet</p>
            </div>
        @endif
    </div>

    <!-- Formulaire d'Envoi de Message -->
   <!-- Modifier le formulaire d'envoi -->
<div class="chat-input">
    <form action="{{ route('chat.send') }}" method="POST" enctype="multipart/form-data" id="chat-form">
        @csrf
        <input type="hidden" name="receiver_id" id="receiver_id" value="{{ $doctorUserId ?? '' }}" required>
        
        <div class="input-container">
            <label for="file-input" class="file-icon">
                <i class="fas fa-paperclip"></i>
            </label>
            <input type="file" name="file" id="file-input" style="display: none;">
            <input type="text" name="message" id="message-input" placeholder="Écrire un message..." required>
            <button type="submit" class="send-button">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
        
        <!-- Prévisualisation du fichier -->
    </form>
    <div id="output"></div>
</div>

</div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function listenForDeletedMessages(doctorUserId) {
    const userId = {{ auth()->user()->id }};
    const chatId = userId < doctorUserId ? `${userId}-${doctorUserId}` : `${doctorUserId}-${userId}`;

    const chatRef = database.ref(`chats/${chatId}/messages`);
    chatRef.on('child_removed', (snapshot) => {
        const messageId = snapshot.key;
        const messageElement = document.querySelector(`.message[data-id="${messageId}"]`);
        if (messageElement) {
            messageElement.remove();
        }
    });
}
    let lastMessageTimestamp = 0; // Timestamp du dernier message envoyé pour la vérification

    // Fonction pour vérifier les nouveaux messages
    function checkForNewMessages() {
        const chatMessages = document.querySelectorAll('.chat-messages .message');
        let newMessage = false;
        
        chatMessages.forEach(message => {
            const messageTimestamp = parseInt(message.getAttribute('data-timestamp'), 10); // Timestamp du message
            if (messageTimestamp > lastMessageTimestamp) {
                newMessage = true;
            }
        });

        // Si un nouveau message est trouvé, on l'ajoute en haut
        if (newMessage) {
            loadNewMessages();
        }
    }
    setTimeout(() => {
    location.reload(true); // Recharge la page depuis le serveur sans utiliser le cache
}, 8000); // Rafraîchit après 8 secondes

    // Fonction pour charger les nouveaux messages
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
                        const messageElement = document.createElement('div');
                        messageElement.classList.add('message', msg.sender_id === userId ? 'sent' : 'received');
                        messageElement.setAttribute('data-timestamp', msg.timestamp);
                        messageElement.innerHTML = `
                            <div class="message-content">
                                <div class="message-header">
                                    <span class="sender">${msg.sender_name}</span>
                                    <span class="time">${new Date(msg.timestamp * 1000).toLocaleTimeString()}</span>
                                </div>
                                <p class="text">${msg.content}</p>
                                ${msg.file_url ? `<a href="${msg.file_url}" target="_blank" class="file-link">View File</a>` : ''}
                            </div>
                        `;
                        chatMessagesContainer.appendChild(messageElement);
                    });
                } else {
                    // Afficher un message si aucun message n'est trouvé
                    chatMessagesContainer.innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-comment-slash"></i>
                            <p>No messages yet</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des messages:', error);
            });
    }

    // Fonction pour rediriger vers la conversation avec un médecin
    function loadMessages(doctorUserId) {
        const userId = {{ auth()->user()->id }}; // ID de l'utilisateur connecté
        window.location.href = `/chat/${userId}/${doctorUserId}`; // Redirection vers l'URL de chat
    }
    function updateNotificationBadge() {
    const badge = document.getElementById('notification-badge');
    if (badge) {
        const currentCount = parseInt(badge.textContent) || 0;
        badge.textContent = currentCount + 1;
        badge.style.display = 'inline-block';
    }
}
                                           // Fonction pour écouter les nouveaux messages
// Fonction pour écouter les nouveaux messages
// Fonction pour écouter les nouveaux messages
function listenForNewMessages(doctorUserId) {
    const userId = {{ auth()->user()->id }};
    const chatId = userId < doctorUserId ? `${userId}-${doctorUserId}` : `${doctorUserId}-${userId}`;

    const chatRef = database.ref(`chats/${chatId}/messages`);
    chatRef.on('child_added', (snapshot) => {
        const message = snapshot.val();
        if (message.receiver_id == userId) {
            appendMessage(message);
            showToast(`New message from ${message.sender_name}: ${message.content}`);
            updateNotificationBadge();
        }
    });
}
    
document.addEventListener('DOMContentLoaded', function() {
    fetch('/last-message')
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur réseau ou serveur');
            }
            // Vérifiez si la réponse est du JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Réponse non valide : attendu JSON, reçu HTML');
            }
            return response.json();
        })
        .then(data => {
            if (data && data.content) {
                showToast(`Dernier message de ${data.sender_name}: ${data.content}`);
            } else {
                showToast("Aucun message reçu pour le moment.");
            }
        })
        .catch(error => {
            console.error("Error fetching last message:", error);
            showToast("Erreur lors de la récupération du dernier message.");
        });
});   // Logique de correction de texte
    document.getElementById('message-input').addEventListener('input', function() {
        const input = this.value;
        const words = input.split(' ');
        let correctedText = '';

        words.forEach(function(word) {
            // Exemple de détection simple : surligner les mots qui n'ont pas de "e" (ou selon une règle plus précise)
            if (word.length > 3 && !/[a-zA-Z]*e[a-zA-Z]*/.test(word)) {
                correctedText += `<span class="incorrect">${word}</span> `;
            } else {
                correctedText += word + ' ';
            }
        });

        document.getElementById('output').innerHTML = correctedText;
    });

    // Vérification des nouveaux messages toutes les 5 secondes
    setInterval(checkForNewMessages, 5000);


    function deleteMessage(chatId, messageId, buttonElement) {
    // URL Firebase pour récupérer les messages du chat
    const messagesUrl = `https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app/chats/${chatId}/messages.json`;

    fetch(messagesUrl)
        .then(response => response.json())
        .then(messages => {
            if (!messages) {
                throw new Error("Aucun message trouvé dans Firebase.");
            }

            // Trouver l'ID Firebase correspondant au messageId
            let firebaseMessageId = null;
            for (const key in messages) {
                if (messages[key].id === messageId) {
                    firebaseMessageId = key;
                    break;
                }
            }

            if (firebaseMessageId) {
                // URL Firebase pour supprimer le message
                const deleteUrl = `https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app/chats/${chatId}/messages/${firebaseMessageId}.json`;

                return fetch(deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                });
            } else {
                throw new Error("Message non trouvé dans Firebase.");
            }
        })
        .then(response => {
            if (response.ok) {
                console.log("Message supprimé avec succès.");

                // Supprimer le message de l'interface utilisateur
                const messageElement = buttonElement.closest('.message');
                if (messageElement) {
                    messageElement.remove();
                }
            } else {
                console.error("Erreur lors de la suppression :", response.statusText);
            }
        })
        .catch(error => {
            console.error("Erreur :", error);
        });
}
function listenForDeletedMessages(chatId) {
    const chatRef = firebase.database().ref(`chats/${chatId}/messages`);

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
 function toggleNotifications() {
    const badge = document.getElementById('notification-badge');
    if (badge) {
        badge.textContent = '0'; // Réinitialiser le compteur
        badge.style.display = 'none'; // Masquer le badge
    }

    // Optionnel : Ouvrir une liste de notifications ou marquer les messages comme lus
    fetch('/mark-notifications-as-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
    })
    .then(response => response.json())
    .then(data => {
        console.log('Notifications marquées comme lues', data);
    })
    .catch(error => console.error('Erreur:', error));
}
  
</script>
<script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-database.js"></script>
<script>
    // Gestion de la prévisualisation des fichiers
    document.getElementById('file-input').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const previewContainer = document.getElementById('file-preview-container');
        previewContainer.innerHTML = '';

        if (file) {
            const reader = new FileReader();
            const fileType = file.type.split('/')[0];
            
            if (fileType === 'image') {
                reader.onload = (e) => {
                    previewContainer.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        <div class="file-info">
                            <div>${file.name}</div>
                            <small>${(file.size/1024).toFixed(2)} KB</small>
                        </div>
                        <span class="remove-file" onclick="clearFileInput()">&times;</span>
                    `;
                };
                reader.readAsDataURL(file);
            } else {
                previewContainer.innerHTML = `
                    <i class="fas fa-file-alt fa-2x"></i>
                    <div class="file-info">
                        <div>${file.name}</div>
                        <small>${(file.size/1024).toFixed(2)} KB</small>
                    </div>
                    <span class="remove-file" onclick="clearFileInput()">&times;</span>
                `;
            }
        }
    });

    function clearFileInput() {
        document.getElementById('file-input').value = '';
        document.getElementById('file-preview-container').innerHTML = '';
    }
// Remplacer le gestionnaire de soumission existant
document.getElementById('chat-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        if (response.ok) {
            this.reset();
            document.getElementById('file-preview-container').innerHTML = '';
            loadMessages(document.getElementById('receiver_id').value);
        } else {
            console.error('Erreur lors de l\'envoi');
        }
    } catch (error) {
        console.error('Erreur réseau:', error);
    }
});
</script>
<script>
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

    // Fonction pour écouter les nouveaux messages
    function listenForNewMessages(doctorUserId) {
        const userId = {{ auth()->user()->id }};
        const chatId = userId < doctorUserId ? `${userId}-${doctorUserId}` : `${doctorUserId}-${userId}`;

        const chatRef = database.ref(`chats/${chatId}/messages`);

        // Écouter les nouveaux messages
        chatRef.on('child_added', (snapshot) => {
            const message = snapshot.val();

            // Vérifier si le message est destiné à l'utilisateur actuel
            if (message.receiver_id == userId) {
                // Rafraîchir la page pour afficher le nouveau message, seulement si c'est le récepteur
                if (window.location.href.includes(doctorUserId)) {
                    location.reload(); // Rafraîchir la page pour le récepteur
                }
            }
        });
    }

    // Appeler la fonction pour écouter les nouveaux messages
    const doctorUserId = "{{ $doctorUserId ?? '' }}"; // Récupérer l'ID du médecin actuel
    if (doctorUserId) {
        listenForNewMessages(doctorUserId);
    }
</script>

@endsection
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
                                            .chat-input input[type="file"] {
                                                    margin-right: 10px;
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
    margin: 15px 0;
    padding: 15px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
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




                                            .input-container {
                                                position: relative;
                                                flex: 1;
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
                                                .chat-input input[type="text"] {
                                                    flex: 1;
                                                    width: 450px;
                                                    padding: 10px;
                                                    border: 1px solid #ddd;
                                                    border-radius: 5px;
                                                    margin-right: 10px;
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
    overflow-y: auto;
    padding: 20px;
    height: calc(100vh - 120px); /* Hauteur totale - header - input */
}

.chat-input {
    position: sticky;
    bottom: 0;
    background: white;
    padding: 15px;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
    z-index: 1000;
}

/* Supprimer les marges existantes */
.chat-main {
    margin-top: 0;
    margin-bottom: 0;
}

/* Ajuster le padding des messages pour l'espace vertical */
.chat-messages {
    padding-bottom: 10px;
    padding-top: 10px;
}

/* Assurer que les éléments sticky restent collés */
.conversation-list {
    position: sticky;
    top: 60px;
    height: calc(100vh - 60px);
}                                      .chat-input input[type="file"] {
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
                                            </style>

@extends('layouts.app')

@section('content')
    <div class="chat-container">
        <!-- Sidebar -->
        <div class="chat-sidebar">
            <div class="chat-header">
                <h4>Chat</h4>
                <div class="chat-settings">
                    <a href="#" class="settings-icon"><i class="fas fa-cog"></i></a>
                </div>
            </div>

            <div class="search-container">
                <div class="search-box">
                    <input type="text" placeholder="Search ..." id="chat-search">
                    <button class="search-btn"><i class="fas fa-search"></i></button>
                </div>
            </div>

            <div class="avatar-row">
                @foreach($users->take(8) as $quickUser)
                    <a href="{{ route('doctor.messages.show', $quickUser->id) }}"
                        class="avatar-circle {{ $quickUser->id == $user->id ? 'active' : '' }}">
                        <img src="{{ $quickUser->getFirstMediaUrl('avatar', 'icon') ?: asset('images/default-avatar.png') }}"
                            alt="{{ $quickUser->name }}">
                        <span class="status-dot user-{{ $quickUser->id }}"></span>
                    </a>
                @endforeach
            </div>

            <div class="chat-tabs">
                <button class="tab-btn active">Chat</button>
                <button class="tab-btn">Group</button>
                <button class="tab-btn">Contact</button>
            </div>

            <div class="chat-list">
                @foreach($users as $chatUser)
                    <div class="chat-item user-{{ $chatUser->id }} {{ $chatUser->id == $user->id ? 'active' : '' }}">
                        <a href="{{ route('doctor.messages.show', $chatUser->id) }}" class="chat-link">
                            <div class="avatar">
                                <img src="{{ $chatUser->getFirstMediaUrl('avatar', 'icon') ?: asset('images/default-avatar.png') }}"
                                    alt="{{ $chatUser->name }}">
                                <span class="status-dot"></span>
                            </div>
                            <div class="chat-info">
                                <div class="chat-name">{{ $chatUser->name }}</div>
                                <div class="chat-message">
                                    <span class="message-preview">Loading...</span>
                                </div>
                            </div>
                            <div class="chat-meta">
                                <div class="chat-time"></div>
                                <div class="unread-badge" style="display: none;">0</div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Main Chat Content -->
        <div class="chat-content">
            <!-- Add status div for Firebase connection status -->
            <div id="status" style="background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px; display: none;">
                Checking Firebase connection...
            </div>

            <div class="conversation-header">
                <div class="conversation-user">
                    <div class="avatar">
                        <img src="{{ $user->getFirstMediaUrl('avatar', 'icon') ?: asset('images/default-avatar.png') }}"
                            alt="{{ $user->name }}">
                        <span class="status-dot user-{{ $user->id }}"></span>
                    </div>
                    <div class="user-info">
                        <div class="user-name">{{ $user->name }}</div>
                        <div class="user-status">offline</div>
                    </div>
                </div>
                <div class="conversation-actions">
                    <button class="action-btn"><i class="fas fa-video"></i></button>
                    <button class="action-btn"><i class="fas fa-phone-alt"></i></button>
                    <button class="action-btn"><i class="fas fa-ellipsis-v"></i></button>
                </div>
            </div>

            <div class="conversation-body" id="messages-container">
                <!-- Messages will be loaded here -->
                <div class="no-messages">No messages yet. Say hello!</div>
            </div>

            <div class="conversation-footer">
                <button class="action-btn emoji-btn"><i class="far fa-smile"></i></button>
                <div class="message-input-container">
                    <input type="text" id="message-input" placeholder="Enter your message">
                </div>
                <button class="action-btn attach-btn"><i class="fas fa-paperclip"></i></button>
                <button class="send-btn" id="send-message-btn"><i class="fas fa-paper-plane"></i></button>
            </div>

            <!-- File upload modal -->
            <input type="file" id="file-upload" style="display:none;">
            <div id="upload-modal" class="upload-modal" style="display:none;">
                <div class="upload-modal-content">
                    <div class="upload-modal-header">
                        <h4>Upload File</h4>
                        <span class="upload-close">&times;</span>
                    </div>
                    <div class="upload-modal-body">
                        <div class="upload-options">
                            <button class="upload-option" id="upload-image">
                                <i class="fas fa-image"></i>
                                <span>Téléverser une image</span>
                            </button>
                            <button class="upload-option" id="upload-file">
                                <i class="fas fa-file"></i>
                                <span>Téléverser un fichier</span>
                            </button>
                        </div>
                        <div class="upload-preview" style="display:none;">
                            <div class="preview-container">
                                <img id="image-preview" style="display:none; max-width: 100%; max-height: 200px;">
                                <div id="file-preview" style="display:none;">
                                    <i class="fas fa-file"></i>
                                    <span id="file-name"></span>
                                </div>
                            </div>
                            <button id="cancel-upload" class="btn btn-sm btn-secondary">Cancel</button>
                            <button id="send-file" class="btn btn-sm btn-primary">Send</button>
                        </div>
                        <div class="upload-progress" style="display:none;">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                            </div>
                            <div class="progress-text">Uploading: 0%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .upload-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .upload-modal-content {
            background-color: white;
            border-radius: 8px;
            width: 350px;
            max-width: 90%;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .upload-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .upload-modal-header h4 {
            margin: 0;
            font-size: 18px;
        }

        .upload-close {
            font-size: 24px;
            cursor: pointer;
        }

        .upload-modal-body {
            padding: 15px;
        }

        .upload-options {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }

        .upload-option {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            background: none;
            transition: all 0.2s;
        }

        .upload-option:hover {
            background-color: #f8f9fa;
        }

        .upload-option i {
            font-size: 24px;
            margin-bottom: 10px;
            color: #4a6cf7;
        }

        .upload-preview {
            text-align: center;
            margin: 15px 0;
        }

        .preview-container {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            min-height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #file-preview {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        #file-preview i {
            font-size: 32px;
            margin-bottom: 10px;
            color: #6c757d;
        }

        .upload-progress {
            margin-top: 15px;
        }

        .progress {
            height: 10px;
            border-radius: 5px;
            background-color: #e9ecef;
            margin-bottom: 5px;
            overflow: hidden;
        }

        .progress-bar {
            background-color: #4a6cf7;
            height: 100%;
        }

        .progress-text {
            font-size: 14px;
            color: #6c757d;
        }

        .chat-image {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            margin-bottom: 5px;
        }

        .file-attachment {
            background-color: #f8f9fa;
            padding: 8px 12px;
            border-radius: 8px;
            margin-bottom: 5px;
            display: inline-block;
        }

        .file-attachment a {
            display: flex;
            align-items: center;
            color: #4a6cf7;
            text-decoration: none;
        }

        .file-attachment i {
            margin-right: 8px;
        }

        /* Button styles */
        .btn {
            display: inline-block;
            font-weight: 400;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.25rem;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .btn-primary {
            color: #fff;
            background-color: #4a6cf7;
            border-color: #4a6cf7;
        }

        .btn-secondary {
            color: #fff;
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.2rem;
        }
    </style>
@endpush

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Firebase App (the core Firebase SDK) must be listed first -->
    <script src="https://www.gstatic.com/firebasejs/8.6.8/firebase-app.js"></script>
    <!-- Add Firebase products that you want to use -->
    <script src="https://www.gstatic.com/firebasejs/8.6.8/firebase-firestore.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.6.8/firebase-storage.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            console.log("DOM loaded");

            // Make sure jQuery is loaded
            if (typeof $ === 'undefined') {
                console.error("jQuery is not loaded!");
                return;
            }

            // Show status element
            $('#status').show().text("Connecting to Firebase...");

            // Load Firebase scripts dynamically to ensure proper loading order
            loadScript('https://www.gstatic.com/firebasejs/8.6.8/firebase-app.js')
                .then(() => loadScript('https://www.gstatic.com/firebasejs/8.6.8/firebase-firestore.js'))
                .then(() => loadScript('https://www.gstatic.com/firebasejs/8.6.8/firebase-storage.js'))
                .then(() => {
                    initializeFirebase();
                })
                .catch(error => {
                    console.error("Error loading Firebase scripts:", error);
                    $('#status').html("<strong>⚠️ Error loading Firebase:</strong> " + error);
                    $('#status').css('background', '#f8d7da');
                });

            // Function to load scripts dynamically
            function loadScript(src) {
                return new Promise((resolve, reject) => {
                    const script = document.createElement('script');
                    script.src = src;
                    script.onload = resolve;
                    script.onerror = reject;
                    document.head.appendChild(script);
                });
            }

            function initializeFirebase() {
                try {
                    // Check Firebase availability after loading scripts
                    console.log("Checking Firebase after loading scripts:");
                    console.log("Firebase availability:", typeof firebase);
                    console.log("Firebase app availability:", typeof firebase.initializeApp);
                    console.log("Firebase firestore availability:", typeof firebase.firestore);
                    console.log("Firebase storage availability:", typeof firebase.storage);

                    const firebaseConfig = {
                        apiKey: "AIzaSyCONylt3t8MDw_02k5H9ceXTEmdtxmQtu8",
                        authDomain: "wic-doctor-b83e0.firebaseapp.com",
                        projectId: "wic-doctor-b83e0",
                        //storageBucket: "wic-doctor-b83e0.appspot.com",
                        storageBucket: "wic-doctor-b83e0.firebasestorage.app", // Correct bucket name
                        messagingSenderId: "599835198131",
                        appId: "1:599835198131:web:3ee81bdd9f4cff0fa21f22"
                    };

                    // Initialize Firebase
                    if (!firebase.apps.length) {
                        firebase.initializeApp(firebaseConfig);
                    }

                    console.log("Firebase initialized successfully");
                    $('#status').html("<strong>✅ Connected to Firebase!</strong> Messages will sync across devices.");
                    $('#status').css('background', '#d4edda');

                    // Initialize chat
                    initChat();

                    // Hide status after 3 seconds
                    setTimeout(() => {
                        $('#status').fadeOut();
                    }, 3000);
                } catch (e) {
                    console.error("Firebase initialization error:", e);
                    $('#status').html("<strong>⚠️ Firebase initialization error:</strong> " + e.message);
                    $('#status').css('background', '#f8d7da');
                }
            }
        });


        function initChat() {
            try {
                console.log("Initializing chat...");
                const currentUserId = "{{ $currentUser->id }}";
                const receiverId = "{{ $user->id }}";

                // Create the direct message path (senderId-receiverId)
                const directRoomId = `${currentUserId}-${receiverId}`;
                // Also try the reversed path (receiverId-senderId) for receiving messages
                const reverseRoomId = `${receiverId}-${currentUserId}`;

                console.log("Direct Room ID:", directRoomId);
                console.log("Reverse Room ID:", reverseRoomId);

                // Access Firestore
                const db = firebase.firestore();

                // Load messages from both directions
                const messagesContainer = $('#messages-container');
                let allMessages = [];

                // Listen to messages sent by current user
                db.collection('messages').doc(directRoomId).collection('chats')
                    .orderBy('time')
                    .onSnapshot((snapshot) => {
                        snapshot.docChanges().forEach((change) => {
                            if (change.type === 'added') {
                                const message = change.doc.data();
                                processNewMessage(message, true); // outgoing
                            }
                        });
                    });

                // Listen to messages sent to current user
                db.collection('messages').doc(reverseRoomId).collection('chats')
                    .orderBy('time')
                    .onSnapshot((snapshot) => {
                        snapshot.docChanges().forEach((change) => {
                            if (change.type === 'added') {
                                const message = change.doc.data();
                                processNewMessage(message, false); // incoming

                                // Mark message as read when viewed
                                db.collection('messages').doc(reverseRoomId).collection('chats')
                                    .doc(change.doc.id).update({ read: true })
                                    .catch(err => console.error("Error marking message as read:", err));
                            }
                        });
                    });

                function processNewMessage(message, isOutgoing) {
                    // If this is the first message, clear the 'no messages' placeholder
                    if (messagesContainer.find('.no-messages').length > 0) {
                        messagesContainer.empty();
                    }

                    const messageDate = message.time ? new Date(parseInt(message.time)) : new Date();

                    // Check if we need to add a date divider
                    const lastDateDivider = messagesContainer.find('.date-divider:last');
                    const lastMessageTime = lastDateDivider.length > 0 ?
                        new Date(lastDateDivider.data('date')) : null;

                    if (!lastMessageTime || !isSameDay(lastMessageTime, messageDate)) {
                        const dateHeader = `<div class="date-divider" data-date="${messageDate.getTime()}">${formatDateHeader(messageDate)}</div>`;
                        messagesContainer.append(dateHeader);
                    }

                    // Display the message
                    const timestamp = messageDate;
                    const timeStr = timestamp.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

                    let messageContent = '';

                    // Handle file attachments if present
                    if (message.fileUrl && message.fileUrl.trim() !== '') {
                        const fileUrl = message.fileUrl;

                        // Image detection - only treat it as an image if the URL has specific image extensions
                        const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        const fileExtension = fileUrl.split('.').pop().toLowerCase().split('?')[0];

                        if (imageExtensions.includes(fileExtension)) {
                            // It's definitely an image
                            messageContent = `<img src="${fileUrl}" alt="Image" class="chat-image">`;
                        } else {
                            // It's a file - use the file icon
                            const fileName = message.text || fileUrl.split('/').pop().split('?')[0];
                            messageContent = `<div class="file-attachment">
                                        <a href="${fileUrl}" target="_blank" download="${fileName}">
                                            <img src="/images/file.png" alt="File" class="file-icon" style="width: 24px; height: 24px; margin-right: 8px;"> 
                                            ${fileName}
                                        </a>
                                    </div>`;
                        }
                    } else {
                        // Regular text message
                        messageContent = message.text;
                    }

                    const messageElement = `
                                <div class="message-row ${isOutgoing ? 'outgoing' : 'incoming'}">
                                    <div class="message-bubble">
                                        <div class="message-text">${messageContent}</div>
                                        <div class="message-time">${timeStr}</div>
                                    </div>
                                </div>
                            `;

                    messagesContainer.append(messageElement);
                    scrollToBottom();
                }

                function insertMessage(message, isOutgoing, messageDate, beforeElement) {
                    // Check if we need to add a date divider
                    const messageTimestamp = messageDate.getTime();

                    // Find the appropriate position for the date divider
                    let previousElement = beforeElement ? beforeElement.prev() : messagesContainer.children().last();
                    let nextElement = beforeElement || null;

                    // Check if we need to add a date header
                    let needsDateHeader = true;
                    let previousDateHeader = null;
                    let previousMessageDate = null;

                    // Look backwards to find the previous date header or message
                    while (previousElement.length > 0) {
                        if (previousElement.hasClass('date-divider')) {
                            previousDateHeader = previousElement;
                            previousMessageDate = new Date(parseInt(previousElement.data('date')));
                            break;
                        } else if (previousElement.hasClass('message-row')) {
                            previousMessageDate = new Date(parseInt(previousElement.attr('data-timestamp')));
                            break;
                        }
                        previousElement = previousElement.prev();
                    }

                    // If we found a previous message/header with the same date, no need for a new header
                    if (previousMessageDate && isSameDay(previousMessageDate, messageDate)) {
                        needsDateHeader = false;
                    }

                    // If we need a date header and there isn't one for this date already
                    if (needsDateHeader) {
                        const dateHeader = $(`<div class="date-divider" data-date="${messageTimestamp}">${formatDateHeader(messageDate)}</div>`);
                        if (beforeElement) {
                            beforeElement.before(dateHeader);
                        } else {
                            messagesContainer.append(dateHeader);
                        }
                    }

                    // Format the message content
                    const timeStr = messageDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    let messageContent = message.text;

                    // Handle file attachments if present
                    if (message.fileUrl && message.fileUrl.trim() !== '') {
                        const fileUrl = message.fileUrl;
                        const fileExtension = fileUrl.split('.').pop().toLowerCase();
                        const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                        if (imageExtensions.includes(fileExtension) || fileUrl.includes('image')) {
                            // It's an image - just show the image without text below it
                            messageContent = `<img src="${fileUrl}" alt="Image" class="chat-image">`;
                        } else {
                            // It's a file
                            const fileName = message.text || fileUrl.split('/').pop();
                            messageContent = `<div class="file-attachment">
                                                                <a href="${fileUrl}" target="_blank" download="${fileName}">
                                                                    <i class="fas fa-file"></i> ${fileName}
                                                                </a>
                                                            </div>`;
                        }
                    }

                    // Create the message element
                    const messageElement = $(`
                                                        <div class="message-row ${isOutgoing ? 'outgoing' : 'incoming'}" data-id="${message.id}" data-timestamp="${messageTimestamp}">
                                                            <div class="message-bubble">
                                                                <div class="message-text">${messageContent}</div>
                                                                <div class="message-time">${timeStr}</div>
                                                            </div>
                                                        </div>
                                                    `);

                    // Insert the message at the correct position
                    if (beforeElement) {
                        beforeElement.before(messageElement);
                    } else {
                        messagesContainer.append(messageElement);
                    }
                }

                // Track presence for receiver
                db.collection('presence').doc(receiverId)
                    .onSnapshot((doc) => {
                        if (doc.exists) {
                            const status = doc.data();
                            updateActiveUserStatus(receiverId, status);
                        }
                    });

                // Update current user's presence
                const presenceRef = db.collection('presence').doc(currentUserId);

                presenceRef.set({
                    online: true,
                    lastSeen: firebase.firestore.FieldValue.serverTimestamp()
                });

                window.addEventListener('beforeunload', () => {
                    presenceRef.set({
                        online: false,
                        lastSeen: firebase.firestore.FieldValue.serverTimestamp()
                    });
                });

                // Track last messages for other users
                @foreach($users as $chatUser)
                    @if($chatUser->id != $user->id)
                        // Use a unique variable name for each iteration to avoid redeclaration
                        const chatUserId{{ $chatUser->id }} = "{{ $chatUser->id }}";
                        const userDirectPath{{ $chatUser->id }} = `${currentUserId}-${chatUserId{{ $chatUser->id }}}`;
                        const userReversePath{{ $chatUser->id }} = `${chatUserId{{ $chatUser->id }}}-${currentUserId}`;

                        // Check messages sent by current user
                        db.collection('messages').doc(userDirectPath{{ $chatUser->id }}).collection('chats')
                            .orderBy('time', 'desc')
                            .limit(1)
                            .onSnapshot((snapshot1) => {
                                if (!snapshot1.empty) {
                                    updateChatPreview(snapshot1.docs[0].data(), chatUserId{{ $chatUser->id }});
                                }

                                // Also check messages sent to current user
                                db.collection('messages').doc(userReversePath{{ $chatUser->id }}).collection('chats')
                                    .orderBy('time', 'desc')
                                    .limit(1)
                                    .get()
                                    .then((snapshot2) => {
                                        if (!snapshot2.empty) {
                                            // If we have messages from both directions, compare timestamps
                                            if (!snapshot1.empty) {
                                                const msg1 = snapshot1.docs[0].data();
                                                const msg2 = snapshot2.docs[0].data();

                                                // Show the most recent message
                                                if (parseInt(msg2.time) > parseInt(msg1.time)) {
                                                    updateChatPreview(msg2, chatUserId{{ $chatUser->id }});
                                                }
                                            } else {
                                                // Just show the message from other user
                                                updateChatPreview(snapshot2.docs[0].data(), chatUserId{{ $chatUser->id }});
                                            }
                                        }
                                    });
                            });
                    @endif
                @endforeach

                function updateChatPreview(message, userId) {
                    const time = message.time ? new Date(parseInt(message.time)) : new Date();
                    const timeStr = formatTime(time);
                    const preview = message.text.substring(0, 30) + (message.text.length > 30 ? '...' : '');
                    $(`.user-${userId} .message-preview`).text(preview);
                    $(`.user-${userId} .chat-time`).text(timeStr);
                }

                // Track unread messages
                db.collection('unread').doc(currentUserId).collection('senders')
                    .onSnapshot((snapshot) => {
                        snapshot.forEach((doc) => {
                            const senderId = doc.id;
                            const count = doc.data().count || 0;

                            if (count > 0) {
                                $(`.user-${senderId} .unread-badge`).text(count).show();
                            } else {
                                $(`.user-${senderId} .unread-badge`).hide();
                            }
                        });
                    });

                // Set up enter key handler
                $('#message-input').off('keypress').on('keypress', function (e) {
                    if (e.which === 13) {
                        sendMessage();
                        return false;
                    }
                });

                // Listen for typing
                let typingTimeout = null;
                const typingRoomId = [currentUserId, receiverId].sort().join('_');

                $('#message-input').on('input', function () {
                    const input = $(this).val().trim();

                    if (input.length > 0) {
                        db.collection('typing').doc(typingRoomId).set({
                            [`${currentUserId}`]: true
                        }, { merge: true });

                        clearTimeout(typingTimeout);
                        typingTimeout = setTimeout(() => {
                            db.collection('typing').doc(typingRoomId).update({
                                [`${currentUserId}`]: false
                            }).catch(() => { });
                        }, 3000);
                    } else {
                        db.collection('typing').doc(typingRoomId).update({
                            [`${currentUserId}`]: false
                        }).catch(() => { });
                    }
                });

                // Listen for other person typing
                db.collection('typing').doc(typingRoomId).onSnapshot((doc) => {
                    if (doc.exists && doc.data()[receiverId]) {
                        $('.user-status').text('typing...');
                    } else {
                        updateActiveUserStatus(receiverId, { online: $('.user-{{ $user->id }} .status-dot').hasClass('online') });
                    }
                });

                // Mark messages as read
                markMessagesAsRead(currentUserId, receiverId);

                // Search functionality
                $('#chat-search').on('keyup', function () {
                    const value = $(this).val().toLowerCase();
                    $('.chat-item').filter(function () {
                        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                    });
                });

                $('#send-message-btn').off('click').on('click', function () {
                    console.log("Send button clicked (jQuery)");
                    sendMessage();
                });

                // Initialize file upload functionality
                initFileUpload();

                console.log("Chat initialized successfully");
                $('.user-status').text('Online (Firebase Mode)');
            } catch (e) {
                console.error("Error in initChat:", e);
            }
        }

        function sendMessage() {
            try {
                console.log("Sending message function called");
                const messageInput = $('#message-input');
                const messageText = messageInput.val().trim();
                console.log("Sending message:", messageText);

                if (!messageText) {
                    console.log("Empty message, not sending");
                    return;
                }

                const currentUserId = "{{ $currentUser->id }}";
                const receiverId = "{{ $user->id }}";
                const roomId = `${currentUserId}-${receiverId}`; // Format: senderId-receiverId
                const timestamp = Date.now();

                // Get Firestore
                const db = firebase.firestore();

                // Show sending indicator
                const tempId = 'msg-' + timestamp;
                const tempMsg = `
                                                                                                    <div id="${tempId}" class="message-row outgoing">
                                                                                                        <div class="message-bubble">
                                                                                                            <div class="message-text">${messageText}</div>
                                                                                                            <div class="message-time">Sending...</div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                `;
                $('#messages-container').append(tempMsg);
                scrollToBottom();

                // Get sender and receiver data
                const senderData = {
                    auth: true,
                    device_token: "{{ $currentUser->device_token ?? '' }}",
                    id: currentUserId,
                    imageUrl: "{{ $currentUser->profile_image ?? '' }}",
                    name: "{{ $currentUser->name }}",
                    phone_number: "{{ $currentUser->phone_number ?? '' }}"
                };

                const receiverData = {
                    auth: false,
                    device_token: "{{ $user->device_token ?? '' }}",
                    id: receiverId,
                    imageUrl: "{{ $user->profile_image ?? '' }}",
                    name: "{{ $user->name }}",
                    phone_number: "{{ $user->phone_number ?? '' }}"
                };

                // Create the message data with the format you specified
                const messageData = {
                    id: timestamp.toString(),
                    sender: senderData,
                    receiver: receiverData,
                    text: messageText,
                    time: timestamp,
                    fileUrl: "" // Empty for text messages
                };

                // Save to Firestore in the path: /messages/[senderID]-[receiverID]/chats/[messageID]
                db.collection('messages').doc(roomId).collection('chats').add(messageData)
                    .then((docRef) => {
                        console.log("Message saved successfully", docRef.id);

                        // Update unread counter
                        db.collection('unread').doc(receiverId).collection('senders').doc(currentUserId).set({
                            count: firebase.firestore.FieldValue.increment(1)
                        }, { merge: true });

                        // Remove temp message (it will be replaced by the real one from the snapshot)
                        $('#' + tempId).remove();
                    })
                    .catch(error => {
                        console.error("Error sending message:", error);
                        $('#' + tempId + ' .message-time').text('Failed to send');
                    });

                // Clear input and typing indicator
                messageInput.val('');
                const typingRoomId = [currentUserId, receiverId].sort().join('_');
                db.collection('typing').doc(typingRoomId).update({
                    [`${currentUserId}`]: false
                }).catch(() => { });
            } catch (e) {
                console.error("Error in sendMessage:", e);
            }
        }

        function scrollToBottom() {
            const container = document.getElementById('messages-container');
            container.scrollTop = container.scrollHeight;
        }

        function markMessagesAsRead(userId, senderId) {
            const db = firebase.firestore();
            const roomId = `${senderId}-${userId}`; // Format: senderId-receiverId (messages FROM sender TO current user)

            // Mark messages as read
            db.collection('messages').doc(roomId).collection('chats')
                .where('read', '==', false)
                .get()
                .then((querySnapshot) => {
                    const batch = db.batch();
                    querySnapshot.forEach((doc) => {
                        batch.update(doc.ref, { read: true });
                    });
                    return batch.commit();
                })
                .then(() => {
                    console.log("Marked all messages as read");

                    // Reset unread counter
                    return db.collection('unread').doc(userId).collection('senders').doc(senderId).set({
                        count: 0
                    }, { merge: true });
                })
                .catch((error) => {
                    console.error("Error marking messages as read:", error);
                });
        }
        function updateUserStatus(userId, status) {
            const statusDot = $(`.user-${userId} .status-dot`);
            if (status.online) {
                statusDot.addClass('online');
            } else {
                statusDot.removeClass('online');
            }
        }

        function updateActiveUserStatus(userId, status) {
            updateUserStatus(userId, status);

            if (status.online) {
                $('.user-status').text('Online');
            } else {
                const lastSeen = status.lastSeen ? new Date(status.lastSeen.toDate()) : new Date();
                $('.user-status').text(`Last seen ${formatLastSeen(lastSeen)}`);
            }
        }

        function formatLastSeen(date) {
            const now = new Date();
            const diff = Math.floor((now - date) / 1000); // seconds

            if (diff < 60) return 'just now';
            if (diff < 3600) return `${Math.floor(diff / 60)} min ago`;
            if (diff < 86400) return `${Math.floor(diff / 3600)} h ago`;
            return formatDate(date);
        }

        function formatTime(date) {
            const now = new Date();
            const yesterday = new Date(now);
            yesterday.setDate(yesterday.getDate() - 1);

            if (date.toDateString() === now.toDateString()) {
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            } else if (date.toDateString() === yesterday.toDateString()) {
                return 'Yesterday';
            } else {
                return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
            }
        }

        function formatDate(date) {
            const now = new Date();
            const yesterday = new Date(now);
            yesterday.setDate(yesterday.getDate() - 1);

            if (date.toDateString() === now.toDateString()) {
                return 'today';
            } else if (date.toDateString() === yesterday.toDateString()) {
                return 'yesterday';
            } else {
                return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
            }
        }

        function formatDateHeader(date) {
            const now = new Date();
            const yesterday = new Date(now);
            yesterday.setDate(yesterday.getDate() - 1);

            if (date.toDateString() === now.toDateString()) {
                return 'Today';
            } else if (date.toDateString() === yesterday.toDateString()) {
                return 'Yesterday';
            } else {
                return date.toLocaleDateString([], { weekday: 'long', month: 'long', day: 'numeric' });
            }
        }

        function isSameDay(date1, date2) {
            return date1.getDate() === date2.getDate() &&
                date1.getMonth() === date2.getMonth() &&
                date1.getFullYear() === date2.getFullYear();
        }

        function initFileUpload() {
            // Check if Firebase Storage is loaded
            if (typeof firebase === 'undefined' || typeof firebase.storage === 'undefined') {
                console.error("Firebase Storage is not loaded!");
                return;
            }

            // Get Firebase Storage reference
            const storage = firebase.storage();

            const attachBtn = document.querySelector('.attach-btn');
            const fileUploadInput = document.getElementById('file-upload');
            const uploadModal = document.getElementById('upload-modal');
            const uploadClose = document.querySelector('.upload-close');
            const uploadImageBtn = document.getElementById('upload-image');
            const uploadFileBtn = document.getElementById('upload-file');
            const imagePreview = document.getElementById('image-preview');
            const filePreview = document.getElementById('file-preview');
            const fileName = document.getElementById('file-name');
            const cancelUploadBtn = document.getElementById('cancel-upload');
            const sendFileBtn = document.getElementById('send-file');
            const uploadPreview = document.querySelector('.upload-preview');
            const uploadOptions = document.querySelector('.upload-options');
            const uploadProgress = document.querySelector('.upload-progress');
            const progressBar = document.querySelector('.progress-bar');
            const progressText = document.querySelector('.progress-text');

            let selectedFile = null;
            let fileType = null;

            // Attach button click handler
            attachBtn.addEventListener('click', function (e) {
                e.preventDefault();
                uploadModal.style.display = 'flex';
            });

            // Close modal when clicking the X
            uploadClose.addEventListener('click', function () {
                uploadModal.style.display = 'none';
                resetUploadUI();
            });

            // Close modal when clicking outside
            uploadModal.addEventListener('click', function (e) {
                if (e.target === uploadModal) {
                    uploadModal.style.display = 'none';
                    resetUploadUI();
                }
            });

            // Handle image upload option click
            uploadImageBtn.addEventListener('click', function () {
                fileType = 'image';
                fileUploadInput.accept = 'image/*';
                fileUploadInput.click();
            });

            // Handle file upload option click
            uploadFileBtn.addEventListener('click', function () {
                fileType = 'file';
                fileUploadInput.accept = '.pdf,.doc,.docx,.txt,.xls,.xlsx';
                fileUploadInput.click();
            });

            // Handle file selection
            fileUploadInput.addEventListener('change', function (e) {
                if (e.target.files.length > 0) {
                    selectedFile = e.target.files[0];

                    // Show preview based on file type
                    uploadOptions.style.display = 'none';
                    uploadPreview.style.display = 'block';

                    if (fileType === 'image' && selectedFile.type.startsWith('image/')) {
                        // Preview image
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            imagePreview.src = e.target.result;
                            imagePreview.style.display = 'block';
                            filePreview.style.display = 'none';
                        };
                        reader.readAsDataURL(selectedFile);
                    } else {
                        // Preview file
                        imagePreview.style.display = 'none';
                        filePreview.style.display = 'flex';
                        fileName.textContent = selectedFile.name;
                    }
                }
            });

            // Handle cancel button
            cancelUploadBtn.addEventListener('click', function () {
                resetUploadUI();
            });

            // Handle send button
            sendFileBtn.addEventListener('click', function () {
                if (selectedFile) {
                    uploadFile(selectedFile);
                }
            });

            // Reset the upload UI
            function resetUploadUI() {
                selectedFile = null;
                fileType = null;
                uploadOptions.style.display = 'flex';
                uploadPreview.style.display = 'none';
                uploadProgress.style.display = 'none';
                progressBar.style.width = '0%';
                progressText.textContent = 'Uploading: 0%';
                imagePreview.style.display = 'none';
                filePreview.style.display = 'none';
                fileUploadInput.value = '';
            }

            // Upload the file to Firebase Storage and send message
            function uploadFile(file) {
                const currentUserId = "{{ $currentUser->id }}";
                const receiverId = "{{ $user->id }}";
                const timestamp = Date.now();
                const fileExtension = file.name.split('.').pop();
                const storageRef = storage.ref();

                // Create a reference to the file in Firebase Storage
                const fileRef = storageRef.child(`chat_files/${currentUserId}/${timestamp}_${file.name}`);

                // Show upload progress
                uploadOptions.style.display = 'none';
                uploadPreview.style.display = 'none';
                uploadProgress.style.display = 'block';

                // Upload the file
                const uploadTask = fileRef.put(file);

                // Listen for state changes, errors, and completion of the upload
                uploadTask.on('state_changed',
                    (snapshot) => {
                        // Get upload progress
                        const progress = (snapshot.bytesTransferred / snapshot.totalBytes) * 100;
                        progressBar.style.width = progress + '%';
                        progressText.textContent = `Uploading: ${Math.round(progress)}%`;
                    },
                    (error) => {
                        // Handle unsuccessful uploads
                        console.error("Error uploading file:", error);
                        alert("Error uploading file. Please try again.");
                        resetUploadUI();
                    },
                    () => {
                        // Handle successful uploads
                        uploadTask.snapshot.ref.getDownloadURL().then((downloadURL) => {
                            console.log('File available at', downloadURL);

                            // Close modal
                            uploadModal.style.display = 'none';
                            resetUploadUI();

                            // Send message with file
                            sendFileMessage(downloadURL, file.name, fileType);
                        });
                    }
                );
            }

            // Send a message with a file attachment using the same structure as regular messages
            function sendFileMessage(fileUrl, fileName, fileType) {
                const currentUserId = "{{ $currentUser->id }}";
                const receiverId = "{{ $user->id }}";
                const roomId = `${currentUserId}-${receiverId}`;
                const timestamp = Date.now();
                const db = firebase.firestore();

                // Default message text based on file type
                let messageText = fileType === 'image' ? 'Photo' : fileName;

                // Show sending indicator
                const tempId = 'msg-' + timestamp;
                let messageContent = '';

                if (fileType === 'image') {
                    messageContent = `<img src="${fileUrl}" alt="Image" class="chat-image"><br>${messageText}`;
                } else {
                    messageContent = `<div class="file-attachment">
                                                                                                        <a href="${fileUrl}" target="_blank" download="${fileName}">
                                                                                                            <i class="fas fa-file"></i> ${fileName}
                                                                                                        </a>
                                                                                                    </div>
                                                                                                    ${messageText}`;
                }

                const tempMsg = `
                                                                                                    <div id="${tempId}" class="message-row outgoing">
                                                                                                        <div class="message-bubble">
                                                                                                            <div class="message-text">${messageContent}</div>
                                                                                                            <div class="message-time">Sending...</div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                `;

                $('#messages-container').append(tempMsg);
                scrollToBottom();

                // Get sender and receiver data
                const senderData = {
                    auth: true,
                    device_token: "{{ $currentUser->device_token ?? '' }}",
                    id: currentUserId,
                    imageUrl: "{{ $currentUser->profile_image ?? '' }}",
                    name: "{{ $currentUser->name }}",
                    phone_number: "{{ $currentUser->phone_number ?? '' }}"
                };

                const receiverData = {
                    auth: false,
                    device_token: "{{ $user->device_token ?? '' }}",
                    id: receiverId,
                    imageUrl: "{{ $user->profile_image ?? '' }}",
                    name: "{{ $user->name }}",
                    phone_number: "{{ $user->phone_number ?? '' }}"
                };

                // Create the message data with the format you specified
                const messageData = {
                    id: timestamp.toString(),
                    sender: senderData,
                    receiver: receiverData,
                    text: messageText,
                    time: timestamp,
                    fileUrl: fileUrl // Include the file URL
                };

                // Save to Firestore in the path: /messages/[senderID]-[receiverID]/chats/[messageID]
                db.collection('messages').doc(roomId).collection('chats').add(messageData)
                    .then((docRef) => {
                        console.log("File message saved successfully", docRef.id);

                        // Update unread counter
                        db.collection('unread').doc(receiverId).collection('senders').doc(currentUserId).set({
                            count: firebase.firestore.FieldValue.increment(1)
                        }, { merge: true });

                        // Remove temp message (it will be replaced by the real one from the snapshot)
                        $('#' + tempId).remove();
                    })
                    .catch(error => {
                        console.error("Error sending file message:", error);
                        $('#' + tempId + ' .message-time').text('Failed to send');
                    });
            }
        }
    </script>
@endsection
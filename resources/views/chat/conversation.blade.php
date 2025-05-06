<!-- resources/views/chat/conversation.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <!-- Include the chat list sidebar -->
                @include('chat.partials.sidebar')
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header chat-header">
                        <div class="d-flex align-items-center">
                            <div class="user-avatar">
                                <img src="{{ $user->avatar ?? 'default-avatar.png' }}" alt="User">
                                <span class="status-dot {{ $user->is_online ? 'online' : '' }}"></span>
                            </div>
                            <div class="chat-user-info">
                                <h5>{{ $user->name }}</h5>
                                <small>{{ $user->is_online ? 'Online' : 'Offline' }}</small>
                            </div>
                        </div>
                        <div class="chat-actions">
                            <button class="btn btn-sm"><i class="fas fa-search"></i></button>
                            <button class="btn btn-sm"><i class="fas fa-video"></i></button>
                            <button class="btn btn-sm"><i class="fas fa-ellipsis-v"></i></button>
                        </div>
                    </div>
                    <div class="card-body chat-body" id="messages-container">
                        <!-- Messages will be loaded here dynamically -->
                    </div>
                    <div class="card-footer">
                        <form id="message-form" class="d-flex align-items-center">
                            <button type="button" class="btn btn-icon"><i class="fas fa-smile"></i></button>
                            <input type="text" id="message-input" class="form-control" placeholder="Type your message...">
                            <button type="button" class="btn btn-icon"><i class="fas fa-paperclip"></i></button>
                            <button type="submit" class="btn btn-primary send-btn"><i
                                    class="fas fa-paper-plane"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-auth.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-database.js"></script>

    <script>
        // Initialize Firebase
        const firebaseConfig = {
            apiKey: "YOUR_API_KEY",
            authDomain: "wic-doctor-b83e0.firebaseapp.com",
            projectId: "wic-doctor-b83e0",
            storageBucket: "wic-doctor-b83e0.appspot.com",
            messagingSenderId: "YOUR_SENDER_ID",
            appId: "YOUR_APP_ID"
        };

        // Initialize Firebase
        firebase.initializeApp(firebaseConfig);

        // Sign in to Firebase with the custom token from Laravel
        firebase.auth().signInWithCustomToken("{{ $firebaseToken }}")
            .then((userCredential) => {
                // Signed in
                console.log("Signed in to Firebase");
                initChat();
            })
            .catch((error) => {
                console.error("Firebase auth error:", error);
            });

        function initChat() {
            const currentUserId = "{{ $currentUser->id }}";
            const receiverId = "{{ $user->id }}";

            // Create a unique room ID by sorting and combining the user IDs
            const roomId = [currentUserId, receiverId].sort().join('_');

            // Set up Firestore
            const db = firebase.firestore();

            // Load existing messages
            db.collection('chats').doc(roomId).collection('messages')
                .orderBy('timestamp')
                .onSnapshot((snapshot) => {
                    snapshot.docChanges().forEach((change) => {
                        if (change.type === 'added') {
                            const message = change.doc.data();
                            displayMessage(message);
                        }
                    });
                });

            // Track presence for receiver
            db.collection('presence').doc(receiverId)
                .onSnapshot((doc) => {
                    if (doc.exists) {
                        const status = doc.data();
                        updateUserStatus(receiverId, status);
                    }
                });

            // Update current user's presence
            const presenceRef = db.collection('presence').doc(currentUserId);

            // Track connection state
            firebase.database().ref('.info/connected').on('value', (snapshot) => {
                if (snapshot.val() === false) return;

                presenceRef.set({
                    online: true,
                    lastSeen: firebase.firestore.FieldValue.serverTimestamp()
                });

                presenceRef.onDisconnect().update({
                    online: false,
                    lastSeen: firebase.firestore.FieldValue.serverTimestamp()
                });
            });

            // Track unread messages
            db.collection('unread').doc(currentUserId).collection('senders')
                .onSnapshot((snapshot) => {
                    snapshot.forEach((doc) => {
                        const senderId = doc.id;
                        const count = doc.data().count || 0;

                        if (count > 0) {
                            // Update unread badge
                            $(`.user-${senderId} .unread-count`).text(count).show();
                        } else {
                            $(`.user-${senderId} .unread-count`).hide();
                        }
                    });
                });

            // Send new message
            $('#message-form').on('submit', function (e) {
                e.preventDefault();

                const messageInput = $('#message-input');
                const messageText = messageInput.val().trim();

                if (!messageText) return;

                // Save to Firestore
                db.collection('chats').doc(roomId).collection('messages').add({
                    text: messageText,
                    senderId: currentUserId,
                    timestamp: firebase.firestore.FieldValue.serverTimestamp()
                });

                // Update unread counter
                db.collection('unread').doc(receiverId).collection('senders').doc(currentUserId).set({
                    count: firebase.firestore.FieldValue.increment(1)
                }, { merge: true });

                // Clear input
                messageInput.val('');
            });

            // Mark messages as read when conversation opened
            markMessagesAsRead(currentUserId, receiverId);
        }

        function markMessagesAsRead(userId, senderId) {
            const db = firebase.firestore();

            db.collection('unread').doc(userId).collection('senders').doc(senderId).set({
                count: 0
            }, { merge: true });
        }

        function displayMessage(message) {
            const currentUserId = "{{ $currentUser->id }}";
            const isOwnMessage = message.senderId === currentUserId;

            const timestamp = message.timestamp ? new Date(message.timestamp) : new Date();

            const messageElement = `
            <div class="message-item ${isOwnMessage ? 'own-message' : 'other-message'}">
                <div class="message-content">
                    <p>${message.text}</p>
                    <small>${timestamp.toLocaleTimeString()}</small>
                </div>
            </div>
        `;

            $('#messages-container').append(messageElement);

            // Scroll to the bottom of the container
            const container = document.getElementById('messages-container');
            container.scrollTop = container.scrollHeight;
        }

        function updateUserStatus(userId, status) {
            const statusDot = $(`.user-${userId} .status-dot`);
            if (status.online) {
                statusDot.addClass('online');
                $(`.user-${userId} .user-status`).text('Online');
            } else {
                statusDot.removeClass('online');
                const lastSeen = status.lastSeen ? new Date(status.lastSeen.toDate()) : new Date();
                $(`.user-${userId} .user-status`).text(`Last seen ${formatLastSeen(lastSeen)}`);
            }
        }

        function formatLastSeen(date) {
            const now = new Date();
            const diff = Math.floor((now - date) / 1000); // seconds

            if (diff < 60) return 'just now';
            if (diff < 3600) return `${Math.floor(diff / 60)} min ago`;
            if (diff < 86400) return `${Math.floor(diff / 3600)} h ago`;
            return date.toLocaleDateString();
        }
    </script>
@endsection
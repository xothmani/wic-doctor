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
                    <a href="{{ route('doctor.messages.show', $quickUser->id) }}" class="avatar-circle">
                        <img src="{{ $quickUser->avatar ?? asset('images/default-avatar.png') }}" alt="{{ $quickUser->name }}">
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
                    <div class="chat-item user-{{ $chatUser->id }}">
                        <a href="{{ route('doctor.messages.show', $chatUser->id) }}" class="chat-link">
                            <div class="avatar">
                                <img src="{{ $chatUser->avatar ?? asset('images/default-avatar.png') }}"
                                    alt="{{ $chatUser->name }}">
                                <span class="status-dot"></span>
                            </div>
                            <div class="chat-info">
                                <div class="chat-name">{{ $chatUser->name }}</div>
                                <div class="chat-message">
                                    <span class="message-preview">Start a conversation</span>
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

        <!-- Main Content (Empty State) -->
        <div class="chat-content">
            <div class="empty-chat">
                <div class="empty-chat-message">
                    <h3>Select a chat to start messaging</h3>
                    <p>Choose someone from your contacts to start a conversation</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
@endsection

@section('scripts')
    <script src="https://www.gstatic.com/firebasejs/7.2.0/firebase.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Firebase if not already initialized
            if (!firebase.apps.length) {
                const firebaseConfig = {
                    apiKey: "AIzaSyCONylt3t8MDw_02k5H9ceXTEmdtxmQtu8",
                    authDomain: "wic-doctor-b83e0.firebaseapp.com",
                    projectId: "wic-doctor-b83e0",
                    storageBucket: "wic-doctor-b83e0.appspot.com",
                    messagingSenderId: "599835198131",
                    appId: "1:599835198131:web:3ee81bdd9f4cff0fa21f22"
                };

                // Initialize Firebase
                firebase.initializeApp(firebaseConfig);
            }

            console.log("Firebase SDK loaded:", firebase.SDK_VERSION);
            console.log("Firestore availability:", typeof firebase.firestore);

            // Proceed with authentication once we're sure Firebase is loaded
            firebase.auth().signInWithCustomToken("{{ $firebaseToken }}")
                .then((userCredential) => {
                    console.log("Signed in to Firebase");
                    initApp();
                })
                .catch((error) => {
                    console.error("Firebase auth error:", error);
                    // Try to initialize chat anyway
                    initApp();
                });
        });

        function initApp() {
            const currentUserId = "{{ auth()->id() }}";

            // Try this alternative way to access Firestore
            const db = firebase.firestore ? firebase.firestore() : firebase.app().firestore();

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

            // Listen for other users' presence
            @foreach($users as $user)
                db.collection('presence').doc('{{ $user->id }}')
                    .onSnapshot((doc) => {
                        if (doc.exists) {
                            const status = doc.data();
                            updateUserStatus('{{ $user->id }}', status);
                        }
                    });
            @endforeach

                // Track last messages for preview
                @foreach($users as $user)
                    const roomId = [currentUserId, '{{ $user->id }}'].sort().join('_');
                    db.collection('chats').doc(roomId).collection('messages')
                        .orderBy('timestamp', 'desc')
                        .limit(1)
                        .onSnapshot((snapshot) => {
                            if (!snapshot.empty) {
                                const lastMessage = snapshot.docs[0].data();
                                const time = lastMessage.timestamp ? new Date(lastMessage.timestamp.toDate()) : new Date();
                                const timeStr = formatTime(time);
                                const preview = lastMessage.text.substring(0, 30) + (lastMessage.text.length > 30 ? '...' : '');
                                $(`.user-{{ $user->id }} .message-preview`).text(preview);
                                $(`.user-{{ $user->id }} .chat-time`).text(timeStr);
                            }
                        });
                @endforeach

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

            // Search functionality
            $('#chat-search').on('keyup', function () {
                const value = $(this).val().toLowerCase();
                $('.chat-item').filter(function () {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
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
    </script>
@endsection
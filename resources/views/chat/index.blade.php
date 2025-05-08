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
                @foreach($patients->take(8) as $quickPatient)
                    <a href="{{ route('doctor.messages.show', $quickPatient->id) }}"
                        class="avatar-circle {{ $quickPatient->id == $user->id ? 'active' : '' }}">
                        <img src="{{ $quickPatient->getFirstMediaUrl('avatar', 'icon') ?: asset('images/default-avatar.png') }}"
                            alt="{{ $quickPatient->name }}">
                        <span class="status-dot user-{{ $quickPatient->id }}"></span>
                    </a>
                @endforeach
            </div>

            <div class="chat-tabs">
                <button class="tab-btn active" data-tab="patients">Patients</button>
                <button class="tab-btn" data-tab="chats">Chat</button>
                <button class="tab-btn" data-tab="groups">Group</button>
            </div>

            <div class="chat-list" id="patients-tab">
                @foreach($patients as $patient)
                    <div class="chat-item user-{{ $patient->id }} {{ $patient->id == $user->id ? 'active' : '' }}">
                        <a href="{{ route('doctor.messages.show', $patient->id) }}" class="chat-link">
                            <div class="avatar">
                                <img src="{{ $patient->getFirstMediaUrl('avatar', 'icon') ?: asset('images/default-avatar.png') }}"
                                    alt="{{ $patient->name }}">
                                <span class="status-dot"></span>
                            </div>
                            <div class="chat-info">
                                <div class="chat-name">{{ $patient->name }}</div>
                                @if($patient->patient)
                                    <div class="patient-info">ID: {{ $patient->patient->patient_id ?? 'N/A' }}</div>
                                @endif
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

            <div class="chat-list" id="chats-tab" style="display: none;">
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

            <div class="chat-list" id="groups-tab" style="display: none;">
                <div id="firebase-groups-list">
                    <!-- Firebase groups will be loaded here dynamically -->
                </div>
                <div class="create-group">
                    <button id="create-group-btn"><i class="fas fa-plus"></i> Create New Group</button>
                </div>
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
                        @if(isset($user->patient) && $user->patient)
                            <div class="patient-details">
                                <span class="patient-id">ID: {{ $user->patient->patient_id ?? 'N/A' }}</span>
                                @if(isset($user->patient->age))
                                    | <span class="patient-age">Age: {{ $user->patient->age }}</span>
                                @endif
                            </div>
                        @endif
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
            <div id="emoji-picker" class="emoji-picker" style="display: none;">
                <div class="emoji-picker-header">
                    <span>Emojis</span>
                    <button class="emoji-close"><i class="fas fa-times"></i></button>
                </div>
                <div class="emoji-categories">
                    <button class="emoji-category active" data-category="smileys"><i class="far fa-smile"></i></button>
                    <button class="emoji-category" data-category="people"><i class="far fa-hand-peace"></i></button>
                    <button class="emoji-category" data-category="animals"><i class="fas fa-cat"></i></button>
                    <button class="emoji-category" data-category="food"><i class="fas fa-pizza-slice"></i></button>
                    <button class="emoji-category" data-category="travel"><i class="fas fa-car"></i></button>
                    <button class="emoji-category" data-category="activities"><i class="fas fa-futbol"></i></button>
                    <button class="emoji-category" data-category="objects"><i class="fas fa-lightbulb"></i></button>
                    <button class="emoji-category" data-category="symbols"><i class="fas fa-heart"></i></button>
                    <button class="emoji-category" data-category="flags"><i class="fas fa-flag"></i></button>
                </div>
                <div class="emoji-container" id="emoji-container">
                    <!-- Emojis will be added here dynamically -->
                </div>
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

    <!-- Group Creation Modal -->
    <div id="group-modal" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h4>Create New Group</h4>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="group-name">Group Name</label>
                    <input type="text" id="group-name" class="form-control" placeholder="Enter group name">
                </div>
                <div class="form-group">
                    <label>Add Participants</label>
                    <div class="user-search">
                        <input type="text" id="user-search" class="form-control" placeholder="Search users...">
                    </div>
                    <div class="user-list">
                        @foreach($users as $potentialMember)
                            <div class="user-item" data-user-id="{{ $potentialMember->id }}">
                                <div class="user-avatar">
                                    <img src="{{ $potentialMember->getFirstMediaUrl('avatar', 'icon') ?: asset('images/default-avatar.png') }}"
                                        alt="{{ $potentialMember->name }}">
                                </div>
                                <div class="user-name">{{ $potentialMember->name }}</div>
                                <div class="user-select">
                                    <input type="checkbox" name="selected_users[]" value="{{ $potentialMember->id }}">
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="selected-users">
                        <h5>Selected Users</h5>
                        <div id="selected-user-list"></div>
                    </div>
                </div>
                <button id="create-group-submit" class="btn btn-primary">Create Group</button>
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

        .emoji-picker {
            position: absolute;
            bottom: 120px;
            /* Distance from bottom */
            left: 40%;
            /* Center horizontally */
            transform: translateX(-50%);
            /* Adjust for true centering */
            width: 320px;
            max-width: 90%;
            /* Make it responsive */
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            overflow: hidden;
        }

        /* For smaller screens, adjust the width */
        @media (max-width: 576px) {
            .emoji-picker {
                width: 90%;
                left: 5%;
                /* Adjust for small screens */
                transform: none;
                /* No transform needed for percentage-based positioning */
            }
        }

        .emoji-picker-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
        }

        .emoji-picker-header span {
            font-weight: bold;
        }

        .emoji-close {
            background: none;
            border: none;
            cursor: pointer;
            color: #777;
            font-size: 16px;
        }

        .emoji-categories {
            display: flex;
            overflow-x: auto;
            padding: 8px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
        }

        .emoji-category {
            flex: 0 0 auto;
            background: none;
            border: none;
            padding: 6px 10px;
            margin-right: 4px;
            border-radius: 4px;
            cursor: pointer;
            color: #555;
        }

        .emoji-category:hover {
            background-color: #e9ecef;
        }

        .emoji-category.active {
            background-color: #e0e3e7;
            color: #4a6cf7;
        }

        .emoji-container {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 8px;
            padding: 15px;
            max-height: 250px;
            overflow-y: auto;
        }

        .emoji-item {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            padding: 5px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
            user-select: none;
        }

        .emoji-item:hover {
            background-color: #f0f2f5;
        }

        /* Group tab styling */
        .chat-tabs {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .tab-btn {
            flex: 1;
            padding: 10px 5px;
            background: none;
            border: none;
            font-size: 14px;
            font-weight: 500;
            color: #6c757d;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
        }

        .tab-btn.active {
            color: #007bff;
            border-bottom: 2px solid #007bff;
        }

        .tab-btn:hover:not(.active) {
            color: #495057;
        }

        /* Group list styling */
        #groups-tab {
            padding: 10px;
        }

        #firebase-groups-list {
            margin-bottom: 15px;
        }

        .no-groups {
            padding: 15px;
            text-align: center;
            color: #6c757d;
            background-color: #f8f9fa;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        /* Create Group button styling */
        .create-group {
            padding: 10px 0;
        }

        #create-group-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 8px 12px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            color: #212529;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        #create-group-btn:hover {
            background-color: #e9ecef;
        }

        #create-group-btn i {
            margin-right: 5px;
        }

        /* Group modal styling */
        #group-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            /* Added flexible centering */
            display: none;
            /* Will be changed to flex when visible */
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            /* Additional positioning - ensures the modal stays centered */
            position: relative;
            margin: auto;
            /* Add some animation */
            animation: modalOpen 0.3s ease-out;
        }

        @keyframes modalOpen {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #e0e0e0;
            background-color: #f8f9fa;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        .modal-header h4 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #212529;
        }

        .close-modal {
            font-size: 24px;
            font-weight: 700;
            color: #6c757d;
            cursor: pointer;
            line-height: 1;
            padding: 0 5px;
        }

        .close-modal:hover {
            color: #343a40;
        }

        .modal-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
        }

        .user-list {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            margin-top: 10px;
        }

        .user-item {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
        }

        .user-item:last-child {
            border-bottom: none;
        }

        .user-item:hover {
            background-color: #f8f9fa;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            margin-right: 10px;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .user-name {
            flex: 1;
            font-size: 14px;
        }

        .user-select {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .selected-users {
            margin-top: 20px;
        }

        .selected-users h5 {
            font-size: 16px;
            margin-bottom: 10px;
        }

        .no-users-selected {
            padding: 10px;
            text-align: center;
            color: #6c757d;
            background-color: #f8f9fa;
            border-radius: 4px;
        }

        .selected-user-item {
            display: flex;
            align-items: center;
            padding: 8px 10px;
            background-color: #f0f7ff;
            border-radius: 4px;
            margin-bottom: 5px;
        }

        .selected-user-avatar {
            width: 28px;
            height: 28px;
            margin-right: 10px;
        }

        .selected-user-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .selected-user-name {
            flex: 1;
            font-size: 14px;
        }

        .remove-user {
            color: #6c757d;
            cursor: pointer;
            padding: 4px;
        }

        .remove-user:hover {
            color: #dc3545;
        }

        #create-group-submit {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        #create-group-submit:hover {
            background-color: #0069d9;
        }

        /* Group message styles */
        .message-row.incoming .message-sender {
            font-size: 12px;
            font-weight: 600;
            color: #495057;
            margin-bottom: 3px;
        }

        /* Loading states */
        .loading-groups,
        .loading-messages {
            padding: 15px;
            text-align: center;
            color: #6c757d;
        }

        .error-groups,
        .error-messages {
            padding: 15px;
            text-align: center;
            color: #dc3545;
            background-color: #f8d7da;
            border-radius: 5px;
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
                    const messageTimestamp = messageDate.getTime();

                    // Skip if this is a temporary message we already handled
                    if (message._tempId && $(`[data-id="${message._tempId}"]`).length) {
                        return;
                    }

                    // Skip if this is a temporary message we're already showing
                    if ($(`[data-temporary][data-timestamp="${message.time}"]`).length) {
                        return;
                    }

                    // Only process if this message doesn't already exist
                    if (messagesContainer.find(`.message-row[data-id="${message.id}"]`).length === 0) {
                        // Find the correct position to insert this message (chronological order)
                        let inserted = false;
                        const existingMessages = messagesContainer.find('.message-row');

                        existingMessages.each(function () {
                            const timestamp = parseInt($(this).attr('data-timestamp') || 0);

                            if (messageTimestamp < timestamp) {
                                // Insert the message before this one
                                insertMessage(message, isOutgoing, messageDate, $(this));
                                inserted = true;
                                return false; // Break the loop
                            }
                        });

                        // If not inserted, add it at the end
                        if (!inserted) {
                            insertMessage(message, isOutgoing, messageDate);
                        }

                        scrollToBottom();
                    }
                }

                // Your existing insertMessage function remains the same
                function insertMessage(message, isOutgoing, messageDate, beforeElement) {
                    const messageTimestamp = messageDate.getTime();

                    // Check if we need to add a date divider
                    let needsDateHeader = true;
                    let previousElement = beforeElement ? beforeElement.prev() : messagesContainer.children().last();

                    // Look backwards to find the previous date header or message
                    while (previousElement.length > 0) {
                        if (previousElement.hasClass('date-divider')) {
                            const previousMessageDate = new Date(parseInt(previousElement.data('date')));
                            if (isSameDay(previousMessageDate, messageDate)) {
                                needsDateHeader = false;
                            }
                            break;
                        } else if (previousElement.hasClass('message-row')) {
                            const previousMessageDate = new Date(parseInt(previousElement.attr('data-timestamp')));
                            if (isSameDay(previousMessageDate, messageDate)) {
                                needsDateHeader = false;
                            }
                            break;
                        }
                        previousElement = previousElement.prev();
                    }

                    // Add date header if needed
                    if (needsDateHeader) {
                        const dateHeader = $(`<div class="date-divider" data-date="${messageTimestamp}">${formatDateHeader(messageDate)}</div>`);
                        if (beforeElement) {
                            beforeElement.before(dateHeader);
                        } else {
                            messagesContainer.append(dateHeader);
                        }
                    }

                    // Format the message time
                    const timeStr = messageDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

                    // Handle message content (text, image, or file)
                    let messageContent = '';
                    if (message.fileUrl && message.fileUrl.trim() !== '') {
                        const fileUrl = message.fileUrl;
                        const fileExtension = fileUrl.split('.').pop().toLowerCase().split('?')[0];
                        const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                        if (imageExtensions.includes(fileExtension)) {
                            // It's an image
                            messageContent = `<img src="${fileUrl}" alt="Image" class="chat-image">`;
                        } else {
                            // It's a file
                            const fileName = message.text || fileUrl.split('/').pop().split('?')[0];
                            messageContent = `
                            <div class="file-attachment">
                                <a href="${fileUrl}" target="_blank" download="${fileName}">
                                    <img src="/images/file.png" alt="File" class="file-icon">
                                    ${fileName}
                                </a>
                            </div>`;
                        }
                    } else {
                        // Regular text message
                        messageContent = message.text;
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

            // Send a message with a file attachment
            function sendFileMessage(fileUrl, fileName, fileType) {
                const sendButton = document.getElementById('send-message-btn');
                const chatType = sendButton.getAttribute('data-chat-type');
                const timestamp = Date.now();
                const db = firebase.firestore();

                // Different handling based on file type
                let messageText, messageContent;
                const isImage = fileType === 'image';

                if (isImage) {
                    messageText = 'Photo';
                    messageContent = `<img src="${fileUrl}" alt="Image" class="chat-image">`;
                } else {
                    messageText = fileName;
                    messageContent = `<div class="file-attachment">
                                    <a href="${fileUrl}" target="_blank" download="${fileName}">
                                        <i class="fas fa-file"></i> ${fileName}
                                    </a>
                                </div>`;
                }

                // Generate a unique temporary ID for this message
                const tempId = 'temp-' + timestamp;

                // Show sending indicator immediately
                const tempMsg = `
                <div id="${tempId}" class="message-row outgoing" data-timestamp="${timestamp}" data-temporary="true">
                    <div class="message-bubble">
                        <div class="message-text">${messageContent}</div>
                        <div class="message-time">Sending...</div>
                    </div>
                </div>
            `;
                $('#messages-container').append(tempMsg);
                scrollToBottom();

                if (chatType === 'group') {
                    // Handle group file message
                    const groupId = sendButton.getAttribute('data-group-id');
                    if (!groupId) return;

                    const currentUserId = "{{ $currentUser->id }}";

                    const senderData = {
                        auth: true,
                        device_token: "{{ $currentUser->device_token ?? '' }}",
                        id: currentUserId,
                        imageUrl: "{{ $currentUser->profile_image ?? '' }}",
                        name: "{{ $currentUser->name }}",
                        phone_number: "{{ $currentUser->phone_number ?? '' }}"
                    };

                    const messageData = {
                        sender: senderData,
                        text: messageText,
                        time: timestamp,
                        fileUrl: fileUrl,
                        _tempId: tempId // Add temporary ID reference
                    };

                    db.collection('group_messages').doc(groupId).collection('chats').add(messageData)
                        .then((docRef) => {
                            console.log("Group file message saved successfully", docRef.id);

                            // Update the temporary message with real ID and status
                            updateTempMessage(tempId, docRef.id, timestamp);

                            db.collection('groups').doc(groupId).update({
                                updatedAt: timestamp
                            });
                        })
                        .catch(error => {
                            console.error("Error sending group file message:", error);
                            $(`#${tempId} .message-time`).text('Failed to send');
                        });
                } else {
                    // Direct message
                    const currentUserId = "{{ $currentUser->id }}";
                    const receiverId = "{{ $user->id }}";
                    const roomId = `${currentUserId}-${receiverId}`;

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

                    const messageData = {
                        id: timestamp.toString(),
                        sender: senderData,
                        receiver: receiverData,
                        text: messageText,
                        time: timestamp,
                        fileUrl: fileUrl,
                        _tempId: tempId // Add temporary ID reference
                    };

                    db.collection('messages').doc(roomId).collection('chats').add(messageData)
                        .then((docRef) => {
                            console.log("File message saved successfully", docRef.id);

                            // Update the temporary message with real ID and status
                            updateTempMessage(tempId, docRef.id, timestamp);

                            db.collection('unread').doc(receiverId).collection('senders').doc(currentUserId).set({
                                count: firebase.firestore.FieldValue.increment(1)
                            }, { merge: true });
                        })
                        .catch(error => {
                            console.error("Error sending file message:", error);
                            $(`#${tempId} .message-time`).text('Failed to send');
                        });
                }
            }

            // Helper function to update temporary message
            function updateTempMessage(tempId, realId, timestamp) {
                const timeStr = new Date(timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

                $(`#${tempId}`)
                    .attr('data-id', realId)
                    .removeAttr('id')
                    .removeAttr('data-temporary')
                    .find('.message-time').text(timeStr);
            }

        }

        const emojiData = {
            smileys: ["😀", "😃", "😄", "😁", "😆", "😅", "😂", "🤣", "😊", "😇", "🙂", "🙃", "😉", "😌", "😍", "🥰", "😘", "😗", "😙", "😚", "😋", "😛", "😝", "😜", "🤪", "🤨", "🧐", "🤓", "😎", "🤩", "🥳", "😏", "😒", "😞", "😔", "😟", "😕", "🙁", "☹️", "😣", "😖", "😫", "😩", "🥺", "😢", "😭", "😤", "😠", "😡"],
            people: ["👋", "🤚", "🖐️", "✋", "🖖", "👌", "🤏", "✌️", "🤞", "🤟", "🤘", "🤙", "👈", "👉", "👆", "🖕", "👇", "☝️", "👍", "👎", "✊", "👊", "🤛", "🤜", "👏", "🙌", "👐", "🤲", "🤝", "🙏", "💪", "🦾", "🦿", "🦵", "🦶", "👂", "🦻", "👃", "🧠", "🦷", "🦴", "👀", "👁️", "👅", "👄"],
            animals: ["🐶", "🐱", "🐭", "🐹", "🐰", "🦊", "🐻", "🐼", "🐨", "🐯", "🦁", "🐮", "🐷", "🐽", "🐸", "🐵", "🙈", "🙉", "🙊", "🐒", "🐔", "🐧", "🐦", "🐤", "🐣", "🐥", "🦆", "🦅", "🦉", "🦇", "🐺", "🐗", "🐴", "🦄", "🐝", "🐛", "🦋", "🐌", "🐞", "🐜"],
            food: ["🍎", "🍐", "🍊", "🍋", "🍌", "🍉", "🍇", "🍓", "🍈", "🍒", "🍑", "🥭", "🍍", "🥥", "🥝", "🍅", "🍆", "🥑", "🥦", "🥬", "🥒", "🌶️", "🌽", "🥕", "🧄", "🧅", "🥔", "🍠", "🥐", "🥯", "🍞", "🥖", "🥨", "🧀", "🥚", "🍳", "🧈", "🥞", "🧇", "🥓"],
            travel: ["🚗", "🚕", "🚙", "🚌", "🚎", "🏎️", "🚓", "🚑", "🚒", "🚐", "🚚", "🚛", "🚜", "🦯", "🦽", "🦼", "🛴", "🚲", "🛵", "🏍️", "🚨", "🚔", "🚍", "🚘", "🚖", "🚡", "🚠", "🚟", "🚃", "🚋", "🚞", "🚝", "🚄", "🚅", "🚈", "🚂"],
            activities: ["⚽", "🏀", "🏈", "⚾", "🥎", "🎾", "🏐", "🏉", "🥏", "🎱", "🪀", "🏓", "🏸", "🏒", "🏑", "🥍", "🏏", "🥅", "⛳", "🪁", "🏹", "🎣", "🤿", "🥊", "🥋", "🎽", "🛹", "🛷", "⛸️", "🥌", "🎿", "⛷️", "🏂", "🪂", "🏋️", "🤼"],
            objects: ["⌚", "📱", "📲", "💻", "⌨️", "🖥️", "🖨️", "🖱️", "🖲️", "🕹️", "🗜️", "💽", "💾", "💿", "📀", "📼", "📷", "📸", "📹", "🎥", "📽️", "🎞️", "📞", "☎️", "📟", "📠", "📺", "📻", "🎙️", "🎚️", "🎛️", "🧭", "⏱️", "⏲️", "⏰", "🕰️"],
            symbols: ["❤️", "🧡", "💛", "💚", "💙", "💜", "🖤", "🤍", "🤎", "💔", "❣️", "💕", "💞", "💓", "💗", "💖", "💘", "💝", "💟", "☮️", "✝️", "☪️", "🕉️", "☸️", "✡️", "🔯", "🕎", "☯️", "☦️", "🛐", "⛎", "♈", "♉", "♊", "♋", "♌"],
            flags: ["🏁", "🚩", "🎌", "🏴", "🏳️", "🏳️‍🌈", "🏴‍☠️", "🇦🇫", "🇦🇽", "🇦🇱", "🇩🇿", "🇦🇸", "🇦🇩", "🇦🇴", "🇦🇮", "🇦🇶", "🇦🇬", "🇦🇷", "🇦🇲", "🇦🇼", "🇦🇺", "🇦🇹", "🇦🇿", "🇧🇸", "🇧🇭", "🇧🇩", "🇧🇧", "🇧🇾", "🇧🇪"]
        };

        // Initialize emoji picker after DOM is loaded
        document.addEventListener('DOMContentLoaded', function () {
            const emojiBtn = document.querySelector('.emoji-btn');
            const emojiPicker = document.getElementById('emoji-picker');
            const emojiClose = document.querySelector('.emoji-close');
            const emojiContainer = document.getElementById('emoji-container');
            const messageInput = document.getElementById('message-input');
            const emojiCategories = document.querySelectorAll('.emoji-category');

            // Function to show a specific emoji category
            function showEmojiCategory(category) {
                emojiContainer.innerHTML = '';

                emojiData[category].forEach(emoji => {
                    const emojiDiv = document.createElement('div');
                    emojiDiv.className = 'emoji-item';
                    emojiDiv.textContent = emoji;
                    emojiDiv.addEventListener('click', function () {
                        // Insert emoji at cursor position
                        const cursorPos = messageInput.selectionStart;
                        const textBefore = messageInput.value.substring(0, cursorPos);
                        const textAfter = messageInput.value.substring(cursorPos);
                        messageInput.value = textBefore + emoji + textAfter;

                        // Set cursor position after the inserted emoji
                        messageInput.selectionStart = cursorPos + emoji.length;
                        messageInput.selectionEnd = cursorPos + emoji.length;
                        messageInput.focus();


                    });

                    emojiContainer.appendChild(emojiDiv);
                });
            }

            // Show emoji picker when emoji button is clicked
            emojiBtn.addEventListener('click', function (e) {
                e.stopPropagation();

                // Toggle emoji picker
                if (emojiPicker.style.display === 'none') {
                    emojiPicker.style.display = 'block';

                    // Show default category (smileys)
                    showEmojiCategory('smileys');

                    // Set active category
                    emojiCategories.forEach(cat => {
                        if (cat.dataset.category === 'smileys') {
                            cat.classList.add('active');
                        } else {
                            cat.classList.remove('active');
                        }
                    });
                } else {
                    emojiPicker.style.display = 'none';
                }
            });

            // Close emoji picker
            emojiClose.addEventListener('click', function () {
                emojiPicker.style.display = 'none';
            });

            // Handle emoji category selection
            emojiCategories.forEach(category => {
                category.addEventListener('click', function () {
                    const categoryName = this.dataset.category;

                    // Update active state
                    emojiCategories.forEach(cat => cat.classList.remove('active'));
                    this.classList.add('active');

                    // Show selected category
                    showEmojiCategory(categoryName);
                });
            });

            // Close emoji picker when clicking outside
            document.addEventListener('click', function (e) {
                if (!emojiPicker.contains(e.target) && e.target !== emojiBtn) {
                    emojiPicker.style.display = 'none';
                }
            });

            // Close emoji picker when clicking on message input
            messageInput.addEventListener('click', function () {
                emojiPicker.style.display = 'none';
            });

            // Show initial emoji category (smileys)
            showEmojiCategory('smileys');
        });
        // Add this code after your existing initChat function

        // Initialize tabbed navigation
        document.addEventListener('DOMContentLoaded', function () {
            // Tab switching functionality
            const tabButtons = document.querySelectorAll('.tab-btn');

            tabButtons.forEach(button => {
                button.addEventListener('click', function () {
                    // Remove active class from all buttons
                    tabButtons.forEach(btn => btn.classList.remove('active'));

                    // Add active class to clicked button
                    this.classList.add('active');

                    // Get the tab to show
                    const tabToShow = this.dataset.tab;

                    // Hide all tabs
                    document.querySelectorAll('.chat-list').forEach(tab => {
                        tab.style.display = 'none';
                    });

                    // Show the selected tab
                    document.getElementById(tabToShow + '-tab').style.display = 'block';

                    // Load Firebase groups when the groups tab is selected
                    if (tabToShow === 'groups') {
                        loadFirebaseGroups();
                    }
                });
            });

            // Group creation modal functionality
            const createGroupBtn = document.getElementById('create-group-btn');
            const groupModal = document.getElementById('group-modal');
            const closeModal = document.querySelector('.close-modal');
            const createGroupSubmit = document.getElementById('create-group-submit');

            if (createGroupBtn) {
                createGroupBtn.addEventListener('click', function () {
                    groupModal.style.display = 'block';
                });
            }

            if (closeModal) {
                closeModal.addEventListener('click', function () {
                    groupModal.style.display = 'none';
                });
            }

            // User selection for group creation
            const userItems = document.querySelectorAll('.user-item');
            const selectedUserList = document.getElementById('selected-user-list');

            if (userItems.length > 0 && selectedUserList) {
                userItems.forEach(item => {
                    item.addEventListener('click', function () {
                        const checkbox = this.querySelector('input[type="checkbox"]');
                        checkbox.checked = !checkbox.checked;
                        updateSelectedUsersList();
                    });
                });
            }

            // User search functionality
            const userSearchInput = document.getElementById('user-search');
            if (userSearchInput) {
                userSearchInput.addEventListener('input', function () {
                    const searchTerm = this.value.toLowerCase();

                    userItems.forEach(item => {
                        const userName = item.querySelector('.user-name').textContent.toLowerCase();
                        if (userName.includes(searchTerm)) {
                            item.style.display = 'flex';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            }

            // Create group submission
            if (createGroupSubmit) {
                createGroupSubmit.addEventListener('click', function () {
                    const groupName = document.getElementById('group-name').value.trim();
                    if (!groupName) {
                        alert('Please enter a group name');
                        return;
                    }

                    const selectedUsers = document.querySelectorAll('.user-item input[type="checkbox"]:checked');
                    if (selectedUsers.length === 0) {
                        alert('Please select at least one user');
                        return;
                    }

                    // Create group in Firebase
                    createFirebaseGroup(groupName, selectedUsers);
                });
            }
        });

        // Function to update selected users list
        function updateSelectedUsersList() {
            const selectedUsers = document.querySelectorAll('.user-item input[type="checkbox"]:checked');
            const selectedUserList = document.getElementById('selected-user-list');

            if (!selectedUserList) return;

            selectedUserList.innerHTML = '';

            if (selectedUsers.length === 0) {
                selectedUserList.innerHTML = '<div class="no-users-selected">No users selected</div>';
                return;
            }

            selectedUsers.forEach(checkbox => {
                const userId = checkbox.value;
                const userItem = document.querySelector(`.user-item[data-user-id="${userId}"]`);

                if (!userItem) return;

                const userName = userItem.querySelector('.user-name').textContent;
                const userAvatar = userItem.querySelector('.user-avatar img').src;

                const selectedUserItem = document.createElement('div');
                selectedUserItem.className = 'selected-user-item';
                selectedUserItem.innerHTML = `
                                                                                                                                                        <div class="selected-user-avatar">
                                                                                                                                                            <img src="${userAvatar}" alt="${userName}">
                                                                                                                                                        </div>
                                                                                                                                                        <div class="selected-user-name">${userName}</div>
                                                                                                                                                        <div class="remove-user" data-user-id="${userId}">
                                                                                                                                                            <i class="fas fa-times"></i>
                                                                                                                                                        </div>
                                                                                                                                                    `;

                selectedUserList.appendChild(selectedUserItem);
            });

            // Add remove user functionality
            document.querySelectorAll('.remove-user').forEach(removeBtn => {
                removeBtn.addEventListener('click', function () {
                    const userId = this.dataset.userId;
                    const checkbox = document.querySelector(`.user-item[data-user-id="${userId}"] input[type="checkbox"]`);
                    if (checkbox) {
                        checkbox.checked = false;
                    }
                    updateSelectedUsersList();
                });
            });
        }

        // Function to load Firebase groups
        function loadFirebaseGroups() {
            try {
                const db = firebase.firestore();
                const currentUserId = "{{ $currentUser->id }}";
                const groupsListElement = document.getElementById('firebase-groups-list');

                if (!groupsListElement) return;

                groupsListElement.innerHTML = '<div class="loading-groups">Loading groups...</div>';

                // Reference to user's groups in Firebase
                db.collection('user_groups').doc(currentUserId).get()
                    .then((doc) => {
                        if (!doc.exists || !doc.data()) {
                            groupsListElement.innerHTML = '<div class="no-groups">No groups yet. Create a new group to get started.</div>';
                            return;
                        }

                        const groups = doc.data();
                        groupsListElement.innerHTML = '';

                        // Check if user has any groups
                        if (Object.keys(groups).length === 0) {
                            groupsListElement.innerHTML = '<div class="no-groups">No groups yet. Create a new group to get started.</div>';
                            return;
                        }

                        // Process each group
                        Object.keys(groups).forEach(groupId => {
                            if (!groups[groupId]) return; // Skip if not a member

                            // Get group details
                            db.collection('groups').doc(groupId).get().then((groupDoc) => {
                                if (!groupDoc.exists) return;

                                const groupData = groupDoc.data();

                                // Create group element
                                const groupElement = document.createElement('div');
                                groupElement.className = 'chat-item group-' + groupId;
                                groupElement.setAttribute('data-group-id', groupId);

                                // Get last message
                                db.collection('group_messages').doc(groupId).collection('chats')
                                    .orderBy('time', 'desc')
                                    .limit(1)
                                    .get()
                                    .then((msgSnapshot) => {
                                        let lastMessage = 'No messages yet';
                                        let timestamp = Date.now();

                                        if (!msgSnapshot.empty) {
                                            const msg = msgSnapshot.docs[0].data();
                                            lastMessage = msg.text || (msg.fileUrl ? 'File shared' : 'New message');
                                            timestamp = msg.time;
                                        }

                                        // Format group element HTML
                                        groupElement.innerHTML = `
                                                                                                                                                                                <a href="#" class="chat-link group-chat-link" data-group-id="${groupId}">
                                                                                                                                                                                    <div class="avatar">
                                                                                                                                                                                        <img src="${groupData.avatar || '/images/default-group.png'}" alt="${groupData.name}">
                                                                                                                                                                                    </div>
                                                                                                                                                                                    <div class="chat-info">
                                                                                                                                                                                        <div class="chat-name">${groupData.name}</div>
                                                                                                                                                                                        <div class="chat-message">
                                                                                                                                                                                            <span class="message-preview">${lastMessage}</span>
                                                                                                                                                                                        </div>
                                                                                                                                                                                    </div>
                                                                                                                                                                                    <div class="chat-meta">
                                                                                                                                                                                        <div class="chat-time" data-timestamp="${timestamp}">${formatTime(new Date(parseInt(timestamp)))}</div>
                                                                                                                                                                                        <div class="unread-badge" style="display: none;">0</div>
                                                                                                                                                                                    </div>
                                                                                                                                                                                </a>
                                                                                                                                                                            `;

                                        // Add click handler for the group
                                        groupElement.querySelector('.group-chat-link').addEventListener('click', function (e) {
                                            e.preventDefault();
                                            loadGroupChat(groupId);
                                        });

                                        // Add to groups list
                                        groupsListElement.appendChild(groupElement);

                                        // Sort groups by latest message
                                        sortChatsByLatestMessage();
                                    });
                            });
                        });
                    })
                    .catch(error => {
                        console.error("Error loading groups:", error);
                        groupsListElement.innerHTML = '<div class="error-groups">Error loading groups. Please try again.</div>';
                    });
            } catch (e) {
                console.error("Error in loadFirebaseGroups:", e);
            }
        }

        // Function to sort chats by latest message
        function sortChatsByLatestMessage() {
            const groupsList = document.getElementById('firebase-groups-list');
            if (!groupsList) return;

            const groupItems = Array.from(groupsList.querySelectorAll('.chat-item'));

            // Sort by timestamp
            groupItems.sort((a, b) => {
                const timeA = parseInt(a.querySelector('.chat-time').dataset.timestamp || '0');
                const timeB = parseInt(b.querySelector('.chat-time').dataset.timestamp || '0');
                return timeB - timeA;
            });

            // Reappend in new order
            groupItems.forEach(item => {
                groupsList.appendChild(item);
            });
        }

        // Function to load a group chat
        function loadGroupChat(groupId) {
            try {
                const db = firebase.firestore();
                const currentUserId = "{{ $currentUser->id }}";

                // Set all chat items as inactive
                document.querySelectorAll('.chat-item.active').forEach(item => {
                    item.classList.remove('active');
                });

                // Set the selected group as active
                document.querySelector(`.group-${groupId}`).classList.add('active');

                // Clear current chat
                const messagesContainer = document.getElementById('messages-container');
                messagesContainer.innerHTML = '<div class="loading-messages">Loading messages...</div>';

                // Get group details
                db.collection('groups').doc(groupId).get().then((groupDoc) => {
                    if (!groupDoc.exists) {
                        messagesContainer.innerHTML = '<div class="error-messages">Group not found.</div>';
                        return;
                    }

                    const groupData = groupDoc.data();

                    // Update conversation header
                    document.querySelector('.conversation-user .avatar img').src = groupData.avatar || '/images/default-group.png';
                    document.querySelector('.user-name').textContent = groupData.name;
                    document.querySelector('.user-status').textContent = 'Group Chat';

                    // Clear any patient details
                    const patientDetailsElement = document.querySelector('.patient-details');
                    if (patientDetailsElement) {
                        patientDetailsElement.style.display = 'none';
                    }

                    // Set chat type data attribute for the send button
                    $('#send-message-btn').attr('data-chat-type', 'group');
                    $('#send-message-btn').attr('data-group-id', groupId);

                    // Load group messages
                    loadGroupMessages(groupId);
                });
            } catch (e) {
                console.error("Error in loadGroupChat:", e);
            }
        }

        // Function to load group messages
        function loadGroupMessages(groupId) {
            try {
                const db = firebase.firestore();
                const currentUserId = "{{ $currentUser->id }}";
                const messagesContainer = document.getElementById('messages-container');

                // Reference to group messages in Firebase
                db.collection('group_messages').doc(groupId).collection('chats')
                    .orderBy('time')
                    .limit(50)
                    .get()
                    .then((snapshot) => {
                        messagesContainer.innerHTML = '';

                        if (snapshot.empty) {
                            messagesContainer.innerHTML = '<div class="no-messages">No messages yet. Be the first to send a message!</div>';
                            return;
                        }

                        let allMessages = [];

                        // Process messages
                        snapshot.forEach((doc) => {
                            allMessages.push({
                                id: doc.id,
                                ...doc.data()
                            });
                        });

                        // Sort messages by timestamp
                        allMessages.sort((a, b) => parseInt(a.time) - parseInt(b.time));

                        // Display messages with date headers
                        let lastDate = null;

                        allMessages.forEach(message => {
                            const messageDate = new Date(parseInt(message.time));

                            // Check if we need to add a date divider
                            if (!lastDate || !isSameDay(lastDate, messageDate)) {
                                const dateHeader = `<div class="date-divider" data-date="${messageDate.getTime()}">${formatDateHeader(messageDate)}</div>`;
                                $(messagesContainer).append(dateHeader);
                                lastDate = messageDate;
                            }

                            const isCurrentUser = message.sender && message.sender.id == currentUserId;
                            const messageElement = $(`<div class="message-row ${isCurrentUser ? 'outgoing' : 'incoming'}" data-id="${message.id}" data-timestamp="${message.time}"></div>`);

                            // Format message content based on type
                            let messageContent = '';

                            if (message.fileUrl && message.fileUrl.trim() !== '') {
                                // Handle file messages
                                const fileUrl = message.fileUrl;
                                const fileExtension = fileUrl.split('.').pop().toLowerCase().split('?')[0];
                                const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                                if (imageExtensions.includes(fileExtension)) {
                                    // It's an image
                                    messageContent = `<img src="${fileUrl}" alt="Image" class="chat-image">`;
                                } else {
                                    // It's a file
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

                            // Create message bubble
                            const senderName = message.sender ? message.sender.name : 'Unknown';
                            const timestamp = new Date(parseInt(message.time));
                            const timeStr = timestamp.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

                            // Add sender name for group chats
                            const messageBubble = `
                                                                                                                                                                    <div class="message-bubble">
                                                                                                                                                                        ${!isCurrentUser ? `<div class="message-sender">${senderName}</div>` : ''}
                                                                                                                                                                        <div class="message-text">${messageContent}</div>
                                                                                                                                                                        <div class="message-time">${timeStr}</div>
                                                                                                                                                                    </div>
                                                                                                                                                                `;

                            messageElement.append(messageBubble);
                            $(messagesContainer).append(messageElement);
                        });

                        // Scroll to bottom
                        messagesContainer.scrollTop = messagesContainer.scrollHeight;

                        // Set up real-time listener for new messages
                        setupGroupMessageListener(groupId);
                    })
                    .catch(error => {
                        console.error("Error loading group messages:", error);
                        messagesContainer.innerHTML = '<div class="error-messages">Error loading messages. Please try again.</div>';
                    });
            } catch (e) {
                console.error("Error in loadGroupMessages:", e);
            }
        }

        // Setup group message listener for real-time updates
        function setupGroupMessageListener(groupId) {
            try {
                const db = firebase.firestore();
                const currentUserId = "{{ $currentUser->id }}";
                const messagesContainer = document.getElementById('messages-container');

                // Reference to group messages in Firebase
                const groupMessagesRef = db.collection('group_messages').doc(groupId).collection('chats')
                    .orderBy('time', 'desc')
                    .limit(1);

                // Listen for new messages
                groupMessagesRef.onSnapshot((snapshot) => {
                    snapshot.docChanges().forEach((change) => {
                        if (change.type === 'added') {
                            const message = change.doc.data();
                            const messageId = change.doc.id;

                            // Check if this message is already displayed
                            if (document.querySelector(`[data-id="${messageId}"]`)) {
                                return;
                            }

                            const messageDate = new Date(parseInt(message.time));

                            // Check if we need to add a date divider
                            const lastDateDivider = $(messagesContainer).find('.date-divider:last');
                            const lastMessageTime = lastDateDivider.length > 0 ?
                                new Date(parseInt(lastDateDivider.data('date'))) : null;

                            if (!lastMessageTime || !isSameDay(lastMessageTime, messageDate)) {
                                const dateHeader = `<div class="date-divider" data-date="${messageDate.getTime()}">${formatDateHeader(messageDate)}</div>`;
                                $(messagesContainer).append(dateHeader);
                            }

                            // Display the message
                            const isCurrentUser = message.sender && message.sender.id == currentUserId;
                            const timestamp = messageDate;
                            const timeStr = timestamp.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

                            let messageContent = '';

                            // Handle file attachments if present
                            if (message.fileUrl && message.fileUrl.trim() !== '') {
                                const fileUrl = message.fileUrl;
                                const fileExtension = fileUrl.split('.').pop().toLowerCase().split('?')[0];
                                const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                                if (imageExtensions.includes(fileExtension)) {
                                    // It's an image
                                    messageContent = `<img src="${fileUrl}" alt="Image" class="chat-image">`;
                                } else {
                                    // It's a file
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

                            // Add sender name for group chats
                            const senderName = message.sender ? message.sender.name : 'Unknown';
                            const messageElement = `
                                                                                                                                                                    <div class="message-row ${isCurrentUser ? 'outgoing' : 'incoming'}" data-id="${messageId}" data-timestamp="${message.time}">
                                                                                                                                                                        <div class="message-bubble">
                                                                                                                                                                            ${!isCurrentUser ? `<div class="message-sender">${senderName}</div>` : ''}
                                                                                                                                                                            <div class="message-text">${messageContent}</div>
                                                                                                                                                                            <div class="message-time">${timeStr}</div>
                                                                                                                                                                        </div>
                                                                                                                                                                    </div>
                                                                                                                                                                `;

                            $(messagesContainer).append(messageElement);

                            // Scroll to bottom
                            messagesContainer.scrollTop = messagesContainer.scrollHeight;

                            // Update the group's last message in the sidebar
                            updateGroupLastMessage(groupId, message);
                        }
                    });
                });
            } catch (e) {
                console.error("Error in setupGroupMessageListener:", e);
            }
        }

        // Update group's last message in sidebar
        function updateGroupLastMessage(groupId, message) {
            try {
                const groupElement = document.querySelector(`.group-${groupId}`);
                if (!groupElement) return;

                const previewElement = groupElement.querySelector('.message-preview');
                const timeElement = groupElement.querySelector('.chat-time');

                if (previewElement) {
                    previewElement.textContent = message.text || (message.fileUrl ? 'File shared' : 'New message');
                }

                if (timeElement) {
                    const timestamp = parseInt(message.time);
                    timeElement.textContent = formatTime(new Date(timestamp));
                    timeElement.dataset.timestamp = timestamp;
                }

                // Move the group to the top of the list
                const parentElement = groupElement.parentElement;
                if (parentElement) {
                    parentElement.insertBefore(groupElement, parentElement.firstChild);
                }
            } catch (e) {
                console.error("Error in updateGroupLastMessage:", e);
            }
        }

        // Create a new group in Firebase
        function createFirebaseGroup(groupName, selectedUsers) {
            try {
                const db = firebase.firestore();
                const storage = firebase.storage();
                const currentUserId = "{{ $currentUser->id }}";
                const currentUserName = "{{ $currentUser->name }}";
                const currentUserAvatar = "{{ $currentUser->getFirstMediaUrl('avatar', 'icon') ?: asset('images/default-avatar.png') }}";

                // Create a new group document
                const groupRef = db.collection('groups').doc();
                const groupId = groupRef.key || groupRef.id;

                // Collect user IDs
                const userIds = Array.from(selectedUsers).map(checkbox => checkbox.value);
                // Add current user to the group
                if (!userIds.includes(currentUserId)) {
                    userIds.push(currentUserId);
                }

                // Create group members object
                const members = {};
                userIds.forEach(userId => {
                    members[userId] = true;
                });

                // Create group data
                const groupData = {
                    name: groupName,
                    createdBy: currentUserId,
                    createdAt: Date.now(),
                    updatedAt: Date.now(),
                    members: members,
                    avatar: '/images/default-group.png' // Default avatar path
                };

                // Set group data
                groupRef.set(groupData)
                    .then(() => {
                        // Add group to each user's groups
                        const promises = userIds.map(userId => {
                            return db.collection('user_groups').doc(userId).set({
                                [groupId]: true
                            }, { merge: true });
                        });

                        return Promise.all(promises);
                    })
                    .then(() => {
                        // Add system message that group was created
                        return db.collection('group_messages').doc(groupId).collection('chats').add({
                            text: `Group "${groupName}" created by ${currentUserName}`,
                            sender: {
                                id: 'system',
                                name: 'System'
                            },
                            time: Date.now(),
                            type: 'system'
                        });
                    })
                    .then(() => {
                        // Close modal and refresh groups
                        document.getElementById('group-modal').style.display = 'none';
                        document.getElementById('group-name').value = '';
                        document.querySelectorAll('.user-item input[type="checkbox"]').forEach(checkbox => {
                            checkbox.checked = false;
                        });
                        updateSelectedUsersList();

                        // Switch to groups tab
                        document.querySelector('.tab-btn[data-tab="groups"]').click();

                        // Load the new group chat
                        setTimeout(() => {
                            loadGroupChat(groupId);
                        }, 500);
                    })
                    .catch(error => {
                        console.error("Error creating group:", error);
                        alert('Error creating group. Please try again.');
                    });
            } catch (e) {
                console.error("Error in createFirebaseGroup:", e);
            }
        }

        // Extend sendMessage function to handle group messages
        const originalSendMessage = sendMessage;
        window.sendMessage = function () {
            try {
                const sendButton = document.getElementById('send-message-btn');
                const chatType = sendButton.getAttribute('data-chat-type');

                if (chatType === 'group') {
                    // Send message to group
                    const groupId = sendButton.getAttribute('data-group-id');
                    const messageInput = document.getElementById('message-input');
                    const messageText = messageInput.value.trim();

                    if (!messageText || !groupId) return;

                    const db = firebase.firestore();
                    const currentUserId = "{{ $currentUser->id }}";
                    const timestamp = Date.now();

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

                    // Create sender data
                    const senderData = {
                        auth: true,
                        device_token: "{{ $currentUser->device_token ?? '' }}",
                        id: currentUserId,
                        imageUrl: "{{ $currentUser->profile_image ?? '' }}",
                        name: "{{ $currentUser->name }}",
                        phone_number: "{{ $currentUser->phone_number ?? '' }}"
                    };

                    // Create the message data
                    const messageData = {
                        sender: senderData,
                        text: messageText,
                        time: timestamp,
                        fileUrl: "" // Empty for text messages
                    };

                    // Save to Firestore in the path: /group_messages/[groupId]/chats/[messageID]
                    db.collection('group_messages').doc(groupId).collection('chats').add(messageData)
                        .then((docRef) => {
                            console.log("Group message saved successfully", docRef.id);

                            // Update group's updatedAt timestamp
                            db.collection('groups').doc(groupId).update({
                                updatedAt: timestamp
                            });

                            // Remove temp message (it will be replaced by the real one from the snapshot)
                            $('#' + tempId).remove();
                        })
                        .catch(error => {
                            console.error("Error sending group message:", error);
                            $('#' + tempId + ' .message-time').text('Failed to send');
                        });

                    // Clear input
                    messageInput.value = '';
                } else {
                    // Use the original function for direct messages
                    originalSendMessage();
                }
            } catch (e) {
                console.error("Error in extended sendMessage:", e);
            }
        };

    </script>
@endsection
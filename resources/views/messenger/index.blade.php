@extends('layouts.app')

@section('title', __('WIC Doctor Messenger'))

@push('css')
<meta name="friends-url" content="{{ route('messenger.friends') }}">
@endpush



@section('content_header')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    {{ __('WIC Doctor Messenger') }}
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Messenger') }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@stop

@section('content')
<div class="content" style="position: relative; overflow-x: hidden;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="modern-messenger-container">

                    <!-- Modern Sidebar -->
                    <div class="modern-sidebar">

                        <!-- User Profile Header -->
                        <div class="user-profile-header">
                            <div class="user-info">
                                <div class="text-center">
                                    <img src="{{auth()->user()->getFirstMediaUrl('avatar', 'icon')}}"
                                        style=" width: 60px; height: 60px; object-fit: cover;"
                                        class="profile-user-img img-fluid img-circle" alt="{{auth()->user()->name}}">
                                </div>
                                <div class=" user-details">
                                    <h4 class="user-name">{{ auth()->user()->name }}</h4>

                                </div>
                            </div>
                            <div class="header-actions">

                                <button class="action-btn" onclick="showAddFriendModal()" title="Ajouter un ami">
                                    <i class="fas fa-user-plus"></i>
                                </button>

                            </div>
                        </div>

                        <!-- Search Bar -->
                        <div class="search-container">
                            <div class="search-input-wrapper">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" class="search-input" placeholder="Rechercher une conversation..."
                                    id="searchContacts">
                            </div>
                        </div>

                        <!-- Navigation Tabs -->
                        <div class="modern-tabs">
                            <button class="modern-tab-btn active" data-tab="conversations">
                                <i class="fas fa-comments"></i>
                                <span>Conversations</span>
                                <div class="tab-indicator"></div>
                            </button>
                            <button class="modern-tab-btn" data-tab="friends">
                                <i class="fas fa-user-md"></i>
                                <span>Médecins</span>
                                <div class="tab-indicator"></div>
                            </button>
                            <button class="modern-tab-btn" data-tab="patients">
                                <i class="fas fa-procedures"></i>
                                <span>Patients</span>
                                <div class="tab-indicator"></div>
                            </button>
                            <button class="modern-tab-btn" data-tab="groups">
                                <i class="fas fa-users"></i>
                                <span>Groupes</span>
                                <div class="tab-indicator"></div>
                            </button>
                        </div>

                        <!-- Chat List Container -->
                        <div class="chat-list-container">

                            <!-- Conversations Tab -->
                            <div id="conversations-tab" class="tab-content active">
                                <div class="loading-state">
                                    <div class="loading-spinner"></div>
                                    <p>Chargement des conversations...</p>
                                </div>
                            </div>

                            <!-- Friends Tab -->
                            <div id="friends-tab" class="tab-content">
                                <div class="loading-state">
                                    <div class="loading-spinner"></div>
                                    <p>Chargement des médecins...</p>
                                </div>
                            </div>

                            <!-- Patients Tab -->
                            <div id="patients-tab" class="tab-content">
                                <div class="loading-state">
                                    <div class="loading-spinner"></div>
                                    <p>Chargement des patients...</p>
                                </div>
                            </div>

                            <!-- Groups Tab -->
                            <div id="groups-tab" class="tab-content">
                                <div class="create-group-section">
                                    <button class="create-group-btn" onclick="createNewGroup()">
                                        <i class="fas fa-plus"></i>
                                        <span>Créer un nouveau groupe</span>
                                    </button>
                                </div>
                                <div class="loading-state">
                                    <div class="loading-spinner"></div>
                                    <p>Chargement des groupes...</p>
                                </div>
                            </div>

                        </div>

                    </div>

                    <!-- Modern Chat Area -->
                    <div class="modern-chat-area">

                        <!-- Welcome Screen -->
                        <div id="welcome-screen"
                            class="d-flex flex-column align-items-center justify-content-center h-100">
                            <div class="messenger-welcome-container" style="max-width: 800px; text-align: center;">
                                <h2 class="mb-3" style="color: #053178; font-weight: bold;">Bienvenue dans WIC Courrier
                                    Médical</h2>
                                <p class="lead mb-4" style="color: #4f4b4b;">Votre plateforme sécurisée pour communiquer
                                    avec vos collègues et patients</p>

                                <!-- Feature Cards in Grid Layout with Fixed Width Icons -->
                                <div class="feature-cards-container mb-5">
                                    <div class="row g-4">
                                        <!-- Messagerie instantanée -->
                                        <div class="col-md-6 mb-4">
                                            <div class="feature-card p-3"
                                                style="border-radius: 10px; background-color: #f8fafc; height: 100%; border-left: 4px solid #11b8aa; box-shadow: 0 2px 5px rgba(0,0,0,0.05); transition: all 0.3s ease;">
                                                <div class="d-flex align-items-center">
                                                    <div class="feature-icon-circle me-3"
                                                        style="background-color: #11b8aa; width: 50px; height: 50px; min-width: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-comment-dots"
                                                            style="color: white; font-size: 20px;"></i>
                                                    </div>
                                                    <div class="feature-content text-start">
                                                        <h5
                                                            style="color: #053178; font-weight: 600; margin-bottom: 5px;">
                                                            Messagerie instantanée</h5>
                                                        <p class="mb-0" style="color: #4f4b4b; font-size: 14px;">
                                                            Échangez en temps réel avec vos collègues médecins et vos
                                                            patients</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Partage sécurisé -->
                                        <div class="col-md-6 mb-4">
                                            <div class="feature-card p-3"
                                                style="border-radius: 10px; background-color: #f8fafc; height: 100%; border-left: 4px solid #053178; box-shadow: 0 2px 5px rgba(0,0,0,0.05); transition: all 0.3s ease;">
                                                <div class="d-flex align-items-center">
                                                    <div class="feature-icon-circle me-3"
                                                        style="background-color: #053178; width: 50px; height: 50px; min-width: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-file-medical"
                                                            style="color: white; font-size: 20px;"></i>
                                                    </div>
                                                    <div class="feature-content text-start">
                                                        <h5
                                                            style="color: #053178; font-weight: 600; margin-bottom: 5px;">
                                                            Partage sécurisé</h5>
                                                        <p class="mb-0" style="color: #4f4b4b; font-size: 14px;">
                                                            Partagez des documents et images médicales en toute
                                                            confidentialité</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Discussions de groupe -->
                                        <div class="col-md-6 mb-4">
                                            <div class="feature-card p-3"
                                                style="border-radius: 10px; background-color: #f8fafc; height: 100%; border-left: 4px solid #fd8e26; box-shadow: 0 2px 5px rgba(0,0,0,0.05); transition: all 0.3s ease;">
                                                <div class="d-flex align-items-center">
                                                    <div class="feature-icon-circle me-3"
                                                        style="background-color: #fd8e26; width: 50px; height: 50px; min-width: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-users"
                                                            style="color: white; font-size: 20px;"></i>
                                                    </div>
                                                    <div class="feature-content text-start">
                                                        <h5
                                                            style="color: #053178; font-weight: 600; margin-bottom: 5px;">
                                                            Discussions de groupe</h5>
                                                        <p class="mb-0" style="color: #4f4b4b; font-size: 14px;">Créez
                                                            des groupes pour faciliter les discussions d'équipe</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Notifications en temps réel -->
                                        <div class="col-md-6 mb-4">
                                            <div class="feature-card p-3"
                                                style="border-radius: 10px; background-color: #f8fafc; height: 100%; border-left: 4px solid #A458E3; box-shadow: 0 2px 5px rgba(0,0,0,0.05); transition: all 0.3s ease;">
                                                <div class="d-flex align-items-center">
                                                    <div class="feature-icon-circle me-3"
                                                        style="background-color: #A458E3; width: 50px; height: 50px; min-width: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-bell"
                                                            style="color: white; font-size: 20px;"></i>
                                                    </div>
                                                    <div class="feature-content text-start">
                                                        <h5
                                                            style="color: #053178; font-weight: 600; margin-bottom: 5px;">
                                                            Notifications en temps réel</h5>
                                                        <p class="mb-0" style="color: #4f4b4b; font-size: 14px;">Soyez
                                                            alerté instantanément des nouveaux messages</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Augmenté la marge ici entre les cartes et la boîte d'information -->
                                <div class="getting-started-box p-3 mb-5"
                                    style="background-color: #e1f5fb; border-radius: 10px; border-left: 4px solid #11b8aa; margin-top: 30px;">
                                    <h5 style="color: #053178; font-weight: bold;"><i
                                            class="fas fa-info-circle mr-2"></i> Pour commencer</h5>
                                    <p class="mb-0">Commencez à discuter avec vos collègues et vos patients en naviguant
                                        entre les onglets</p>
                                    entre les onglets</p>
                                </div>

                                <!-- Augmenté la marge ici entre la boîte d'information et les boutons -->
                                <div class="d-flex justify-content-center mt-4">
                                    <a href="https://wiccrm.com/FAQ.html" target="_blank"
                                        class="btn btn-outline-primary me-3">
                                        <i class="fas fa-question-circle me-1"></i> Aide
                                    </a>
                                    <a href="https://wic-doctor.com/inscription-professionnel/wic-courrier-m%C3%A9dical.html"
                                        target="_blank" class="btn btn-primary">
                                        <i class="fas fa-play-circle me-1"></i> Découvrir
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!-- Modern Chat Interface -->
                        <div id="chat-interface" class="modern-chat-interface">

                            <!-- Chat Header -->
                            <div class="modern-chat-header">
                                <div class="chat-contact-info">
                                    <div class="contact-avatar-container">
                                        <img src="/images/default-avatar.png" class="contact-avatar" id="chat-avatar">
                                        <div class="contact-status-indicator"></div>
                                    </div>
                                    <div class="contact-details">
                                        <h4 class="contact-name" id="chat-contact-name">Contact</h4>

                                    </div>
                                </div>
                                <div class="chat-actions">

                                    <button class="chat-action-btn" onclick="toggleChatSidebar()"
                                        title="Médias et liens" id="sidebarToggle">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Messages Container -->
                            <div class="modern-messages-container">
                                <div id="messages-container" class="messages-wrapper">
                                    <!-- Messages will be loaded here -->
                                </div>
                            </div>

                            <!-- Message Input Area -->
                            <div class="modern-message-input">
                                <div class="input-container">
                                    <button class="attach-btn" onclick="showFileUploadOptions()"
                                        title="Joindre un fichier">
                                        <i class="fas fa-paperclip"></i>
                                    </button>
                                    <div class="message-input-wrapper">
                                        <textarea class="message-input" placeholder="Tapez votre message..."
                                            id="messageInput" rows="1"></textarea>
                                        <button class="emoji-btn" onclick="toggleEmojiPicker()" title="Émojis"
                                            id="emojiToggle">
                                            <i class="fas fa-smile"></i>
                                        </button>
                                    </div>
                                    <button class="send-btn" onclick="sendMessage()" title="Envoyer">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>

                                <!-- Emoji Picker -->
                                <div id="emoji-picker" class="emoji-picker">
                                    <div class="emoji-grid">
                                        <span onclick="insertEmoji('😀')">😀</span>
                                        <span onclick="insertEmoji('😃')">😃</span>
                                        <span onclick="insertEmoji('😄')">😄</span>
                                        <span onclick="insertEmoji('😁')">😁</span>
                                        <span onclick="insertEmoji('😆')">😆</span>
                                        <span onclick="insertEmoji('😅')">😅</span>
                                        <span onclick="insertEmoji('😂')">😂</span>
                                        <span onclick="insertEmoji('🤣')">🤣</span>
                                        <span onclick="insertEmoji('😊')">😊</span>
                                        <span onclick="insertEmoji('😇')">😇</span>
                                        <span onclick="insertEmoji('🙂')">🙂</span>
                                        <span onclick="insertEmoji('🙃')">🙃</span>
                                        <span onclick="insertEmoji('😉')">😉</span>
                                        <span onclick="insertEmoji('😌')">😌</span>
                                        <span onclick="insertEmoji('😍')">😍</span>
                                        <span onclick="insertEmoji('🥰')">🥰</span>
                                        <span onclick="insertEmoji('😘')">😘</span>
                                        <span onclick="insertEmoji('😗')">😗</span>
                                        <span onclick="insertEmoji('😙')">😙</span>
                                        <span onclick="insertEmoji('😚')">😚</span>
                                        <span onclick="insertEmoji('😋')">😋</span>
                                        <span onclick="insertEmoji('😛')">😛</span>
                                        <span onclick="insertEmoji('😝')">😝</span>
                                        <span onclick="insertEmoji('😜')">😜</span>
                                        <span onclick="insertEmoji('🤪')">🤪</span>
                                        <span onclick="insertEmoji('🤨')">🤨</span>
                                        <span onclick="insertEmoji('🧐')">🧐</span>
                                        <span onclick="insertEmoji('🤓')">🤓</span>
                                        <span onclick="insertEmoji('😎')">😎</span>
                                        <span onclick="insertEmoji('🤩')">🤩</span>
                                        <span onclick="insertEmoji('🥳')">🥳</span>
                                        <span onclick="insertEmoji('😏')">😏</span>
                                        <span onclick="insertEmoji('😒')">😒</span>
                                        <span onclick="insertEmoji('😞')">😞</span>
                                        <span onclick="insertEmoji('😔')">😔</span>
                                        <span onclick="insertEmoji('😟')">😟</span>
                                        <span onclick="insertEmoji('😕')">😕</span>
                                        <span onclick="insertEmoji('🙁')">🙁</span>
                                        <span onclick="insertEmoji('☹️')">☹️</span>
                                        <span onclick="insertEmoji('😣')">😣</span>
                                        <span onclick="insertEmoji('😖')">😖</span>
                                        <span onclick="insertEmoji('😫')">😫</span>
                                        <span onclick="insertEmoji('😩')">😩</span>
                                        <span onclick="insertEmoji('🥺')">🥺</span>
                                        <span onclick="insertEmoji('😢')">😢</span>
                                        <span onclick="insertEmoji('😭')">😭</span>
                                        <span onclick="insertEmoji('😤')">😤</span>
                                        <span onclick="insertEmoji('😠')">😠</span>
                                        <span onclick="insertEmoji('😡')">😡</span>
                                        <span onclick="insertEmoji('🤬')">🤬</span>
                                        <span onclick="insertEmoji('🤯')">🤯</span>
                                        <span onclick="insertEmoji('😳')">😳</span>
                                        <span onclick="insertEmoji('🥵')">🥵</span>
                                        <span onclick="insertEmoji('🥶')">🥶</span>
                                        <span onclick="insertEmoji('😱')">😱</span>
                                        <span onclick="insertEmoji('😨')">😨</span>
                                        <span onclick="insertEmoji('😰')">😰</span>
                                        <span onclick="insertEmoji('😥')">😥</span>
                                        <span onclick="insertEmoji('😓')">😓</span>
                                        <span onclick="insertEmoji('🤗')">🤗</span>
                                        <span onclick="insertEmoji('🤔')">🤔</span>
                                        <span onclick="insertEmoji('🤭')">🤭</span>
                                        <span onclick="insertEmoji('🤫')">🤫</span>
                                        <span onclick="insertEmoji('🤥')">🤥</span>
                                        <span onclick="insertEmoji('😶')">😶</span>
                                        <span onclick="insertEmoji('😐')">😐</span>
                                        <span onclick="insertEmoji('😑')">😑</span>
                                        <span onclick="insertEmoji('😬')">😬</span>
                                        <span onclick="insertEmoji('🙄')">🙄</span>
                                        <span onclick="insertEmoji('😯')">😯</span>
                                        <span onclick="insertEmoji('😦')">😦</span>
                                        <span onclick="insertEmoji('😧')">😧</span>
                                        <span onclick="insertEmoji('😮')">😮</span>
                                        <span onclick="insertEmoji('😲')">😲</span>
                                        <span onclick="insertEmoji('🥱')">🥱</span>
                                        <span onclick="insertEmoji('😴')">😴</span>
                                        <span onclick="insertEmoji('🤤')">🤤</span>
                                        <span onclick="insertEmoji('😪')">😪</span>
                                        <span onclick="insertEmoji('😵')">😵</span>
                                        <span onclick="insertEmoji('🤐')">🤐</span>
                                        <span onclick="insertEmoji('🥴')">🥴</span>
                                        <span onclick="insertEmoji('🤢')">🤢</span>
                                        <span onclick="insertEmoji('🤮')">🤮</span>
                                        <span onclick="insertEmoji('🤧')">🤧</span>
                                        <span onclick="insertEmoji('😷')">😷</span>
                                        <span onclick="insertEmoji('🤒')">🤒</span>
                                        <span onclick="insertEmoji('🤕')">🤕</span>
                                        <span onclick="insertEmoji('🤑')">🤑</span>
                                        <span onclick="insertEmoji('🤠')">🤠</span>
                                        <span onclick="insertEmoji('👍')">👍</span>
                                        <span onclick="insertEmoji('👎')">👎</span>
                                        <span onclick="insertEmoji('👌')">👌</span>
                                        <span onclick="insertEmoji('✌️')">✌️</span>
                                        <span onclick="insertEmoji('🤞')">🤞</span>
                                        <span onclick="insertEmoji('🤟')">🤟</span>
                                        <span onclick="insertEmoji('🤘')">🤘</span>
                                        <span onclick="insertEmoji('🤙')">🤙</span>
                                        <span onclick="insertEmoji('👈')">👈</span>
                                        <span onclick="insertEmoji('👉')">👉</span>
                                        <span onclick="insertEmoji('👆')">👆</span>
                                        <span onclick="insertEmoji('🖕')">🖕</span>
                                        <span onclick="insertEmoji('👇')">👇</span>
                                        <span onclick="insertEmoji('☝️')">☝️</span>
                                        <span onclick="insertEmoji('👏')">👏</span>
                                        <span onclick="insertEmoji('🙌')">🙌</span>
                                        <span onclick="insertEmoji('👐')">👐</span>
                                        <span onclick="insertEmoji('🤲')">🤲</span>
                                        <span onclick="insertEmoji('🤝')">🤝</span>
                                        <span onclick="insertEmoji('🙏')">🙏</span>
                                        <span onclick="insertEmoji('❤️')">❤️</span>
                                        <span onclick="insertEmoji('🧡')">🧡</span>
                                        <span onclick="insertEmoji('💛')">💛</span>
                                        <span onclick="insertEmoji('💚')">💚</span>
                                        <span onclick="insertEmoji('💙')">💙</span>
                                        <span onclick="insertEmoji('💜')">💜</span>
                                        <span onclick="insertEmoji('🖤')">🖤</span>
                                        <span onclick="insertEmoji('🤍')">🤍</span>
                                        <span onclick="insertEmoji('🤎')">🤎</span>
                                        <span onclick="insertEmoji('💔')">💔</span>
                                        <span onclick="insertEmoji('❣️')">❣️</span>
                                        <span onclick="insertEmoji('💕')">💕</span>
                                        <span onclick="insertEmoji('💞')">💞</span>
                                        <span onclick="insertEmoji('💓')">💓</span>
                                        <span onclick="insertEmoji('💗')">💗</span>
                                        <span onclick="insertEmoji('💖')">💖</span>
                                        <span onclick="insertEmoji('💘')">💘</span>
                                        <span onclick="insertEmoji('💝')">💝</span>
                                        <span onclick="insertEmoji('💟')">💟</span>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                    <!-- Right Sidebar for Media and Links - Inside the messenger container -->
                    <div id="chat-sidebar" class="chat-sidebar">
                        <div class="sidebar-header">
                            <h5 class="sidebar-title">Médias et Liens</h5>
                            <button class="sidebar-close-btn" onclick="closeChatSidebar()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="sidebar-content">
                            <!-- Contact Info Section -->
                            <div class="sidebar-section">
                                <div class="section-header">
                                    <h6><i class="fas fa-user mr-2"></i>Informations</h6>
                                </div>
                                <div class="contact-details-sidebar">
                                    <img src="/images/default-avatar.png" class="sidebar-contact-avatar"
                                        id="sidebar-avatar">
                                    <h6 class="sidebar-contact-name" id="sidebar-contact-name">Contact</h6>
                                </div>
                            </div>

                            <!-- Shared Media Section -->
                            <div class="sidebar-section">
                                <div class="section-header">
                                    <h6><i class="fas fa-images mr-2"></i>Images partagées</h6>
                                    <span class="media-count" id="images-count">0</span>
                                </div>
                                <div class="media-grid" id="shared-images">
                                    <!-- Shared images will be loaded here -->
                                </div>
                            </div>

                            <!-- Shared Files Section -->
                            <div class="sidebar-section">
                                <div class="section-header">
                                    <h6><i class="fas fa-file mr-2"></i>Fichiers partagés</h6>
                                    <span class="media-count" id="files-count">0</span>
                                </div>
                                <div class="files-list" id="shared-files">
                                    <!-- Shared files will be loaded here -->
                                </div>
                            </div>

                            <!-- Shared Links Section -->
                            <div class="sidebar-section">
                                <div class="section-header">
                                    <h6><i class="fas fa-link mr-2"></i>Liens partagés</h6>
                                    <span class="media-count" id="links-count">0</span>
                                </div>
                                <div class="links-list" id="shared-links">
                                    <!-- Shared links will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Backdrop for mobile sidebar -->
                    <div id="sidebar-backdrop" class="sidebar-backdrop" onclick="closeChatSidebar()"></div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Group Modal -->
<div class="modal fade" id="createGroupModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modern-modal">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-users mr-2"></i>
                    Créer un nouveau groupe
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="createGroupForm">
                    <div class="form-group">
                        <label class="form-label">Nom du groupe:</label>
                        <input type="text" class="form-control modern-input" id="groupName"
                            placeholder="Entrez le nom du groupe" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ajouter des membres:</label>
                        <div id="friendsList" class="friends-selection">
                            <!-- Friends will be loaded here -->
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary modern-btn" onclick="submitCreateGroup()">
                    <i class="fas fa-plus mr-2"></i>
                    Créer le groupe
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@push('scripts')
    <script>
        // Global variables
        let currentConversationId = null;
        let currentUserId = {{ auth()->user()->id }};
        let conversations = [];
        let friends = [];
        let patients = [];
        let groups = [];

        // 🔔 Unread message tracking with localStorage persistence
        let unreadCounts = new Map();
        let totalUnreadCount = 0;
        let isUnreadCountsInitialized = false; // New flag to prevent reinitialization
        let processedMessages = new Set(); // Still used by listener to avoid reprocessing


        // 🔔 Load unread counts from localStorage
        function loadUnreadCountsFromStorage() {
            try {
                const stored = localStorage.getItem('wic_messenger_unread_counts');
                if (stored) {
                    const data = JSON.parse(stored);
                    unreadCounts = new Map(Object.entries(data));
                    console.log('📥 Loaded unread counts from storage:', Object.fromEntries(unreadCounts));
                }
            } catch (error) {
                console.warn('⚠️ Error loading unread counts from storage:', error);
                unreadCounts = new Map();
            }
        }

        // 🔔 Save unread counts to localStorage
        function saveUnreadCountsToStorage() {
            try {
                const data = Object.fromEntries(unreadCounts);
                localStorage.setItem('wic_messenger_unread_counts', JSON.stringify(data));
                console.log('💾 Saved unread counts to storage:', data);
            } catch (error) {
                console.warn('⚠️ Error saving unread counts to storage:', error);
            }
        }

        // 🔔 Initialize unread counts on page load
        loadUnreadCountsFromStorage();

        // 🔔 Unread count functions
        function incrementUnreadCount(conversationId) {
            console.log('🔔 Incrementing unread count for conversation:', conversationId);
            // Ensure unreadCounts is initialized
            if (!window.unreadCounts) {
                window.unreadCounts = unreadCounts;
            }

            if (currentConversationId === conversationId) return; // Don't count if viewing conversation
            const count = unreadCounts.get(conversationId) || 0;
            unreadCounts.set(conversationId, count + 1);
            updateUnreadBadges();
            saveUnreadCountsToStorage(); // Persist to localStorage
        }

        function clearUnreadCount(conversationId) {
            // Ensure unreadCounts is initialized
            if (!window.unreadCounts) {
                window.unreadCounts = unreadCounts;
            }

            unreadCounts.set(conversationId, 0);
            updateUnreadBadges();
            saveUnreadCountsToStorage(); // Persist to localStorage
        }

        function updateUnreadBadges() {
            // Calculate total unread count
            totalUnreadCount = 0;
            unreadCounts.forEach(count => totalUnreadCount += count);

            // Update conversations tab badge
            const conversationsTab = document.querySelector('[data-tab="conversations"]');
            if (conversationsTab) {
                let tabBadge = conversationsTab.querySelector('.tab-unread-badge');
                if (totalUnreadCount > 0) {
                    if (!tabBadge) {
                        tabBadge = document.createElement('span');
                        tabBadge.className = 'badge badge-danger tab-unread-badge';
                        tabBadge.style.cssText = 'position:absolute;top:-8px;right:-8px;min-width:20px;height:20px;border-radius:10px;font-size:11px;line-height:20px;text-align:center;';
                        conversationsTab.style.position = 'relative';
                        conversationsTab.appendChild(tabBadge);
                    }
                    tabBadge.textContent = totalUnreadCount > 99 ? '99+' : totalUnreadCount;
                    tabBadge.style.display = 'inline-block';
                } else if (tabBadge) {
                    tabBadge.style.display = 'none';
                }
            }

            // Update individual conversation badges
            updateConversationBadges();
        }

        function updateConversationBadges() {
            // Add unread count to conversation objects for re-rendering
            if (conversations && Array.isArray(conversations)) {
                conversations.forEach(conv => {
                    conv.unread_count = unreadCounts.get(conv.id) || 0;
                });
                // displayConversations(conversations);
            }
        }

        // 🔔 Global message listener
        // 🔔 Global message listener with real-time Firestore (with retry)
        function startGlobalMessageListener() {
            console.log('🔔 Starting global message listener with real-time updates...');
            if (window.globalMessageInterval) {
                clearInterval(window.globalMessageInterval);
                window.globalMessageInterval = null;
            }
            if (window.notificationPollingInterval) {
                clearInterval(window.notificationPollingInterval);
                window.notificationPollingInterval = null;
            }
            const userId = {{ auth()->user()->id }};
            let lastCheckTime = window.lastCheckTime || Date.now(); // Use value from loadConversations or fallback to now

            if (!window.processedMessages) {
                window.processedMessages = new Set();
            }

            if (!window.firebaseDb || !conversations) return;
            const db = window.firebaseDb;

            const setupListeners = () => {
                conversations.forEach(conversation => {
                    const conversationId = conversation.id;
                    if (currentConversationId === conversationId) return; // Skip current conversation

                    const isGroupConversation = !conversationId.includes('-') || conversationId.length > 10;
                    const chatsRef = isGroupConversation
                        ? db.collection('groups').doc(conversationId).collection('messages').orderBy('timestamp', 'asc')
                        : db.collection('messages').doc(conversationId).collection('chats').orderBy('time', 'asc');

                    setupListenerWithRetry(chatsRef, snapshot => {
                        console.log('🔔 Update for conversation:', conversationId);
                        snapshot.docChanges().forEach(change => {
                            if (change.type === 'added') {
                                const messageData = change.doc.data();
                                const messageId = change.doc.id;
                                const timestamp = isGroupConversation ? (messageData.timestamp || 0) : parseInt(messageData.time || 0);
                                if (window.processedMessages.has(messageId) || timestamp <= lastCheckTime) {
                                    console.log('⏭️ Message already processed or too old:', messageId, timestamp);
                                    return;
                                }

                                // Check if userId is part of the conversation
                                const senderId = messageData.sender?.id;
                                const receiverId = messageData.receiver?.id;
                                if (!senderId || !receiverId || (senderId !== userId.toString() && receiverId !== userId.toString())) {
                                    console.log('⚠️ Message not for you:', conversationId, 'sender:', senderId, 'receiver:', receiverId);
                                    return;
                                }

                                if (receiverId === userId.toString() &&
                                    senderId !== userId.toString() &&
                                    (messageData.status === 'sent' || messageData.status === 'delivered')) {
                                    console.log('📨 New message detected:', {
                                        from: messageData.sender?.name,
                                        message: messageData.text?.substring(0, 50),
                                        conversationId,
                                        messageId,
                                        status: messageData.status
                                    });
                                    window.processedMessages.add(messageId);
                                    incrementUnreadCount(conversationId);
                                    const existingConv = conversations.find(c => c.id === conversationId);
                                    if (existingConv) {
                                        existingConv.last_message = messageData.text || 'New message';
                                        existingConv.last_message_time = new Date(timestamp).toLocaleString();
                                        existingConv.timestamp = timestamp;
                                        conversations.sort((a, b) => b.timestamp - a.timestamp);
                                        refreshConversationItem(existingConv);
                                        console.log('🔄 Refreshed conversation item:', conversationId, 'DOM state:', document.getElementById('conversations-container')?.innerHTML);
                                    }
                                    showNewMessageNotification(messageData, conversationId);
                                    lastCheckTime = Math.max(lastCheckTime, timestamp); // Update for next update
                                }
                            }
                        });
                    }, `conversationListener_${conversationId}`);
                });
            };

            setupListeners(); // Set up listeners once
            console.log('✅ Global message listener started with real-time updates');
        }
        function setupListenerWithRetry(ref, onNext, listenerName) {
            const maxRetries = 3;
            let retryCount = 0;

            const attemptListen = () => {
                const unsubscribe = ref.onSnapshot(
                    onNext,
                    error => {
                        console.error(`❌ ${listenerName} error:`, error);
                        if (retryCount < maxRetries) {
                            retryCount++;
                            console.log(`🔄 Retrying ${listenerName} (Attempt ${retryCount}/${maxRetries})...`);
                            setTimeout(attemptListen, 1000 * retryCount); // Retry with 1s, 2s, 3s delay
                        } else {
                            console.error(`❌ ${listenerName} failed after ${maxRetries} retries`);
                        }
                    }
                );
                return unsubscribe;
            };

            const unsubscribe = attemptListen();

            // Store the unsubscribe function
            if (!window.conversationListeners) {
                window.conversationListeners = new Map();
            }
            window.conversationListeners.set(listenerName, unsubscribe);
            console.log(`✅ ${listenerName} set up with retry`);
        }

        // 🔔 Real-time Firestore listener for immediate notifications (simplified)
        function setupRealtimeGlobalListener() {
            // Real-time listener disabled to prevent duplication with polling
            console.log('🔄 Real-time listener disabled to prevent duplicate notifications');
            // Note: startGlobalMessageListener() now handles all message detection
        }

        // 🔔 Polling notifications (now integrated into startGlobalMessageListener)
        function setupPollingNotifications() {
            // This function is now integrated into startGlobalMessageListener to prevent duplication
            console.log('🔄 Polling notifications integrated into main listener to prevent duplicates');
        }

        // Initialize messenger when DOM and Firebase are ready
        document.addEventListener('DOMContentLoaded', function () {
            console.log('🚀 WIC Messenger DOM loaded - Initializing Firebase...');

            // Wait a moment for Firebase scripts to load, then initialize
            setTimeout(function () {
                if (typeof firebase !== 'undefined') {
                    console.log('✅ Firebase SDK detected, initializing...');
                    initializeFirebase();
                } else {
                    console.error('❌ Firebase SDK not loaded!');
                    console.log('🔧 Retrying Firebase initialization in 1 second...');
                    setTimeout(function () {
                        if (typeof firebase !== 'undefined') {
                            initializeFirebase();
                        } else {
                            console.error('❌ Firebase SDK still not available after retry');
                        }
                    }, 1000);
                }

                // Initialize messenger interface
                initializeMessenger();
            }, 100);
        });

        // 🔥 Firebase Initialization Function with QUIC fix
        function initializeFirebase() {
            // Check if Firebase is already initialized to prevent conflicts
            if (!window.firebase || !window.firebase.apps.length) {

                // Firebase Configuration for WIC Doctor Messenger
                const firebaseConfig = {
                    apiKey: "AIzaSyCONylt3t8MDw_02k5H9ceXTEmdtxmQtu8",
                    authDomain: "wic-doctor-b83e0.firebaseapp.com",
                    databaseURL: "https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app",
                    projectId: "wic-doctor-b83e0",
                    storageBucket: "wic-doctor-b83e0.firebasestorage.app",
                    messagingSenderId: "895957208558",
                    appId: "1:895957208558:web:322c25347af966f5f512ff",
                    measurementId: "G-J2RKG7ZXE5"
                };

                console.log('🔧 Firebase config:', firebaseConfig);

                // Initialize Firebase only if configuration is complete
                if (firebaseConfig.apiKey && firebaseConfig.messagingSenderId) {
                    try {
                        firebase.initializeApp(firebaseConfig);
                        console.log('🔥 WIC Doctor Firebase initialized successfully');

                        // Initialize Firestore (not Realtime Database)
                        if (typeof firebase.firestore === 'function') {
                            window.firebaseDb = firebase.firestore();
                            console.log('✅ Firebase Firestore initialized');

                            // 🧪 Test Firestore connection immediately
                            console.log('🧪 Testing Firestore connection...');
                            // testFirestoreConnection(); // Disabled to avoid duplicate logs
                        } else {
                            console.error('❌ Firebase Firestore not available');
                        }

                        // Initialize Storage
                        if (typeof firebase.storage === 'function') {
                            window.firebaseStorage = firebase.storage();
                            console.log('✅ Firebase Storage initialized');
                        }

                        // 🔥 Initialize messaging AFTER user authentication
                        if (typeof firebase.messaging === 'function') {
                            // Get current Laravel user
                            const currentUser = @json(auth()->user());

                            if (currentUser && currentUser.id) {
                                console.log('👤 Current user ID:', currentUser.id);

                                // 🔥 Initialize messaging directly (no Firebase Auth needed)
                                try {
                                    window.firebaseMessaging = firebase.messaging();
                                    console.log('💬 Firebase Cloud Messaging initialized with user:', currentUser.id);

                                    // Set up messaging with proper sender context
                                    setupFirebaseMessaging(currentUser);
                                } catch (msgError) {
                                    console.error('❌ Error initializing messaging:', msgError);
                                }
                            } else {
                                console.error('❌ No current user found, cannot initialize messaging properly');
                            }
                        }
                    } catch (error) {
                        console.error('❌ Firebase initialization error:', error);
                    }
                } else {
                    console.error('❌ Firebase configuration incomplete. Missing apiKey or messagingSenderId.');
                }

            } else {
                console.log('ℹ️ Firebase already initialized, using existing instance');

                // If Firebase is already initialized, just setup messaging for current user
                const currentUser = @json(auth()->user());
                if (currentUser && typeof firebase.messaging === 'function') {
                    try {
                        window.firebaseMessaging = firebase.messaging();
                        setupFirebaseMessaging(currentUser);
                        console.log('💬 Using existing Firebase, messaging setup completed');
                    } catch (error) {
                        console.error('❌ Error setting up messaging on existing Firebase:', error);
                    }
                }

                // Test Firestore if it's available
                if (window.firebaseDb) {
                    // testFirestoreConnection(); // Disabled to avoid duplicate logs
                }
            }
        }

        // 🧪 Test Firestore connection
        async function testFirestoreConnection() {
            try {
                console.log('🧪 Testing Firestore connection with specific document...');
                const db = window.firebaseDb;

                // Try to access the specific conversation we know exists
                console.log('🔍 Testing access to: messages/586-433');
                const testDoc = await db.collection('messages').doc('586-433').get();
                console.log('📄 Test document exists:', testDoc.exists);
                if (testDoc.exists) {
                    console.log('📄 Test document data:', testDoc.data());
                }

                // Try to list all documents in messages collection
                console.log('📋 Listing all documents in messages collection...');
                const allDocs = await db.collection('messages').get();
                console.log('📋 Total documents found:', allDocs.size);
                allDocs.forEach((doc) => {
                    console.log('📄 Document ID:', doc.id, 'Data:', doc.data());
                });

                // Try to access the chats subcollection directly
                console.log('💬 Testing access to: messages/586-433/chats');
                const chatsSnapshot = await db.collection('messages').doc('586-433').collection('chats').get();
                console.log('💬 Chats found:', chatsSnapshot.size);
                chatsSnapshot.forEach((chatDoc) => {
                    console.log('💬 Chat ID:', chatDoc.id, 'Data:', chatDoc.data());
                });

            } catch (error) {
                console.error('❌ Firestore connection test failed:', error);
                console.error('❌ Error code:', error.code);
                console.error('❌ Error message:', error.message);
            }
        }

        // 🔥 Setup Firebase Messaging with proper user context
        function setupFirebaseMessaging(currentUser) {
            if (!window.firebaseMessaging) {
                console.warn('⚠️ Firebase messaging not available');
                return;
            }

            try {
                // Set user context for messaging
                window.messengerUser = {
                    id: currentUser.id,
                    name: currentUser.name + ' ' + (currentUser.lastname || ''),
                    email: currentUser.email,
                    firebase_uid: null // Will be set after auth
                };

                console.log('👤 Messenger user context set:', window.messengerUser);

                // 🔥 Skip notification permissions for now - just setup messaging
                console.log('💬 Firebase messaging setup completed successfully');

                // Save FCM token to Laravel backend with retry mechanism
                let saveTokenRetries = 0;
                const maxTokenRetries = 3;

                async function saveTokenToBackend(token) {
                    try {
                        const response = await fetch('/messenger/save-fcm-token', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                token: token,
                                user_id: currentUser.id
                            })
                        });

                        if (response.ok) {
                            const result = await response.json();
                            console.log('✅ FCM token saved successfully:', result);
                            return true;
                        } else {
                            console.error('❌ Failed to save FCM token:', response.status, response.statusText);
                            return false;
                        }
                    } catch (error) {
                        console.error('❌ Error saving FCM token:', error);
                        return false;
                    }
                }

                // Function to save token with retry
                async function saveTokenWithRetry(token) {
                    const success = await saveTokenToBackend(token);
                    if (!success && saveTokenRetries < maxTokenRetries) {
                        saveTokenRetries++;
                        console.log(`🔄 Retrying FCM token save (${saveTokenRetries}/${maxTokenRetries})...`);
                        setTimeout(() => saveTokenWithRetry(token), 1000 * saveTokenRetries);
                    }
                }

                // Try to get FCM token without forcing notification permission
                console.log('🔔 Attempting to get FCM token...');
                window.firebaseMessaging.getToken({ vapidKey: 'BH8Xj8_1NF5ZtSLZRxBu7FHJ_7Q4I3F1s1RH_1wN7L4hP_qE3I8Y1Ds7L9Q_4H_1s3P4D_1sN_5Tx4H_1' })
                    .then((currentToken) => {
                        if (currentToken) {
                            console.log('🎯 FCM Token obtained:', currentToken.substring(0, 20) + '...');
                            saveTokenWithRetry(currentToken);
                        } else {
                            console.log('📵 No FCM token available (notifications not permitted or supported)');
                        }
                    }).catch((err) => {
                        console.warn('⚠️ Unable to get FCM token:', err);
                    });

            } catch (error) {
                console.error('❌ Error in Firebase messaging setup:', error);
            }
        }

        function initializeMessenger() {
            console.log('🚀 WIC Messenger loaded successfully!');
            testAuthentication().then(() => {
                setupTabSwitching();
                loadConversations();
                setupMessageInput();
                setupSearch();
            });
        }

        // 🧪 Test API authentication
        async function testAuthentication() {
            try {
                const response = await fetch('/messenger/test-auth', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    credentials: 'same-origin'
                });

                if (response.ok) {
                    const data = await response.json();
                    console.log('✅ Authentication test successful:', data);
                    return true;
                } else {
                    console.error('❌ Authentication test failed:', response.status, response.statusText);
                    return false;
                }
            } catch (error) {
                console.error('❌ Authentication test error:', error);
                return false;
            }
        }

        function setupTabSwitching() {
            document.querySelectorAll('.modern-tab-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const tabName = this.dataset.tab;

                    console.log('🔄 Switching to tab:', tabName);

                    // 🔄 Add loading state to clicked button
                    const originalIcon = this.querySelector('i').className;
                    const originalText = this.querySelector('span').textContent;

                    // Show loading on button temporarily
                    this.querySelector('i').className = 'fas fa-spinner fa-spin';
                    this.style.pointerEvents = 'none'; // Prevent multiple clicks

                    // Update active tab button
                    document.querySelectorAll('.modern-tab-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    // Show/hide tab content
                    document.querySelectorAll('.tab-content').forEach(content => {
                        content.classList.remove('active');
                    });
                    document.getElementById(tabName + '-tab').classList.add('active');

                    // Load data for the selected tab
                    loadTabData(tabName);

                    // 🔄 Restore button state after a short delay
                    setTimeout(() => {
                        this.querySelector('i').className = originalIcon;
                        this.querySelector('span').textContent = originalText;
                        this.style.pointerEvents = 'auto';
                    }, 1000); // 1 second loading indication
                });
            });
        }

        function loadTabData(tabName) {
            console.log('🔄 Loading data for tab:', tabName);

            // Show loading indicator for the active tab
            showTabLoadingIndicator(tabName);

            switch (tabName) {
                case 'conversations':
                    loadConversations();
                    break;
                case 'friends':
                    loadFriends();
                    break;
                case 'patients':
                    loadPatients();
                    break;
                case 'groups':
                    loadGroups();
                    break;
            }
        }

        // Show Add Friend Modal from header
        function showAddFriendModal() {
            // Create and show a modal for adding friends
            const modalHtml = `
                                                                                                                                                    <div class="modal fade" id="addFriendModal" tabindex="-1">
                                                                                                                                                        <div class="modal-dialog">
                                                                                                                                                            <div class="modal-content themed-modal">
                                                                                                                                                                <div class="modal-header" style="background-color: #1A2A44; color: #FFFFFF;">
                                                                                                                                                                    <h5 class="modal-title">
                                                                                                                                                                        <i class="fas fa-user-plus mr-2"></i>
                                                                                                                                                                        Ajouter un ami
                                                                                                                                                                    </h5>
                                                                                                                                                                    <button type="button" class="close" data-dismiss="modal">
                                                                                                                                                                        <span style="color: #FFFFFF;">×</span>
                                                                                                                                                                    </button>
                                                                                                                                                                </div>
                                                                                                                                                                <div class="modal-body" style="background-color: #F5F7FA;">
                                                                                                                                                                    <div class="search-user-container">
                                                                                                                                                                        <label class="form-label" style="color: #1A2A44;">Rechercher par email:</label>
                                                                                                                                                                        <div class="email-search-wrapper">
                                                                                                                                                                            <input type="email" class="email-search-input themed-input"
                                                                                                                                                                                placeholder="email@exemple.com" id="modalSearchEmail">
                                                                                                                                                                            <button class="search-user-btn themed-button" onclick="searchUserByEmailModal()">
                                                                                                                                                                                <i class="fas fa-search"></i>
                                                                                                                                                                            </button>
                                                                                                                                                                        </div>
                                                                                                                                                                    </div>
                                                                                                                                                                    <div id="modal-search-results" class="search-results mt-3"></div>
                                                                                                                                                                </div>
                                                                                                                                                            </div>
                                                                                                                                                    </div>
                                                                                                                                                </div>
                                                                                                                                                `;

            // Remove existing modal if any
            const existingModal = document.getElementById('addFriendModal');
            if (existingModal) {
                existingModal.remove();
            }

            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', modalHtml);

            // Show modal
            $('#addFriendModal').modal('show');
        }

        // Show loading indicator for specific tab
        function showTabLoadingIndicator(tabName) {
            const tabContent = document.getElementById(tabName + '-tab');
            if (!tabContent) return;

            let loadingHTML = '';

            switch (tabName) {
                case 'conversations':
                    loadingHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="text-center p-4">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="spinner-border text-primary" role="status">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <span class="sr-only">Chargement...</span>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <p class="mt-2">Chargement des conversations...</p>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                `;
                    break;
                case 'friends':
                    loadingHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="text-center p-4">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="spinner-border text-primary" role="status">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <span class="sr-only">Chargement...</span>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <p class="mt-2">Chargement des médecins amis...</p>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                `;
                    break;
                case 'patients':
                    loadingHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="text-center p-4">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="spinner-border text-primary" role="status">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <span class="sr-only">Chargement...</span>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <p class="mt-2">Chargement des patients...</p>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                `;
                    break;
                case 'groups':
                    loadingHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="p-3">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <button class="btn btn-primary btn-sm w-100 mb-3" onclick="createNewGroup()">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <i class="fas fa-plus mr-2"></i>Créer un groupe
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </button>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="text-center p-4">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="spinner-border text-primary" role="status">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <span class="sr-only">Chargement...</span>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <p class="mt-2">Chargement des groupes...</p>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                `;
                    break;
                case 'add-friend':
                    // No loading needed for add-friend tab
                    return;
            }

            if (loadingHTML) {
                tabContent.innerHTML = loadingHTML;
            }
        }

        // 🔔 Calculate unread messages for a conversation based on message status
        async function calculateUnreadCount(conversationId) {
            try {
                if (!window.firebaseDb) return 0;

                const userId = {{ auth()->user()->id }};
                const db = window.firebaseDb;

                // Query messages where current user is receiver and status is not 'read'
                const query = db.collection('messages')
                    .doc(conversationId)
                    .collection('chats')
                    .where('receiver.id', '==', userId.toString())
                    .orderBy('time', 'desc')
                    .limit(50); // Check last 50 messages

                const snapshot = await query.get();
                let unreadCount = 0;

                snapshot.forEach(doc => {
                    const messageData = doc.data();
                    // Count messages that are sent/delivered but not read
                    if (messageData.status === 'sent' || messageData.status === 'delivered') {
                        unreadCount++;
                    }
                });

                console.log(`📊 Conversation ${conversationId}: ${unreadCount} unread messages`);
                return unreadCount;
            } catch (error) {
                console.warn('⚠️ Error calculating unread count for', conversationId, error);
                return 0;
            }
        }

        // 🔔 Initialize unread counts for all conversations
        async function initializeUnreadCounts(conversations) {
            if (isUnreadCountsInitialized) {
                console.log('✅ Unread counts already initialized, skipping recalculation');
                updateUnreadBadges();
                return Promise.resolve(Array.from(unreadCounts.entries()).map(([id, count]) => ({ conversationId: id, count })));
            }

            console.log('🔄 Initializing unread counts for conversations...');

            // Merge with stored counts, only recalculate if no stored data or forced refresh
            const storedCounts = new Map(unreadCounts);

            const unreadPromises = conversations.map(async (conv) => {
                const storedCount = storedCounts.get(conv.id) || 0;
                if (storedCount > 0) {
                    // Verify stored count with Firestore
                    const actualCount = await calculateUnreadCount(conv.id);
                    if (actualCount > 0) {
                        unreadCounts.set(conv.id, actualCount);
                        conv.unread_count = actualCount;
                    } else {
                        unreadCounts.set(conv.id, 0);
                        conv.unread_count = 0;
                    }
                } else {
                    const count = await calculateUnreadCount(conv.id);
                    if (count > 0) {
                        unreadCounts.set(conv.id, count);
                        conv.unread_count = count;
                    } else {
                        conv.unread_count = 0;
                    }
                }
                return { conversationId: conv.id, count: unreadCounts.get(conv.id) || 0 };
            });

            const results = await Promise.all(unreadPromises);

            // Update badges and save to storage
            updateUnreadBadges();
            saveUnreadCountsToStorage();
            isUnreadCountsInitialized = true;

            console.log('✅ Unread counts initialized:', results);
            return results;
        }

        // Load Conversations from Firebase using efficient collectionGroup approach (like mobile app)
        async function loadConversations() {
            try {
                console.log('🔄 Loading conversations using efficient collectionGroup approach...');
                if (!window.friends || !window.patients) {
                    console.log('🔄 Preloading friends and patients data for conversations...');
                    const preloadPromises = [];
                    if (!window.friends) preloadPromises.push(loadFriendsData());
                    if (!window.patients) preloadPromises.push(loadPatientsData());
                    await Promise.all(preloadPromises);
                }
                if (!window.firebaseDb) {
                    console.error('❌ Firebase Firestore not initialized');
                    document.getElementById('conversations-tab').innerHTML =
                        '<div class="text-center p-4"><p class="text-muted">Firebase non initialisé</p></div>';
                    return;
                }
                const userId = {{ auth()->user()->id }};
                const db = window.firebaseDb;
                const chatsSnapshot = await db.collectionGroup('chats')
                    .where('sender.id', '==', userId.toString())
                    .orderBy('time', 'desc')
                    .get();
                const receivedChatsSnapshot = await db.collectionGroup('chats')
                    .where('receiver.id', '==', userId.toString())
                    .orderBy('time', 'desc')
                    .get();
                const allMessages = [];
                chatsSnapshot.forEach((doc) => {
                    const messageData = doc.data();
                    messageData.doc_id = doc.id;
                    messageData.doc_ref = doc.ref.path;
                    allMessages.push(messageData);
                });
                receivedChatsSnapshot.forEach((doc) => {
                    const messageData = doc.data();
                    messageData.doc_id = doc.id;
                    messageData.doc_ref = doc.ref.path;
                    allMessages.push(messageData);
                });
                const latestMessages = new Map();
                let latestTimestamp = 0;
                allMessages.forEach((messageData) => {
                    if (!messageData.sender || !messageData.receiver || !messageData.sender.id || !messageData.receiver.id) {
                        console.warn('⚠️ Skipping message with missing sender/receiver data:', messageData);
                        return;
                    }
                    if (messageData.sender.id === userId.toString() && messageData.receiver.id === userId.toString()) return;
                    const otherUserId = messageData.sender.id === userId.toString() ? messageData.receiver.id : messageData.sender.id;
                    if (!otherUserId) {
                        console.warn('⚠️ Skipping message with invalid otherUserId:', messageData);
                        return;
                    }
                    if (!messageData.doc_ref) {
                        console.warn('⚠️ Skipping message with missing doc_ref:', messageData);
                        return;
                    }
                    const pathParts = messageData.doc_ref.split('/');
                    if (pathParts.length < 2) {
                        console.warn('⚠️ Skipping message with invalid doc_ref format:', messageData.doc_ref);
                        return;
                    }
                    const conversationId = pathParts[1];
                    const timestamp = parseInt(messageData.time || 0);
                    if (!latestMessages.has(otherUserId) || timestamp > parseInt(latestMessages.get(otherUserId).time || 0)) {
                        latestMessages.set(otherUserId, {
                            ...messageData,
                            conversation_id: conversationId,
                            other_user_id: otherUserId
                        });
                    }
                    latestTimestamp = Math.max(latestTimestamp, timestamp); // Track latest timestamp
                });
                const conversationsArray = [];
                latestMessages.forEach((latestMessage, otherUserId) => {
                    try {
                        const otherUserInfo = latestMessage.sender && latestMessage.sender.id === userId.toString()
                            ? latestMessage.receiver
                            : latestMessage.sender;
                        if (!otherUserInfo || !otherUserInfo.id) {
                            console.warn('⚠️ Skipping conversation with invalid otherUserInfo:', latestMessage);
                            return;
                        }
                        const lastMessageTime = latestMessage.time ? new Date(parseInt(latestMessage.time)).toLocaleString() : '';
                        const lastMessageText = latestMessage.text || (latestMessage.fileUrl ? 'File shared' : 'Message');
                        const conversationObj = {
                            id: latestMessage.conversation_id,
                            firebase_path: `/messages/${latestMessage.conversation_id}/chats/`,
                            name: otherUserInfo.name || `User ${otherUserInfo.id}`,
                            avatar: otherUserInfo.imageUrl || otherUserInfo.avatar || '/images/default-avatar.png',
                            other_user: {
                                id: otherUserInfo.id,
                                name: otherUserInfo.name || `User ${otherUserInfo.id}`,
                                imageUrl: otherUserInfo.imageUrl,
                                avatar: otherUserInfo.avatar,
                                type: 'firebase_user'
                            },
                            last_message: lastMessageText,
                            last_message_time: lastMessageTime,
                            type: 'firebase_conversation',
                            timestamp: parseInt(latestMessage.time || 0),
                            source: 'collectionGroup_efficient'
                        };
                        conversationsArray.push(conversationObj);
                    } catch (convError) {
                        console.error('❌ Error creating conversation object:', convError, latestMessage);
                    }
                });
                conversationsArray.sort((a, b) => b.timestamp - a.timestamp);
                conversations = conversationsArray;
                if (conversationsArray.length > 0) {
                    // Recalculate unread counts from Firebase on each load
                    await Promise.all(conversationsArray.map(async (conv) => {
                        const count = await calculateUnreadCount(conv.id);
                        unreadCounts.set(conv.id, count);
                        conv.unread_count = count;
                    }));
                    updateUnreadBadges();
                    window.lastCheckTime = latestTimestamp; // Set initial lastCheckTime
                }
                displayConversations(conversations);
                if (!window.globalListenerStarted) {
                    startGlobalMessageListener();
                    window.globalListenerStarted = true;
                }
                if (conversationsArray.length === 0) {
                    document.getElementById('conversations-tab').innerHTML =
                        '<div class="text-center p-4"><p class="text-muted">Aucune conversation trouvée</p><small class="text-info">Scan efficace effectué</small></div>';
                } else {
                    console.log(`🎉 Success! Found ${conversationsArray.length} conversations using efficient method`);
                }
            } catch (error) {
                console.error('❌ Error in loadConversations:', error);
                document.getElementById('conversations-tab').innerHTML =
                    '<div class="text-center p-4"><p class="text-muted">Erreur de chargement</p><small class="text-danger">' + error.message + '</small></div>';
            }
        }


        // 🔥 Helper function to load friends data only (without updating display)
        async function loadFriendsData() {
            try {
                const response = await fetch(`{{ route('messenger.friends') }}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                // Make sure each friend has a status property (accepted or pending)
                let friends = (data.friends || []).map(friend => {
                    // Only mark as pending if:
                    // 1. The friendship status is pending AND
                    // 2. The current user is the receiver (not the sender)
                    const isPendingRequest = friend.friendship_status === 'pending' && friend.is_receiver === true;
                    friend.status = isPendingRequest ? 'pending' : 'accepted';
                    return friend;
                });
                
                // Filter out friends where the current user is the sender of a pending request
                // This hides users who haven't accepted the friend request yet
                friends = friends.filter(friend => {
                    // Keep the friend if:
                    // 1. The friendship is already accepted, OR
                    // 2. The current user is the receiver of a pending request
                    return friend.friendship_status === 'accepted' || 
                           (friend.friendship_status === 'pending' && friend.is_receiver === true);
                });

                window.friends = friends;
                console.log('✅ Friends data preloaded:', window.friends.length, 'friends');
                return window.friends;
            } catch (error) {
                console.error('❌ Error preloading friends:', error);
                window.friends = []; // Set empty array to prevent repeated attempts
                return [];
            }
        }

        // 🔥 Helper function to load patients data only (without updating display)
        async function loadPatientsData() {
            try {
                const response = await fetch(`{{ route('messenger.patients') }}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                window.patients = data.patients || [];
                console.log('✅ Patients data preloaded:', window.patients.length, 'patients');
                return window.patients;
            } catch (error) {
                console.error('❌ Error preloading patients:', error);
                window.patients = []; // Set empty array to prevent repeated attempts
                return [];
            }
        }

        // Load Doctor Friends
        async function loadFriends() {
            try {
                console.log('🔄 Loading friends...');
                const response = await fetch(`{{ route('messenger.friends') }}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    credentials: 'same-origin'
                });

                console.log('📡 Friends response status:', response.status);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const textResponse = await response.text();
                    console.error('❌ Friends - Expected JSON but received:', contentType);
                    console.error('❌ Friends response content:', textResponse.substring(0, 200) + '...');
                    throw new Error('Server returned non-JSON response');
                }

                const data = await response.json();
                console.log('✅ Friends data received:', data);

                friends = data.friends || [];
                // Ensure each friend has a status property
                friends = friends.map(friend => {
                    // Only mark as pending if:
                    // 1. The friendship status is pending AND
                    // 2. The current user is the receiver (not the sender)
                    const isPendingRequest = friend.friendship_status === 'pending' && friend.is_receiver === true;
                    friend.status = isPendingRequest ? 'pending' : 'accepted';
                    return friend;
                });
                
                // Filter out friends where the current user is the sender of a pending request
                // This hides users who have not yet accepted the friend request
                friends = friends.filter(friend => {
                    // Keep the friend if:
                    // 1. The friendship is already accepted, OR
                    // 2. The current user is the receiver of a pending request
                    return friend.friendship_status === 'accepted' || 
                           (friend.friendship_status === 'pending' && friend.is_receiver === true);
                });

                // 🔥 Store globally for conversations tab
                window.friends = friends;
                displayFriends(friends);
            } catch (error) {
                console.error('❌ Error loading friends:', error);
                document.getElementById('friends-tab').innerHTML =
                    '<div class="text-center p-4"><p class="text-muted">Erreur de chargement des médecins</p><small class="text-danger">' + error.message + '</small></div>';
            }
        }

        // Load Patients
        async function loadPatients() {
            try {
                console.log('🔄 Loading patients...');
                const response = await fetch(`{{ route('messenger.patients') }}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    credentials: 'same-origin'
                });

                console.log('📡 Patients response status:', response.status);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const textResponse = await response.text();
                    console.error('❌ Patients - Expected JSON but received:', contentType);
                    console.error('❌ Patients response content:', textResponse.substring(0, 200) + '...');
                    throw new Error('Server returned non-JSON response');
                }

                const data = await response.json();
                console.log('✅ Patients data received:', data);

                patients = data.patients || [];
                // 🔥 Store globally for conversations tab
                window.patients = patients;
                displayPatients(patients);
            } catch (error) {
                console.error('❌ Error loading patients:', error);
                document.getElementById('patients-tab').innerHTML =
                    '<div class="text-center p-4"><p class="text-muted">Erreur de chargement des patients</p><small class="text-danger">' + error.message + '</small></div>';
            }
        }

        // Load Groups from Firebase Firestore
        async function loadGroups() {
            try {
                console.log('🔥 Loading groups from Firebase Firestore...');

                if (!window.firebaseDb) {
                    console.error('❌ Firebase Firestore not initialized');
                    document.getElementById('groups-tab').innerHTML =
                        '<div class="p-3"><button class="btn btn-primary btn-sm w-100 mb-3" onclick="createNewGroup()"><i class="fas fa-plus mr-2"></i>Créer un groupe</button></div>' +
                        '<div class="text-center p-4"><p class="text-muted">Firebase non initialisé</p></div>';
                    return;
                }

                const userId = {{ auth()->user()->id }};
                console.log('👤 Loading groups for user:', userId);

                const db = window.firebaseDb;
                const groupsArray = [];

                try {
                    // 🔥 Query Firebase groups collection where user is a member
                    console.log('🔍 Querying Firebase groups...');

                    // 🔧 Simplified query to avoid composite index requirement
                    const groupsSnapshot = await db.collection('groups')
                        .where(`members.${userId}`, '==', true)
                        .get();

                    console.log('📊 Found', groupsSnapshot.size, 'groups for user', userId);

                    if (groupsSnapshot.empty) {
                        console.log('📭 No groups found for user');
                        groups = [];
                        displayGroups(groups);
                        return;
                    }

                    // 🔥 Process each group and filter/sort on client side
                    groupsSnapshot.forEach((groupDoc) => {
                        const groupData = groupDoc.data();
                        const groupId = groupDoc.id;

                        console.log('📄 Processing group:', groupId, groupData);

                        // 🔧 Filter active groups on client side
                        if (groupData.isActive !== false) {
                            // Count members
                            const membersCount = groupData.members ? Object.keys(groupData.members).length : 0;

                            // Format timestamps
                            const createdAt = groupData.createdAt ? new Date(groupData.createdAt).toLocaleString() : '';
                            const updatedAt = groupData.updatedAt ? new Date(groupData.updatedAt).toLocaleString() : '';

                            const groupObj = {
                                id: groupId,
                                firebase_id: groupId,
                                name: groupData.name || 'Groupe sans nom',
                                description: groupData.description || '',
                                avatar: groupData.avatar || '/images/default-group.png',
                                created_by: groupData.createdBy,
                                members_count: membersCount,
                                created_at: createdAt,
                                updated_at: updatedAt,
                                last_activity: updatedAt,
                                is_active: groupData.isActive !== false,
                                members: groupData.members || {},
                                type: 'firebase_group',
                                // 🔧 Add raw timestamp for sorting
                                sort_timestamp: groupData.updatedAt || groupData.createdAt || 0
                            };

                            groupsArray.push(groupObj);
                            console.log('✅ Added active group to array:', groupObj);
                        } else {
                            console.log('⏭️ Skipping inactive group:', groupId);
                        }
                    });

                    // 🔧 Sort groups by updatedAt on client side (newest first)
                    groupsArray.sort((a, b) => b.sort_timestamp - a.sort_timestamp);

                    console.log('✅ Firebase groups loaded and sorted successfully:', groupsArray);
                    groups = groupsArray;
                    displayGroups(groups);

                    if (groupsArray.length === 0) {
                        console.log('📭 No active groups found for user');
                    } else {
                        console.log(`🎉 Success! Found ${groupsArray.length} active groups for user ${userId}`);
                    }

                } catch (firestoreError) {
                    console.error('❌ Error querying Firebase groups:', firestoreError);

                    // Fallback to empty groups list
                    groups = [];
                    displayGroups(groups);

                    document.getElementById('groups-tab').innerHTML =
                        '<div class="p-3"><button class="btn btn-primary btn-sm w-100 mb-3" onclick="createNewGroup()"><i class="fas fa-plus mr-2"></i>Créer un groupe</button></div>' +
                        '<div class="text-center p-4"><p class="text-muted">Erreur Firestore</p><small class="text-danger">' + firestoreError.message + '</small></div>';
                }

            } catch (error) {
                console.error('❌ Error in loadGroups:', error);
                document.getElementById('groups-tab').innerHTML =
                    '<div class="p-3"><button class="btn btn-primary btn-sm w-100 mb-3" onclick="createNewGroup()"><i class="fas fa-plus mr-2"></i>Créer un groupe</button></div>' +
                    '<div class="text-center p-4"><p class="text-muted">Erreur de chargement</p><small class="text-danger">' + error.message + '</small></div>';
            }
        }

        // Display functions
        function displayConversations(conversations) {
            const container = document.getElementById('conversations-tab');
            if (conversations.length === 0) {
                container.innerHTML = '<div class="loading-state"><p>Aucune conversation trouvée</p></div>';
                return;
            }
            let html = '';
            conversations.forEach(conv => {
                let correctedAvatar = conv.avatar || '/images/default-avatar.png';
                let correctedName = conv.name;
                if (conv.other_user && conv.other_user.id) {
                    const otherUserId = parseInt(conv.other_user.id);
                    if (window.patients && Array.isArray(window.patients)) {
                        const patient = window.patients.find(p => p.id === otherUserId);
                        if (patient) {
                            correctedAvatar = patient.avatar || '/images/default-avatar.png';
                            correctedName = `${patient.first_name || ''} ${patient.last_name || ''}`.trim() || `Patient ${patient.id}`;
                        }
                    }
                    if (correctedAvatar === (conv.avatar || '/images/default-avatar.png') && window.friends && Array.isArray(window.friends)) {
                        const friend = window.friends.find(f => f.id === otherUserId);
                        if (friend) {
                            correctedAvatar = friend.avatar || '/images/default-avatar.png';
                            correctedName = friend.name;
                        }
                    }
                }
                html += `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="conversation-item" onclick="openConversation('${conv.id}', '${correctedName.replace(/'/g, "\\'")}', '${correctedAvatar}')">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <img src="${correctedAvatar}" class="contact-avatar" alt="${correctedName}">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="contact-info">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <h6 class="contact-name">${correctedName}</h6>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <p class="last-message">${conv.last_message || 'Aucun message'}</p>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="message-time">${conv.last_message_time || ''}</div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                ${conv.unread_count ? `<span class="badge badge-primary badge-pill">${conv.unread_count}</span>` : ''}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        `;
            });
            container.innerHTML = html;
            updateUnreadBadges(); // Ensure badges are updated after rendering
        }

        function displayFriends(friends) {
            const container = document.getElementById('friends-tab');

            if (friends.length === 0) {
                container.innerHTML = '<div class="loading-state"><p>Aucun médecin ami trouvé</p></div>';
                return;
            }

            let html = '';
            friends.forEach(friend => {
                // Check if the friend request is pending
                const isPending = friend.status === 'pending';
                const statusBadge = isPending ?
                    `<span class="badge badge-warning ml-2">Demande d'ami</span>` : '';

                // Add a special class for pending friend items
                const pendingClass = isPending ? 'pending-friend-request' : '';

                // Add a small button or icon for pending requests to make it more obvious
                const pendingAction = isPending ?
                    `<div class="pending-action"><i class="fas fa-user-plus text-warning" title="Demande d'ami en attente"></i></div>` : '';

                html += `
                            <div class="friend-item ${pendingClass}" onclick="startChatWithFriend(${friend.id}, '${friend.name.replace(/'/g, "\\'")}', '${friend.avatar}', ${isPending})">
                                <img src="${friend.avatar || '/images/default-avatar.png'}" class="contact-avatar" alt="${friend.name}">
                                <div class="contact-info">
                                    <h6 class="contact-name">${friend.name} ${statusBadge}</h6>
                                    <p class="last-message">${friend.specialty || 'Médecin'}</p>
                                </div>
                                <div class="message-time">
                                    <small class="${friend.is_online ? 'text-success' : 'text-muted'}">${friend.is_online ? 'En ligne' : 'Hors ligne'}</small>
                                    ${pendingAction}
                                </div>
                            </div>
                        `;
            });

            container.innerHTML = html;
        }

        function displayPatients(patients) {
            const container = document.getElementById('patients-tab');

            if (patients.length === 0) {
                container.innerHTML = '<div class="loading-state"><p>Aucun patient trouvé</p></div>';
                return;
            }

            let html = '';
            patients.forEach(patient => {
                const patientName = `${patient.first_name || ''} ${patient.last_name || ''}`.trim() || `Patient ${patient.id}`;
                const lastVisit = patient.last_appointment ? new Date(patient.last_appointment).toLocaleDateString() : '';

                // Generate initials from patient name
                const generateInitials = (name) => {
                    const names = name.trim().split(' ');
                    let initials = names[0].charAt(0).toUpperCase();
                    if (names.length > 1) {
                        initials += names[names.length - 1].charAt(0).toUpperCase();
                    }
                    return initials;
                };

                const initials = generateInitials(patientName);

                // Use consistent green color for all patients
                const bgColor = '#11b8aa';

                html += `
                                                                                                                                                                        <div class="patient-item" onclick="startChatWithPatient(${patient.id}, '${patientName.replace(/'/g, "\\'")}', '${patient.avatar || '/images/default-avatar.png'}')">
                                                                                                                                                                            <div class="contact-initials-circle" style="background-color: ${bgColor};">
                                                                                                                                                                                ${initials}
                                                                                                                                                                            </div>
                                                                                                                                                                            <div class="contact-info">
                                                                                                                                                                                <h6 class="contact-name">${patientName}</h6>
                                                                                                                                                                                <p class="last-message">Patient - ${patient.age || ''}${patient.age ? ' ans' : ''} ${patient.gender || ''}</p>
                                                                                                                                                                            </div>
                                                                                                                                                                            <div class="message-time">${lastVisit}</div>
                                                                                                                                                                        </div>
                                                                                                                                                                    `;
            });

            container.innerHTML = html;
        }
        function displayGroups(groups) {
            const container = document.getElementById('groups-tab');

            let html = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="create-group-section">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <button class="create-group-btn" onclick="createNewGroup()">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <i class="fas fa-plus"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <span>Créer un nouveau groupe</span>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </button>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            `;

            if (groups.length === 0) {
                html += '<div class="loading-state"><p>Aucun groupe trouvé</p></div>';
                container.innerHTML = html;
                return;
            }

            groups.forEach(group => {
                html += `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="group-item" onclick="openGroupChat('${group.id}', '${group.name}')">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="contact-avatar" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <i class="fas fa-users text-white"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="contact-info">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <h6 class="contact-name">${group.name}</h6>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <p class="last-message">${group.members_count} membres</p>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="message-time">${group.last_activity || ''}</div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                `;
            });

            container.innerHTML = html;
        }

        // Chat functions
        function openConversation(conversationId, name, avatar) {
            console.log('🔔 Opening conversation:', conversationId);
            // Clean up previous listener
            let normalizedConversationId = conversationId;
            if (conversationId.includes('-')) {
                const [firstId, secondId] = conversationId.split('-').map(id => parseInt(id));
                const userIds = [firstId, secondId].sort((a, b) => a - b);
                normalizedConversationId = `${userIds[0]}-${userIds[1]}`;
                console.log('📱 Normalized conversation ID for opening:', normalizedConversationId);
            }

            currentConversationId = conversationId; // Use original conversationId for current context
            document.getElementById('chat-contact-name').textContent = name;
            document.getElementById('chat-avatar').src = avatar || '/images/default-avatar.png';

            // 🔥 PERMANENTLY remove welcome screen after first conversation
            const welcomeScreen = document.getElementById('welcome-screen');
            if (welcomeScreen) {
                console.log('🗑️ Permanently removing welcome screen');
                welcomeScreen.remove(); // Completely remove from DOM
            }

            // 🔥 Show and optimize chat interface
            const chatInterface = document.getElementById('chat-interface');
            chatInterface.style.display = 'flex';
            chatInterface.style.flexDirection = 'column';
            chatInterface.style.height = '100%';

            // Load messages for this conversation and force scroll to bottom
            loadMessages(conversationId); // Use original conversationId

            // 🔔 Mark messages as read and clear unread count
            markMessagesAsRead(conversationId); // Use original conversationId
        }

        function startChatWithFriend(friendId, name, avatar, isPending = false) {
            // If this is a pending friend request, show confirmation dialog
            if (isPending) {
                const confirmAccept = confirm(`Vous avez une demande d'ami de la part de ${name}. Voulez-vous l'accepter?`);
                if (confirmAccept) {
                    acceptFriendRequest(friendId);
                    // Create or get existing conversation with friend
                    createDirectConversation(friendId, name, avatar);
                }
                return; // Stop here if it's a pending request
            }

            // If not pending, create or get existing conversation with friend
            createDirectConversation(friendId, name, avatar);
        }

        // Function to accept a friend request
        async function acceptFriendRequest(friendId) {
            try {
                console.log('🤝 Accepting friend request from:', friendId);
                const response = await fetch('/messenger/accept-friend', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        friend_id: friendId
                    }),
                    credentials: 'same-origin'
                });

                if (response.ok) {
                    console.log('✅ Friend request accepted successfully');

                    // Show success toast or alert
                    const responseData = await response.json();
                    const friendName = responseData.data?.friend_name || 'Docteur';

                    // Create and show a toast notification
                    showToast(`Demande d'ami de ${friendName} acceptée avec succès`, 'success');

                    // Update the friend's status in the local friends array
                    if (window.friends && Array.isArray(window.friends)) {
                        const friend = window.friends.find(f => f.id === friendId);
                        if (friend) {
                            friend.status = 'accepted';
                            friend.friendship_status = 'accepted';
                            friend.is_receiver = false; // Reset this as it's no longer relevant

                            // If we're on the friends tab, update the UI immediately
                            const friendElements = document.querySelectorAll(`.friend-item[onclick*="${friendId}"]`);
                            friendElements.forEach(element => {
                                // Remove pending class
                                element.classList.remove('pending-friend-request');

                                // Remove badge from name
                                const nameElement = element.querySelector('.contact-name');
                                if (nameElement) {
                                    nameElement.innerHTML = friend.name;
                                }

                                // Remove pending action icon
                                const messageTime = element.querySelector('.message-time');
                                if (messageTime) {
                                    const pendingAction = messageTime.querySelector('.pending-action');
                                    if (pendingAction) pendingAction.remove();
                                }

                                // Update onclick to not pass isPending=true
                                element.setAttribute('onclick', `startChatWithFriend(${friend.id}, '${friend.name.replace(/'/g, "\\'")}', '${friend.avatar}', false)`);
                                
                                // Apply the newly accepted class for visual feedback
                                element.classList.add('new-friend-accepted');
                                
                                // Add "Accepté" badge temporarily
                                nameElement.innerHTML = friend.name + `<span class="badge badge-success ml-2">Accepté</span>`;
                                
                                // Remove the new friend indicator after some time
                                setTimeout(() => {
                                    const freshElement = document.querySelector(`.friend-item[onclick*="${friend.id}"]`);
                                    if (freshElement) {
                                        // Remove highlighting and badge after 5 seconds
                                        freshElement.classList.remove('new-friend-accepted');
                                        const nameEl = freshElement.querySelector('.contact-name');
                                        if (nameEl) {
                                            nameEl.innerHTML = friend.name;
                                        }
                                    }
                                }, 5000);
                            });
                        }
                    }
                    
                    // Reload friends list to ensure the UI is up to date and to display any
                    // previously hidden users who sent friend requests that are now accepted
                    loadFriends();
                    
                } else {
                    console.error('❌ Failed to accept friend request:', await response.text());
                }
            } catch (error) {
                console.error('❌ Error accepting friend request:', error);
            }
        }

        // Function to show toast notifications
        function showToast(message, type = 'info', duration = 3000) {
            // Create toast container if it doesn't exist
            let toastContainer = document.getElementById('toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toast-container';
                toastContainer.style.position = 'fixed';
                toastContainer.style.top = '20px';
                toastContainer.style.right = '20px';
                toastContainer.style.zIndex = '9999';
                document.body.appendChild(toastContainer);
            }

            // Create toast element
            const toast = document.createElement('div');
            toast.className = 'custom-toast';
            toast.style.backgroundColor = type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8';
            toast.style.color = 'white';
            toast.style.padding = '12px 20px';
            toast.style.borderRadius = '4px';
            toast.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
            toast.style.marginBottom = '10px';
            toast.style.width = '300px';
            toast.style.display = 'flex';
            toast.style.justifyContent = 'space-between';
            toast.style.alignItems = 'center';
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s ease-in-out';

            // Add message
            const messageSpan = document.createElement('span');
            messageSpan.innerText = message;
            toast.appendChild(messageSpan);

            // Add close button
            const closeBtn = document.createElement('button');
            closeBtn.innerText = '×';
            closeBtn.style.background = 'none';
            closeBtn.style.border = 'none';
            closeBtn.style.color = 'white';
            closeBtn.style.fontSize = '20px';
            closeBtn.style.fontWeight = 'bold';
            closeBtn.style.cursor = 'pointer';
            closeBtn.style.marginLeft = '10px';
            closeBtn.onclick = function () {
                removeToast(toast);
            };
            toast.appendChild(closeBtn);

            // Add to container
            toastContainer.appendChild(toast);

            // Show toast with animation
            setTimeout(() => {
                toast.style.opacity = '1';
            }, 10);

            // Hide after duration
            setTimeout(() => {
                removeToast(toast);
            }, duration);

            function removeToast(toast) {
                toast.style.opacity = '0';
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }
        }

        function startChatWithPatient(patientId, name, avatar) {
            // Create or get existing conversation with patient
            createDirectConversation(patientId, name, avatar);
        }

        function openGroupChat(groupId, name) {
            // Clean up previous listener
            cleanupMessageListener();

            currentConversationId = groupId;
            document.getElementById('chat-contact-name').textContent = name;
            document.getElementById('chat-avatar').innerHTML = '<i class="fas fa-users text-primary"></i>';

            // 🔥 SAFELY handle welcome screen removal (it may already be removed)
            const welcomeScreen = document.getElementById('welcome-screen');
            if (welcomeScreen) {
                console.log('🗑️ Permanently removing welcome screen from group chat');
                welcomeScreen.remove(); // Completely remove from DOM
            }

            // 🔥 Show and optimize chat interface
            const chatInterface = document.getElementById('chat-interface');
            if (chatInterface) {
                chatInterface.style.display = 'flex';
                chatInterface.style.flexDirection = 'column';
                chatInterface.style.height = '100%';
            }

            // Load group messages and force scroll to bottom
            loadMessages(groupId);
        }

        async function createDirectConversation(userId, name, avatar) {
            try {
                console.log(`🔄 Creating conversation with user ${userId}...`);

                const response = await fetch(`{{ route('messenger.create-conversation') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        user_id: userId
                    })
                });

                console.log('📡 Create conversation response status:', response.status);

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('❌ Create conversation failed:', response.status, errorText);

                    try {
                        const errorData = JSON.parse(errorText);
                        console.error('❌ Error details:', errorData);
                        alert(`Erreur ${response.status}: ${errorData.message || 'Erreur de création de conversation'}`);
                    } catch (e) {
                        alert(`Erreur ${response.status}: Impossible de créer la conversation`);
                    }
                    return;
                }

                const data = await response.json();
                console.log('✅ Conversation creation response:', data);

                if (data.success) {
                    // Use data from response or fallback to provided parameters
                    const conversationId = data.data.conversation_id || data.conversation_id;
                    const conversationName = data.data.other_user?.name || name;
                    const conversationAvatar = data.data.other_user?.avatar || avatar;

                    console.log(`✅ Opening conversation ${conversationId} with ${conversationName}`);
                    openConversation(conversationId, conversationName, conversationAvatar);
                } else {
                    console.error('❌ Conversation creation failed:', data.message);
                    alert(`Erreur: ${data.message || 'Impossible de créer la conversation'}`);
                }
            } catch (error) {
                console.error('❌ Error creating conversation:', error);
                alert('Erreur de réseau lors de la création de la conversation');
            }
        }
        async function loadMessages(conversationId) {
            try {
                console.log('📥 Loading messages for conversation:', conversationId);

                if (!window.firebaseDb) {
                    console.error('❌ Firebase not initialized');
                    document.getElementById('messages-container').innerHTML =
                        '<div class="text-center p-4"><p class="text-danger">Firebase non initialisé</p></div>';
                    return;
                }

                const container = document.getElementById('messages-container');
                container.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Chargement...</span></div><p mt-2>Chargement des messages...</p></div>';

                const db = window.firebaseDb;
                // Fallback for userId if PHP interpolation fails
                const userId = typeof {{ auth()->user()->id }} !== 'undefined' ? {{ auth()->user()->id }} : '156';
                console.log('🔑 User ID resolved:', userId);

                // 🔥 Normalize the conversation ID for direct messages
                let normalizedConversationId = conversationId;
                if (!conversationId.includes('-') || conversationId.length > 10) {
                    // Group conversation, no normalization needed
                } else {
                    const [firstId, secondId] = conversationId.split('-').map(id => parseInt(id));
                    const userIds = [firstId, secondId].sort((a, b) => a - b);
                    normalizedConversationId = `${userIds[0]}-${userIds[1]}`;
                    console.log('📱 Normalized conversation ID:', normalizedConversationId);
                }

                const isGroupConversation = !conversationId.includes('-') || conversationId.length > 10;

                console.log('🔍 Loading messages for:', isGroupConversation ? 'GROUP' : 'DIRECT', 'conversation:', conversationId);

                // 🧹 CRITICAL: Clean up existing listeners FIRST
                if (window.currentUnsubscribe) {
                    Object.values(window.currentUnsubscribe).forEach(unsubscribe => {
                        if (typeof unsubscribe === 'function') {
                            unsubscribe();
                        }
                    });
                    window.currentUnsubscribe = {};
                }

                // 🎯 Set the current active conversation globally
                window.currentActiveConversation = conversationId;

                let messagesSnapshot;
                let activeConversationId = conversationId; // Default to original ID

                // 🔧 Add timeout to prevent hanging requests
                const LOAD_TIMEOUT_MS = 10000; // 10 seconds
                const timeoutPromise = new Promise((_, reject) => {
                    setTimeout(() => reject(new Error('Request timeout after 10 seconds')), LOAD_TIMEOUT_MS);
                });

                try {
                    const queryPromise = db.collection('messages')
                        .doc(normalizedConversationId)
                        .collection('chats')
                        .orderBy('time', 'asc')
                        .limit(20);
                    messagesSnapshot = await Promise.race([queryPromise.get(), timeoutPromise]);
                    console.log('📋 Queried normalized path:', normalizedConversationId, 'Result:', messagesSnapshot.empty ? 'Empty' : 'Success', 'Docs:', messagesSnapshot.docs.length);
                    if (!messagesSnapshot.empty) {
                        activeConversationId = normalizedConversationId; // Use normalized if data exists
                    }
                } catch (queryError) {
                    console.warn('❌ Query failed for normalized ID:', normalizedConversationId, 'error:', queryError.message);
                }

                if (!messagesSnapshot || messagesSnapshot.empty) {
                    console.log('🔍 Falling back to original conversation ID:', conversationId);
                    try {
                        const fallbackQuery = db.collection('messages')
                            .doc(conversationId)
                            .collection('chats')
                            .orderBy('time', 'asc')
                            .limit(20);
                        messagesSnapshot = await Promise.race([fallbackQuery.get(), timeoutPromise]);
                        console.log('📋 Queried original path:', conversationId, 'Result:', messagesSnapshot.empty ? 'Empty' : 'Success', 'Docs:', messagesSnapshot.docs.length);
                    } catch (fallbackError) {
                        console.error('❌ Fallback query failed:', fallbackError.message);
                    }
                }

                // Clear loading state
                container.innerHTML = '';

                if (!messagesSnapshot || messagesSnapshot.empty) {
                    console.log('ℹ️ No messages found in conversation:', normalizedConversationId, 'or', conversationId);
                    container.innerHTML = '<div class="text-center p-4"><p class="text-muted">Aucun message pour le moment.</p></div>';
                    return;
                }

                const messages = [];
                messagesSnapshot.forEach((doc) => {
                    const messageData = doc.data();
                    console.log('📄 Retrieved message:', messageData.text, 'sender:', messageData.sender?.id, 'time:', messageData.time, 'messageId:', messageData.messageId || messageData.id);
                    messages.push(messageData);
                });

                // Display initial messages with dynamic isOwn
                messages.forEach((messageData) => {
                    const isOwn = messageData.sender && messageData.sender.id === userId.toString();
                    displayFirebaseMessage(messageData, isOwn, isGroupConversation);
                });

                // 🎯 Set up real-time listener with conversation validation
                console.log('🔍 Setting up listener on path:', activeConversationId);
                let isRendering = false;

                const unsubscribe = db.collection('messages')
                    .doc(activeConversationId)
                    .collection('chats')
                    .orderBy('time', 'asc')
                    .onSnapshot((snapshot) => {
                        console.log('📡 Real-time snapshot triggered, docs:', snapshot.size, 'path:', activeConversationId);

                        // 🛡️ CRITICAL: Check if this listener is still for the active conversation
                        if (window.currentActiveConversation !== conversationId) {
                            console.log('🚫 Ignoring snapshot - not for current active conversation:', window.currentActiveConversation, 'vs', conversationId);
                            return;
                        }

                        if (isRendering) {
                            console.log('🔄 Already rendering, skipping...');
                            return; // Prevent re-entry during rendering
                        }

                        isRendering = true;

                        try {
                            // Collect all current messages
                            const allMessages = [];
                            snapshot.forEach((doc) => {
                                const messageData = doc.data();
                                console.log('📢 Real-time message:', messageData.text, 'sender:', messageData.sender?.id, 'time:', messageData.time, 'messageId:', messageData.messageId || messageData.id);
                                allMessages.push(messageData);
                            });

                            console.log('📢 All messages collected:', allMessages.length);
                            // Sort all messages by time
                            allMessages.sort((a, b) => parseInt(a.time || 0) - parseInt(b.time || 0));

                            // 🛡️ Double-check we're still on the right conversation before rendering
                            if (window.currentActiveConversation !== conversationId) {
                                console.log('🚫 Aborting render - conversation changed during processing');
                                return;
                            }

                            // Re-render all messages to maintain order
                            const currentContainer = document.getElementById('messages-container');
                            if (currentContainer) {
                                currentContainer.innerHTML = ''; // Clear the container
                                if (allMessages.length > 0) {
                                    allMessages.forEach((messageData) => {
                                        const isOwn = messageData.sender && messageData.sender.id === userId.toString();
                                        const messageId = messageData.messageId || messageData.id || Date.now().toString();
                                        console.log('📢 Rendering:', messageData.text, 'isOwn:', isOwn, 'messageId:', messageId, 'for conversation:', conversationId);
                                        displayFirebaseMessage(messageData, isOwn, isGroupConversation);
                                    });
                                    scheduleScrollToBottom();
                                    console.log('📡 Real-time update: Re-rendered', allMessages.length, 'messages for conversation:', conversationId);
                                } else {
                                    console.log('📡 No messages to render in real-time update');
                                }
                            }
                        } catch (error) {
                            console.error('❌ Error during real-time render:', error);
                        } finally {
                            isRendering = false; // Allow next render
                        }
                    }, (error) => {
                        console.error('❌ Real-time listener error:', error);
                        isRendering = false; // Ensure re-entry is allowed on error
                    });

                // Store unsubscribe function with conversation-specific key
                window.currentUnsubscribe = window.currentUnsubscribe || {};
                window.currentUnsubscribe[conversationId] = unsubscribe;

                scheduleScrollToBottom();
                console.log('✅ Loaded initial messages for conversation:', activeConversationId, 'with real-time listener');

            } catch (error) {
                console.error('❌ Unexpected error in loadMessages:', error);
                document.getElementById('messages-container').innerHTML =
                    '<div class="text-center p-4"><p class="text-danger">Erreur inattendue : ' + error.message + '</p></div>';
            }
        }
        // 🔥 Display a message from Firebase in the UI
        function displayFirebaseMessage(messageData, isOwn = false, isGroupConversation = false) {
            const container = document.getElementById('messages-container');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message-item ${isOwn ? 'own' : ''}`;

            // Store message data for deletion
            messageDiv.dataset.messageId = messageData.messageId || Date.now().toString();
            messageDiv.dataset.conversationId = currentConversationId;
            messageDiv.dataset.isGroup = isGroupConversation;

            // Format timestamp - handle both group (timestamp) and direct (time) formats
            const timestamp = messageData.timestamp || (messageData.time ? parseInt(messageData.time) : Date.now());
            const time = new Date(timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            // Get message content
            let messageText = messageData.text || '[Message]';
            const senderName = messageData.sender?.name || 'Unknown';

            // 🔧 FIXED: Get avatar from chat header for accurate patient/doctor images
            let senderAvatar = '/images/default-avatar.png';

            if (!isOwn) {
                // For received messages, use the avatar from the chat header (which has the correct patient/doctor image)
                const chatAvatarElement = document.getElementById('chat-avatar');
                if (chatAvatarElement && chatAvatarElement.src) {
                    senderAvatar = chatAvatarElement.src;
                } else {
                    // Fallback to messageData if chat header not available
                    senderAvatar = messageData.sender?.imageUrl || messageData.sender?.avatar || '/images/default-avatar.png';
                }
            }

            // Check if this is a media message
            const fileUrl = messageData.fileUrl;
            const fileName = messageData.fileName;
            const messageType = messageData.type || 'text';

            // 🔗 Process links in text messages
            const urlRegex = /(https?:\/\/[^\s]+)/g;
            const hasLinks = messageText.match(urlRegex);

            if (hasLinks && messageType === 'text') {
                // Replace long URLs with short link previews
                messageText = messageText.replace(urlRegex, (url) => {
                    const domain = extractDomain(url);
                    const shortUrl = url.length > 30 ? url.substring(0, 27) + '...' : url;
                    return `<div class="message-link-preview" onclick="window.open('${url}', '_blank')">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="link-preview-icon">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <i class="fas fa-link"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="link-preview-content">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="link-preview-title">${domain}</div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="link-preview-url">${shortUrl}</div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="link-preview-action">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <i class="fas fa-external-link-alt"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>`;
                });
            }

            // Generate content based on message type
            let messageContent = '';

            if (messageType === 'image' && fileUrl) {
                // Display actual image instead of text
                messageContent = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div style="position: relative; display: inline-block;">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <img src="${fileUrl}" alt="${fileName || 'Image'}" style="max-width: 200px; max-height: 200px; border-radius: 8px; cursor: pointer;" onclick="window.open('${fileUrl}', '_blank')">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <small class="message-time">${time}</small>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        `;
            } else if (messageType === 'file' && fileUrl && fileName && fileName.toLowerCase().includes('.pdf')) {
                // Display PDF with cover image (you will provide the cover image)
                messageContent = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div style="display: flex; align-items: center; padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 4px; cursor: pointer;" onclick="window.open('${fileUrl}', '_blank')">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <img src="/images/pdf-cover.png" alt="PDF" style="width: 40px; height: 40px; margin-right: 12px; border-radius: 4px;">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div style="font-weight: 500; color: #374151;">${fileName}</div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div style="font-size: 12px; color: #6b7280;">PDF Document</div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <small class="message-time">${time}</small>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    `;
            } else if (fileUrl) {
                // Other file types
                messageContent = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div style="display: flex; align-items: center; padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 4px;">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <i class="fas fa-file" style="margin-right: 8px; color: #667eea;"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <a href="${fileUrl}" target="_blank" style="color: #667eea; text-decoration: none; font-weight: 500;">${fileName || 'Fichier'}</a>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <small class="message-time">${time}</small>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    `;
            } else {
                // Regular text message (with processed links)
                messageContent = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="message-text">${messageText}</div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <small class="message-time">${time}</small>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    `;
            }

            // Add read receipt status for own messages
            const readReceiptIcon = isOwn ? `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="message-status-icon" style="position: absolute; bottom: 4px; right: 8px; font-size: 12px;">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        ${messageData.status === 'read' ?
                    '<i class="fas fa-check-double" style="color: #667eea;" title="Lu"></i>' :
                    '<i class="fas fa-check" style="color: #9ca3af;" title="Envoyé"></i>'
                }
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                ` : '';

            // Add delete button for own messages (exclude images since they have their own context menu)
            const deleteButton = isOwn && messageType !== 'image' ? `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="message-delete-btn ${messageType === 'text' ? 'text-delete' : 'file-delete'}" onclick="deleteMessage(this)" title="Supprimer le message">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <i class="fas fa-trash"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ` : '';

            if (isOwn) {
                // Add message menu for image messages
                const messageMenu = messageType === 'image' ? `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="message-menu-icon" style="position: absolute; top: 8px; right: 8px; opacity: 0; transition: opacity 0.3s ease; background: rgba(0,0,0,0.7); color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 10;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0'" onclick="event.preventDefault(); event.stopPropagation(); toggleMessageMenu(this, '${fileUrl}', '${fileName || 'image'}', ${isOwn}); return false;">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <i class="fas fa-ellipsis-v" style="font-size: 10px; pointer-events: none;"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="message-context-menu" style="position: absolute; top: 35px; right: 8px; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); min-width: 120px; opacity: 0; visibility: hidden; transform: translateY(-10px); transition: all 0.2s ease; z-index: 1000;">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="menu-item" style="padding: 10px 16px; cursor: pointer; border-bottom: 1px solid #f0f0f0; font-size: 14px; color: #333; transition: background 0.2s ease;" onclick="downloadImage('${fileUrl}', '${fileName || 'image'}')">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <i class="fas fa-download" style="margin-right: 8px; color: #667eea;"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                Télécharger
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="menu-item delete-item" style="padding: 10px 16px; cursor: pointer; font-size: 14px; color: #ef4444; transition: background 0.2s ease;" onclick="deleteMessage(this.closest('.message-item'))">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <i class="fas fa-trash" style="margin-right: 8px;"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                Supprimer
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    ` : '';

                messageDiv.innerHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="message-bubble" style="position: relative;" onmouseover="showMessageMenu(this)" onmouseout="hideMessageMenu(this)">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ${messageContent}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ${deleteButton}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ${messageMenu}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                ${readReceiptIcon}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    `;
            } else {
                // 🔥 For group messages, always show sender name. For direct messages, show sender name only if it's a group
                const showSenderName = isGroupConversation;

                // Add message menu for image messages (received messages)
                const messageMenu = messageType === 'image' ? `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="message-menu-icon" style="position: absolute; top: 8px; right: 8px; opacity: 0; transition: opacity 0.3s ease; background: rgba(0,0,0,0.7); color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 10;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0'" onclick="event.preventDefault(); event.stopPropagation(); toggleMessageMenu(this, '${fileUrl}', '${fileName || 'image'}', ${isOwn}); return false;">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <i class="fas fa-ellipsis-v" style="font-size: 10px; pointer-events: none;"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="message-context-menu" style="position: absolute; top: 35px; right: 8px; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); min-width: 120px; opacity: 0; visibility: hidden; transform: translateY(-10px); transition: all 0.2s ease; z-index: 1000;">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="menu-item" style="padding: 10px 16px; cursor: pointer; font-size: 14px; color: #333; transition: background 0.2s ease;" onclick="downloadImage('${fileUrl}', '${fileName || 'image'}')">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <i class="fas fa-download" style="margin-right: 8px; color: #667eea;"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            Télécharger
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                ` : '';

                messageDiv.innerHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <img src="${senderAvatar}" class="message-sender-avatar" alt="${senderName}">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="message-bubble" style="position: relative;" onmouseover="showMessageMenu(this)" onmouseout="hideMessageMenu(this)">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ${showSenderName ? `<div class="message-sender-name">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <small class="text-primary font-weight-bold">${senderName}</small>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>` : ''}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        ${messageContent}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        ${messageMenu}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    `;
            }

            container.appendChild(messageDiv);

            // 🔥 Performance: Check if we have too many messages and remove old ones
            const messages = container.querySelectorAll('.message-item');
            if (messages.length > 100) { // Keep only last 100 messages in DOM
                console.log('🔧 Removing old messages for performance (keeping last 100)');
                for (let i = 0; i < messages.length - 100; i++) {
                    messages[i].remove();
                }
            }

            // 🔥 Force scroll to bottom after adding message
            setTimeout(() => {
                forceScrollToBottom();
            }, 50);
        }

        // Helper function to extract domain from URL
        function extractDomain(url) {
            try {
                const urlObj = new URL(url);
                return urlObj.hostname.replace('www.', '');
            } catch (e) {
                // Fallback for invalid URLs
                const match = url.match(/^https?:\/\/([^\/]+)/);
                return match ? match[1].replace('www.', '') : 'Lien';
            }
        }

        // 🔧 DISABLED: Real-time listeners to avoid QUIC errors - using polling instead
        function setupRealtimeMessageListener(conversationId) {
            console.log('🔄 Setting up POLLING listener (real-time disabled) for:', conversationId);

            // Clean up any existing listeners/polling
            cleanupMessageListener();

            const isGroupConversation = !conversationId.includes('-') || conversationId.length > 10;

            // 🔄 Use efficient polling instead of real-time listeners
            setupPollingListener(conversationId, isGroupConversation);

            console.log('✅ Polling listener active - real-time listeners disabled to avoid QUIC errors');
        }

        // 🔧 Enhanced polling listener - primary method (no more real-time listeners)
        function setupPollingListener(conversationId, isGroupConversation) {
            console.log('🔄 Setting up efficient polling for:', conversationId);
            if (window.pollingInterval) {
                clearInterval(window.pollingInterval);
            }
            const userId = {{ auth()->user()->id }};
            let lastChecked = Date.now();
            let consecutiveErrors = 0;
            const maxErrors = 3;

            // Normalize the conversation ID for direct messages
            let normalizedConversationId = conversationId;
            if (!isGroupConversation && conversationId.includes('-')) {
                const [firstId, secondId] = conversationId.split('-').map(id => parseInt(id));
                const userIds = [firstId, secondId].sort((a, b) => a - b);
                normalizedConversationId = `${userIds[0]}-${userIds[1]}`;
                console.log('📱 Normalized conversation ID for polling:', normalizedConversationId);
            }

            window.pollingInterval = setInterval(async () => {
                try {
                    if (!window.firebaseDb) {
                        console.warn('⚠️ Firebase not available for polling');
                        return;
                    }
                    const db = window.firebaseDb;
                    let query;
                    if (isGroupConversation) {
                        query = db.collection('groups')
                            .doc(conversationId) // Use original ID for groups
                            .collection('messages')
                            .orderBy('timestamp')
                            .where('timestamp', '>', lastChecked)
                            .limit(10);
                    } else {
                        query = db.collection('messages')
                            .doc(normalizedConversationId) // Use normalized ID for direct messages
                            .collection('chats')
                            .orderBy('time')
                            .where('time', '>', lastChecked.toString())
                            .limit(10);
                    }
                    const snapshot = await query.get();
                    if (!snapshot.empty) {
                        console.log('🔄 Polling found', snapshot.size, 'new messages');
                        const newMessages = [];
                        snapshot.forEach((doc) => {
                            const messageData = doc.data();
                            const timestamp = isGroupConversation ? (messageData.timestamp || 0) : parseInt(messageData.time || 0);
                            newMessages.push({ data: messageData, timestamp });
                        });
                        newMessages.sort((a, b) => a.timestamp - b.timestamp);
                        newMessages.forEach(({ data: messageData }) => {
                            const isOwn = messageData.sender && messageData.sender.id === userId.toString();
                            if (!isOwn && messageData.status !== 'read') {
                                console.log('📥 New message via polling:', messageData.text?.substring(0, 50));
                                displayFirebaseMessage(messageData, false, isGroupConversation);
                                // Update conversation item if in conversation
                                if (currentConversationId === normalizedConversationId) { // Use normalized ID here
                                    const conv = conversations.find(c => c.id === conversationId); // Keep original ID for lookup
                                    if (conv) {
                                        conv.last_message = messageData.text || 'New message';
                                        conv.last_message_time = new Date(messageData.time || Date.now()).toLocaleString();
                                        conv.timestamp = parseInt(messageData.time || Date.now());
                                        refreshConversationItem(conv);
                                    }
                                    // Mark messages as read since user is in conversation
                                    markMessagesAsRead(normalizedConversationId); // Use normalized ID here
                                }
                                scheduleScrollToBottom();
                            }
                        });
                        if (newMessages.length > 0) {
                            const latestTimestamp = Math.max(...newMessages.map(m => m.timestamp));
                            lastChecked = latestTimestamp + 1;
                        }
                    }
                    consecutiveErrors = 0;
                } catch (pollingError) {
                    consecutiveErrors++;
                    console.error('❌ Polling error (' + consecutiveErrors + '/' + maxErrors + '):', pollingError);
                    if (consecutiveErrors >= maxErrors) {
                        console.warn('⚠️ Too many polling errors, restarting with longer interval...');
                        clearInterval(window.pollingInterval);
                        setTimeout(() => {
                            setupPollingListener(conversationId, isGroupConversation); // Use original ID for restart
                        }, 10000);
                        return;
                    }
                }
            }, 2000);
            console.log('✅ Efficient polling active every 2 seconds');
        }

        // Enhanced cleanup with polling cleanup only
        function cleanupMessageListener() {
            // No more real-time listeners to clean up

            if (window.pollingInterval) {
                console.log('🧹 Cleaning up polling interval');
                clearInterval(window.pollingInterval);
                window.pollingInterval = null;
            }

            console.log('🧹 Message listener cleanup complete (polling mode)');
        }

        // Search user by email from modal
        async function searchUserByEmailModal() {
            const email = document.getElementById('modalSearchEmail').value.trim();
            if (!email) return;

            try {
                const response = await fetch(`{{ route('messenger.search-doctors') }}?email=${encodeURIComponent(email)}`, {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                displaySearchResultsModal(data.users || []);
            } catch (error) {
                console.error('Error searching user:', error);
                document.getElementById('modal-search-results').innerHTML = '<p class="text-danger">Erreur de recherche</p>';
            }
        }

        function displaySearchResultsModal(users) {
            const container = document.getElementById('modal-search-results');

            if (users.length === 0) {
                container.innerHTML = '<p class="text-muted">Aucun utilisateur trouvé</p>';
                return;
            }

            let html = '';
            users.forEach(user => {
                html += `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="user-result p-3 border rounded mb-2">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="d-flex align-items-center">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <img src="${user.avatar || '/images/default-avatar.png'}" class="rounded-circle mr-3" width="40" height="40">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="flex-grow-1">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <h6 class="mb-1">${user.name}</h6>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <small class="text-muted">${user.email}</small>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <button class="btn btn-primary btn-sm" onclick="sendFriendRequest(${user.id})">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <i class="fas fa-user-plus mr-1"></i>Ajouter
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </button>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                `;
            });

            container.innerHTML = html;
        }

        // Search user by email (legacy function - kept for compatibility)
        async function searchUserByEmail() {
            // This function is now replaced by the modal version
            showAddFriendModal();
        }

        // Create new group
        function createNewGroup() {
            // Load friends for group member selection
            if (friends.length === 0) {
                loadFriends().then(() => {
                    showCreateGroupModal();
                });
            } else {
                showCreateGroupModal();
            }
        }

        function showCreateGroupModal() {
            // Populate friends list in modal
            const friendsList = document.getElementById('friendsList');
            let html = '';

            friends.forEach(friend => {
                html += `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="form-check">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <input class="form-check-input" type="checkbox" value="${friend.id}" id="friend-${friend.id}">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <label class="form-check-label d-flex align-items-center" for="friend-${friend.id}">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <img src="${friend.avatar || '/images/default-avatar.png'}" class="rounded-circle mr-2" width="30" height="30">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ${friend.name}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </label>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                `;
            });

            friendsList.innerHTML = html;
            $('#createGroupModal').modal('show');
        }

        async function submitCreateGroup() {
            const groupName = document.getElementById('groupName').value.trim();
            const selectedFriends = Array.from(document.querySelectorAll('#friendsList input:checked')).map(cb => cb.value);

            if (!groupName || selectedFriends.length === 0) {
                alert('Veuillez saisir un nom de groupe et sélectionner au moins un membre');
                return;
            }

            try {
                console.log('🔥 Creating group:', {
                    name: groupName,
                    members: selectedFriends
                });

                // 🔥 Step 1: Get group data from Laravel backend
                const response = await fetch(`{{ route('messenger.create-group') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        name: groupName,
                        members: selectedFriends
                    })
                });

                const data = await response.json();
                console.log('📡 Backend response:', data);

                if (data.success && data.data.should_save_to_firebase) {
                    console.log('🔥 Saving group to Firebase...');

                    if (!window.firebaseDb) {
                        console.error('❌ Firebase not initialized');
                        alert('Erreur: Firebase non initialisé');
                        return;
                    }

                    // 🔥 Step 2: Save to Firebase using data from backend
                    const groupData = data.data.group_data;
                    const firebaseGroupId = data.data.firebase_group_id;

                    console.log('💾 Saving to Firebase:', {
                        id: firebaseGroupId,
                        data: groupData
                    });

                    const db = window.firebaseDb;
                    await db.collection('groups').doc(firebaseGroupId).set(groupData);

                    console.log('✅ Group saved to Firebase successfully!');

                    // 🔥 Step 3: Close modal and reload
                    $('#createGroupModal').modal('hide');
                    document.getElementById('groupName').value = '';
                    document.querySelectorAll('#friendsList input:checked').forEach(cb => cb.checked = false);

                    loadGroups(); // Reload groups
                    //alert('Groupe créé avec succès dans Firebase!');

                } else {
                    console.error('❌ Backend failed to prepare group data:', data.message);
                    alert('Erreur: ' + (data.message || 'Impossible de préparer les données du groupe'));
                }
            } catch (error) {
                console.error('❌ Error creating group:', error);
                alert('Erreur lors de la création du groupe: ' + error.message);
            }
        }

        // Send friend request
        async function sendFriendRequest(userId) {
            try {
                const response = await fetch(`{{ route('messenger.send-invitation') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        user_id: userId
                    })
                });

                const data = await response.json();
                if (data.success) {
                    alert('Demande d\'ami envoyée!');
                }
            } catch (error) {
                console.error('Error sending friend request:', error);
                alert('Erreur lors de l\'envoi de la demande');
            }
        }

        // Message functions
        function setupMessageInput() {
            document.getElementById('messageInput')?.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });
        }

        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();

            if (message && currentConversationId) {
                // Add message to UI immediately
                addMessageToUI(message, true);

                // Send to server/Firebase and reload conversation list on success
                sendMessageToServer(currentConversationId, message)
                    .then(() => {
                        console.log('📤 Message sent, reloading conversation list for:', currentConversationId);
                        loadConversations(); // Reload the conversation list
                    })
                    .catch(error => {
                        console.error('❌ Error sending message, reverting UI:', error);
                        // Remove the message from UI if sending failed
                        const messageElements = document.querySelectorAll('.message-item');
                        if (messageElements.length > 0) {
                            const lastMessage = messageElements[messageElements.length - 1];
                            lastMessage.remove();
                        }
                        alert('Erreur lors de l\'envoi du message: ' + error.message);
                    });

                // Clear input
                input.value = '';
            }
        }

        function addMessageToUI(message, isOwn = false) {
            const container = document.getElementById('messages-container');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message-item ${isOwn ? 'own' : ''}`;

            // Store message data for deletion
            const messageId = Date.now().toString();
            messageDiv.dataset.messageId = messageId;
            messageDiv.dataset.conversationId = currentConversationId;
            messageDiv.dataset.isGroup = !currentConversationId.includes('-') || currentConversationId.length > 10;

            const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            // 🔧 FIXED: Define messageType for regular text messages
            const messageType = 'text';

            // 🔗 Process links in text messages
            let processedMessage = message;
            const urlRegex = /(https?:\/\/[^\s]+)/g;
            const hasLinks = message.match(urlRegex);

            if (hasLinks) {
                // Replace long URLs with short link previews
                processedMessage = message.replace(urlRegex, (url) => {
                    const domain = extractDomain(url);
                    const shortUrl = url.length > 30 ? url.substring(0, 27) + '...' : url;
                    return `<div class="message-link-preview" onclick="window.open('${url}', '_blank')">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="link-preview-icon">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <i class="fas fa-link"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="link-preview-content">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="link-preview-title">${domain}</div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="link-preview-url">${shortUrl}</div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="link-preview-action">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <i class="fas fa-external-link-alt"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>`;
                });
            }

            // Add delete button for own messages (exclude images since they have their own context menu)
            const deleteButton = isOwn && messageType !== 'image' ? `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="message-delete-btn ${messageType === 'text' ? 'text-delete' : 'file-delete'}" onclick="deleteMessage(this)" title="Supprimer le message">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <i class="fas fa-trash"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ` : '';

            if (isOwn) {
                messageDiv.innerHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="message-bubble" style="position: relative;">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="message-text">${processedMessage}</div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <small class="message-time">${time}</small>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        ${deleteButton}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                `;
            } else {
                messageDiv.innerHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <img src="/images/default-avatar.png" class="message-sender-avatar" alt="User">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="message-bubble">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="message-text">${processedMessage}</div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <small class="message-time">${time}</small>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                `;
            }

            container.appendChild(messageDiv);

            // 🔥 Performance: Check if we have too many messages and remove old ones
            const messages = container.querySelectorAll('.message-item');
            if (messages.length > 100) { // Keep only last 100 messages in DOM
                console.log('🔧 Removing old messages for performance (keeping last 100)');
                for (let i = 0; i < messages.length - 100; i++) {
                    messages[i].remove();
                }
            }

            // 🔥 Force scroll to bottom after adding message
            setTimeout(() => {
                forceScrollToBottom();
            }, 50);
        }

        async function sendMessageToServer(conversationId, message, messageData = null) {
            try {
                console.log('📤 Sending message to Firebase:', message || 'File message', 'conversation:', conversationId);

                if (!window.firebaseDb) {
                    console.error('❌ Firebase not initialized');
                    alert('Erreur: Firebase non initialisé');
                    return;
                }

                const userId = {{ auth()->user()->id }}; // Should be 156
                const userName = '{{ auth()->user()->name }}'; // Hatem Gharbi
                const userAvatar = '{{ auth()->user()->avatar ?? "/images/default-avatar.png" }}';

                // 🔥 Check if this is a group conversation or direct conversation
                const isGroupConversation = !conversationId.includes('-') || conversationId.length > 10;

                console.log('🔍 Conversation type:', isGroupConversation ? 'GROUP' : 'DIRECT', 'ID:', conversationId);

                // Generate unique message ID
                const messageId = Date.now().toString() + '_' + Math.random().toString(36).substr(2, 9);

                // Use provided messageData or create new for text message
                const finalMessageData = messageData || {
                    text: message,
                    time: Date.now(), // Changed to numeric timestamp
                    sender: {
                        id: userId.toString(),
                        name: userName,
                        imageUrl: userAvatar,
                        auth: true
                    },
                    type: 'text',
                    status: 'sent',
                    read_at: null,
                    messageId: messageId
                };

                // Ensure messageId is set for all messages
                if (!finalMessageData.messageId) {
                    finalMessageData.messageId = messageId;
                }

                // Ensure status and read_at are set for all messages
                if (!finalMessageData.status) {
                    finalMessageData.status = 'sent';
                }
                if (finalMessageData.read_at === undefined) {
                    finalMessageData.read_at = null;
                }

                let targetConversationId = conversationId; // Default to the provided conversationId

                if (!isGroupConversation) {
                    // 🔥 DIRECT MESSAGE: Check if the provided conversationId exists
                    const db = window.firebaseDb;
                    const [firstId, secondId] = conversationId.split('-').map(id => parseInt(id));
                    const userIds = [firstId, secondId].sort((a, b) => a - b);
                    const normalizedConversationId = `${userIds[0]}-${userIds[1]}`;
                    const reverseConversationId = `${secondId}-${firstId}`; // Check the reverse form

                    console.log('📱 Original conversation ID:', conversationId);
                    console.log('📱 Normalized conversation ID:', normalizedConversationId);
                    console.log('📱 Reverse conversation ID:', reverseConversationId);
                    console.log('📱 Sender ID:', userId, 'Receiver ID from conversation:', secondId);

                    // Check if the conversation exists by looking for sub-documents in the chats collection
                    const checkConversation = async (id) => {
                        try {
                            const snapshot = await db.collection('messages').doc(id).collection('chats').limit(1).get();
                            console.log('📋 Checked conversation:', id, 'Exists:', !snapshot.empty);
                            return !snapshot.empty;
                        } catch (error) {
                            console.error('❌ Error checking conversation:', id, error);
                            return false;
                        }
                    };

                    const existsAsProvided = await checkConversation(conversationId);
                    const existsAsReverse = await checkConversation(reverseConversationId);
                    const existsAsNormalized = await checkConversation(normalizedConversationId);

                    if (existsAsProvided) {
                        targetConversationId = conversationId; // Use the provided ID if it exists
                        console.log('📱 Using existing conversation ID:', targetConversationId);
                    } else if (existsAsReverse) {
                        targetConversationId = reverseConversationId; // Use reverse ID if it exists
                        console.log('📱 Using existing reverse conversation ID:', targetConversationId);
                    } else if (existsAsNormalized) {
                        targetConversationId = normalizedConversationId; // Use normalized ID if it exists
                        console.log('📱 Using normalized existing conversation ID:', targetConversationId);
                    } else {
                        targetConversationId = normalizedConversationId; // Create new with normalized ID
                        console.log('📱 Creating new conversation with normalized ID:', targetConversationId);
                    }

                    // Add receiver info for direct messages if not already present
                    if (!finalMessageData.receiver) {
                        const receiverId = targetConversationId.split('-').find(id => id !== userId.toString());
                        console.log('📱 Determined receiver ID:', receiverId);
                        finalMessageData.receiver = {
                            id: receiverId,
                            name: document.getElementById('chat-contact-name')?.textContent || 'Unknown',
                            imageUrl: document.getElementById('chat-avatar')?.src || '/images/default-avatar.png',
                            auth: false,
                            device_token: null
                        };
                    }
                }

                if (isGroupConversation) {
                    // 🔥 GROUP MESSAGE: Save to groups/{groupId}/messages/
                    console.log('📤 Sending GROUP message to Firebase...');

                    const groupMessageData = {
                        ...finalMessageData,
                        timestamp: Date.now(), // Numeric timestamp for groups
                        groupId: targetConversationId
                    };

                    console.log('📤 Group message data to send:', groupMessageData);

                    const db = window.firebaseDb;

                    // 🔥 Save to groups/{groupId}/messages/{messageId}
                    await db.collection('groups')
                        .doc(targetConversationId)
                        .collection('messages')
                        .doc(messageId)
                        .set(groupMessageData);

                    console.log('✅ Group message sent successfully to Firebase:', messageId, 'in group:', targetConversationId);

                    // 🔥 Update group's last activity
                    await db.collection('groups')
                        .doc(targetConversationId)
                        .update({
                            updatedAt: Date.now(),
                            lastMessage: finalMessageData.text || 'File shared',
                            lastMessageBy: userId.toString()
                        });

                    console.log('✅ Group last activity updated');

                } else {
                    // 🔥 DIRECT MESSAGE: Use existing logic with targetConversationId
                    console.log('📤 Sending DIRECT message to Firebase...');

                    console.log('📤 Direct message data to send:', finalMessageData);

                    // Send to Firebase using targetConversationId
                    const db = window.firebaseDb;

                    await db.collection('messages')
                        .doc(targetConversationId)
                        .collection('chats')
                        .doc(messageId)
                        .set(finalMessageData);

                    console.log('✅ Direct message sent successfully to Firebase:', messageId, 'in target conversation:', targetConversationId);
                }

                // Optional: also send to Laravel backend for logging
                try {
                    await fetch('/messenger/log-message', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            conversation_id: targetConversationId,
                            message: finalMessageData.text || 'File shared',
                            firebase_message_id: messageId,
                            is_group: isGroupConversation,
                            message_type: finalMessageData.type || 'text',
                            file_url: finalMessageData.fileUrl || null
                        })
                    });
                    console.log('✅ Message logged to Laravel backend');
                } catch (logError) {
                    console.warn('⚠️ Failed to log message to backend (non-critical):', logError);
                }

            } catch (error) {
                console.error('❌ Error sending message to Firebase:', error);
                alert('Erreur lors de l\'envoi du message: ' + error.message);

                // Remove the message from UI if sending failed (only for new messages, not file uploads)
                if (!messageData) {
                    const messageElements = document.querySelectorAll('.message-item');
                    if (messageElements.length > 0) {
                        const lastMessage = messageElements[messageElements.length - 1];
                        lastMessage.remove();
                    }
                }
            }
        }

        // Search functionality
        function setupSearch() {
            const searchInput = document.getElementById('searchContacts');
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    const query = this.value.toLowerCase().trim();
                    filterCurrentTab(query);
                });
            }

            document.getElementById('searchEmail')?.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    searchUserByEmail();
                }
            });
        }

        function filterCurrentTab(query) {
            const activeTab = document.querySelector('.modern-tab-btn.active')?.dataset.tab;
            if (!activeTab) return;

            const items = document.querySelectorAll(`#${activeTab}-tab .conversation-item, #${activeTab}-tab .friend-item, #${activeTab}-tab .patient-item, #${activeTab}-tab .group-item`);

            items.forEach(item => {
                const name = item.querySelector('.contact-name')?.textContent.toLowerCase() || '';
                const message = item.querySelector('.last-message')?.textContent.toLowerCase() || '';

                if (name.includes(query) || message.includes(query)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Additional functions
        function attachFile() {
            alert('Fonctionnalité de partage de fichiers à venir');
        }

        function startVideoCall() {
            alert('Fonctionnalité d\'appel vidéo à venir');
        }

        function showChatInfo() {
            alert('Informations de conversation à venir');
        }

        // Messenger configuration
        window.messengerConfig = {
            userId: {{ auth()->user()->id }},
            userName: '{{ auth()->user()->name }}',
            apiBaseUrl: '{{ url("/messenger") }}',
            csrfToken: '{{ csrf_token() }}'
        };

        // 🔄 Refresh current active tab
        function refreshCurrentTab() {
            const activeTabBtn = document.querySelector('.tab-btn.active');
            if (!activeTabBtn) return;

            const tabName = activeTabBtn.dataset.tab;
            console.log('🔄 Manually refreshing tab:', tabName);

            // Add spinning animation to refresh button
            const refreshBtn = event.target.closest('button');
            const refreshIcon = refreshBtn.querySelector('i');
            const originalClass = refreshIcon.className;

            refreshIcon.className = 'fas fa-spinner fa-spin';
            refreshBtn.disabled = true;

            // Show loading indicator and reload data
            showTabLoadingIndicator(tabName);
            loadTabData(tabName);

            // Restore refresh button after delay
            setTimeout(() => {
                refreshIcon.className = originalClass;
                refreshBtn.disabled = false;
            }, 1500);
        }

        // 🔧 Optimized scroll scheduling to prevent excessive DOM updates
        let scrollScheduled = false;
        function scheduleScrollToBottom() {
            if (scrollScheduled) return;

            scrollScheduled = true;
            requestAnimationFrame(() => {
                const messagesArea = document.querySelector('.modern-messages-container');
                if (messagesArea) {
                    messagesArea.scrollTo({
                        top: messagesArea.scrollHeight,
                        behavior: 'smooth'
                    });
                }
                scrollScheduled = false;
            });
        }

        // 🔧 Force immediate scroll to bottom (for when opening conversations)
        function forceScrollToBottom() {
            const messagesArea = document.querySelector('.modern-messages-container');
            if (messagesArea) {
                // Use both smooth scroll and immediate scroll for reliability
                messagesArea.scrollTo({
                    top: messagesArea.scrollHeight,
                    behavior: 'smooth'
                });

                // Backup: Force immediate scroll after a short delay
                setTimeout(() => {
                    messagesArea.scrollTop = messagesArea.scrollHeight;
                }, 100);
            }
        }

        // Emoji Picker Functions
        function toggleEmojiPicker() {
            const emojiPicker = document.getElementById('emoji-picker');
            const emojiToggle = document.getElementById('emojiToggle');

            if (emojiPicker.style.display === 'block') {
                emojiPicker.style.display = 'none';
                emojiToggle.classList.remove('active');
            } else {
                emojiPicker.style.display = 'block';
                emojiToggle.classList.add('active');
            }
        }

        function insertEmoji(emoji) {
            const messageInput = document.getElementById('messageInput');
            const start = messageInput.selectionStart;
            const end = messageInput.selectionEnd;
            const text = messageInput.value;

            messageInput.value = text.substring(0, start) + emoji + text.substring(end);
            messageInput.selectionStart = messageInput.selectionEnd = start + emoji.length;
            messageInput.focus();

            // Close emoji picker after selection
            toggleEmojiPicker();
        }

        // Close emoji picker when clicking outside
        document.addEventListener('click', function (event) {
            const emojiPicker = document.getElementById('emoji-picker');
            const emojiToggle = document.getElementById('emojiToggle');

            if (emojiPicker && !emojiPicker.contains(event.target) && !emojiToggle.contains(event.target)) {
                emojiPicker.style.display = 'none';
                emojiToggle.classList.remove('active');
            }
        });

        // File Upload Functions
        function showFileUploadOptions() {
            const input = document.createElement('input');
            input.type = 'file';
            input.multiple = true;
            input.accept = 'image/*,video/*,.pdf,.doc,.docx,.txt,.zip,.rar';

            input.onchange = function (event) {
                const files = event.target.files;
                if (files.length > 0) {
                    uploadFiles(files);
                }
            };

            input.click();
        }

        async function uploadFiles(files) {
            if (!currentConversationId) {
                alert('Veuillez sélectionner une conversation');
                return;
            }

            for (let file of files) {
                try {
                    console.log('📤 Uploading file:', file.name);

                    // Show upload progress in UI
                    addUploadProgressToUI(file.name);

                    // Upload to Firebase Storage
                    const downloadURL = await uploadFileToFirebase(file);

                    // Send file message
                    await sendFileMessage(file.name, downloadURL, file.type);

                    console.log('✅ File uploaded successfully:', downloadURL);

                } catch (error) {
                    console.error('❌ Error uploading file:', error);
                    alert(`Erreur lors de l'upload de ${file.name}: ${error.message}`);
                }
            }
        }

        async function uploadFileToFirebase(file) {
            const maxRetries = 3;
            let retryCount = 0;

            const tryUpload = async () => {
                if (!window.firebaseStorage) {
                    if (retryCount < maxRetries) {
                        console.log(`🔄 Retrying Firebase Storage initialization (${retryCount + 1}/${maxRetries})...`);
                        retryCount++;
                        return new Promise((resolve) => setTimeout(resolve, 1000)).then(tryUpload);
                    }
                    throw new Error('Firebase Storage not initialized');
                }

                const userId = {{ auth()->user()->id }};
                const timestamp = Date.now();
                const fileName = `${timestamp}_${file.name}`;
                const filePath = `chat_files/${userId}/${fileName}`;

                const storageRef = window.firebaseStorage.ref(filePath);
                const uploadTask = storageRef.put(file);

                return new Promise((resolve, reject) => {
                    uploadTask.on('state_changed',
                        (snapshot) => {
                            const progress = (snapshot.bytesTransferred / snapshot.totalBytes) * 100;
                            updateUploadProgress(file.name, progress);
                        },
                        (error) => {
                            console.error('❌ Storage upload error:', error);
                            reject(error);
                        },
                        () => {
                            uploadTask.snapshot.ref.getDownloadURL().then((downloadURL) => {
                                console.log('🔗 Generated downloadURL:', downloadURL);
                                resolve(downloadURL);
                            }).catch((error) => {
                                console.error('❌ Error getting downloadURL:', error);
                                reject(error);
                            });
                        }
                    );
                });
            };

            return tryUpload();
        }
        function addUploadProgressToUI(fileName) {
            const container = document.getElementById('messages-container');
            const progressDiv = document.createElement('div');
            progressDiv.className = 'upload-progress-item';
            progressDiv.id = `upload-${fileName.replace(/[^a-zA-Z0-9]/g, '')}`;

            progressDiv.innerHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="upload-info">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <i class="fas fa-file-upload mr-2"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <span>Envoi de ${fileName}</span>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="progress-bar">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="progress-fill" style="width: 0%"></div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            `;

            container.appendChild(progressDiv);
            scheduleScrollToBottom();
        }

        function updateUploadProgress(fileName, progress) {
            const uploadItem = document.getElementById(`upload-${fileName.replace(/[^a-zA-Z0-9]/g, '')}`);
            if (uploadItem) {
                const progressFill = uploadItem.querySelector('.progress-fill');
                progressFill.style.width = `${progress}%`;

                if (progress >= 100) {
                    setTimeout(() => uploadItem.remove(), 1000);
                }
            }
        }

        async function sendFileMessage(fileName, downloadURL, fileType) {
            const isImage = fileType.startsWith('image/');
            const isVideo = fileType.startsWith('video/');

            const messageData = {
                text: isImage ? `📷 Image: ${fileName}` : isVideo ? `🎥 Vidéo: ${fileName}` : `📎 Fichier: ${fileName}`,
                fileUrl: downloadURL,
                fileName: fileName,
                fileType: fileType,
                type: isImage ? 'image' : isVideo ? 'video' : 'file',
                time: Date.now(), // Changed to number
                sender: {
                    id: ({{ auth()->user()->id }}).toString(),
                    name: '{{ auth()->user()->name }}',
                    imageUrl: '{{ auth()->user()->avatar ?? "/images/default-avatar.png" }}',
                    auth: true
                },
                status: 'sent'
            };

            // Add file info for direct messages
            if (currentConversationId.includes('-')) {
                const receiverId = currentConversationId.split('-').find(id => id !== ({{ auth()->user()->id }}).toString());
                messageData.receiver = {
                    id: receiverId,
                    name: document.getElementById('chat-contact-name')?.textContent || 'Unknown',
                    imageUrl: document.getElementById('chat-avatar')?.src || '/images/default-avatar.png',
                    auth: false
                };
            }

            // Send to Firebase
            await sendMessageToServer(currentConversationId, '', messageData);

            // Add to UI
            //addFileMessageToUI(messageData, true);
        }

        function addFileMessageToUI(messageData, isOwn = false) {
            const container = document.getElementById('messages-container');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message-item ${isOwn ? 'own' : ''}`;

            // Store message data for deletion
            const messageId = messageData.messageId || Date.now().toString();
            messageDiv.dataset.messageId = messageId;
            messageDiv.dataset.conversationId = currentConversationId;
            messageDiv.dataset.isGroup = !currentConversationId.includes('-') || currentConversationId.length > 10;

            const time = new Date(parseInt(messageData.time)).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const isImage = messageData.type === 'image';
            const isVideo = messageData.type === 'video';
            const isPdf = messageData.type === 'file' && messageData.fileName && messageData.fileName.toLowerCase().includes('.pdf');

            // 🔧 FIXED: Use messageData.type instead of undefined messageType
            const messageType = messageData.type || 'file';

            // Add delete button for own messages (exclude images since they have their own context menu)
            const deleteButton = isOwn && messageType !== 'image' ? `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="message-delete-btn ${messageType === 'text' ? 'text-delete' : 'file-delete'}" onclick="deleteMessage(this)" title="Supprimer le message">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <i class="fas fa-trash"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ` : '';

            if (isOwn) {
                if (isImage) {
                    // Add message menu for image messages
                    const messageMenu = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="message-menu-icon" style="position: absolute; top: 8px; right: 8px; opacity: 0; transition: opacity 0.3s ease; background: rgba(0,0,0,0.7); color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 10;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0'" onclick="event.preventDefault(); event.stopPropagation(); toggleMessageMenu(this, '${messageData.fileUrl}', '${messageData.fileName}', ${isOwn}); return false;">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <i class="fas fa-ellipsis-v" style="font-size: 10px; pointer-events: none;"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="message-context-menu" style="position: absolute; top: 35px; right: 8px; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); min-width: 120px; opacity: 0; visibility: hidden; transform: translateY(-10px); transition: all 0.2s ease; z-index: 1000;">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="menu-item" style="padding: 10px 16px; cursor: pointer; border-bottom: 1px solid #f0f0f0; font-size: 14px; color: #333; transition: background 0.2s ease;" onclick="downloadImage('${messageData.fileUrl}', '${messageData.fileName}')">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <i class="fas fa-download" style="margin-right: 8px; color: #667eea;"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            Télécharger
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="menu-item delete-item" style="padding: 10px 16px; cursor: pointer; font-size: 14px; color: #ef4444; transition: background 0.2s ease;" onclick="deleteMessage(this.closest('.message-item'))">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <i class="fas fa-trash" style="margin-right: 8px;"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            Supprimer
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    `;

                    messageDiv.innerHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="message-bubble" style="position: relative;" onmouseover="showMessageMenu(this)" onmouseout="hideMessageMenu(this)">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div style="position: relative; display: inline-block;">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <img src="${messageData.fileUrl}" alt="${messageData.fileName}" style="max-width: 200px; max-height: 200px; border-radius: 8px; cursor: pointer;" onclick="window.open('${messageData.fileUrl}', '_blank')">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <small class="message-time">${time}</small>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        ${messageMenu}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    `;
                } else if (isVideo) {
                    messageDiv.innerHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="message-bubble" style="position: relative;">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="message-video">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <video controls>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <source src="${messageData.fileUrl}" type="${messageData.fileType}">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </video>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <small class="message-time">${time}</small>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        `;
                } else if (isPdf) {
                    // Display PDF with cover image (same as displayFirebaseMessage)
                    messageDiv.innerHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="message-bubble" style="position: relative;">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div style="display: flex; align-items: center; padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 4px; cursor: pointer;" onclick="window.open('${messageData.fileUrl}', '_blank')">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <img src="/images/pdf-cover.png" alt="PDF" style="width: 40px; height: 40px; margin-right: 12px; border-radius: 4px;">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div style="font-weight: 500; color: #374151;">${messageData.fileName}</div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div style="font-size: 12px; color: #6b7280;">PDF Document</div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <small class="message-time">${time}</small>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ${deleteButton}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    `;
                } else {
                    // Other file types
                    messageDiv.innerHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="message-bubble" style="position: relative;">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div style="display: flex; align-items: center; padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 4px;">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <i class="fas fa-file" style="margin-right: 8px; color: #667eea;"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <a href="${messageData.fileUrl}" target="_blank" style="color: #667eea; text-decoration: none; font-weight: 500;">${messageData.fileName}</a>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <small class="message-time">${time}</small>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ${deleteButton}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    `;
                }
            }

            container.appendChild(messageDiv);
            forceScrollToBottom();
        }

        function openImageModal(imageUrl) {
            const modal = document.createElement('div');
            modal.className = 'image-modal';
            modal.style.zIndex = '20000'; // Higher than shared images modal (10000)
            modal.innerHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="image-modal-content">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <img src="${imageUrl}" alt="Image">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <button class="image-modal-close" onclick="this.parentElement.parentElement.remove()">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <i class="fas fa-times"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </button>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            `;
            document.body.appendChild(modal);
        }

        // Chat Sidebar Functions
        function toggleChatSidebar() {
            const sidebar = document.getElementById('chat-sidebar');

            if (sidebar.classList.contains('open')) {
                closeChatSidebar();
            } else {
                openChatSidebar();
            }
        }

        function openChatSidebar() {
            const sidebar = document.getElementById('chat-sidebar');
            const toggle = document.getElementById('sidebarToggle');
            const container = document.querySelector('.modern-messenger-container');

            sidebar.classList.add('open');
            toggle.classList.add('active');

            // Add backdrop class for mobile
            if (container) {
                container.classList.add('sidebar-open');
            }

            loadChatSidebarData();
        }

        function closeChatSidebar() {
            const sidebar = document.getElementById('chat-sidebar');
            const toggle = document.getElementById('sidebarToggle');
            const backdrop = document.getElementById('sidebar-backdrop');

            sidebar.classList.remove('open');
            toggle.classList.remove('active');

            // Hide backdrop
            if (backdrop) {
                backdrop.classList.remove('show');
            }
        }

        function loadChatSidebarData() {
            if (!currentConversationId) return;

            // Get elements and check if they exist before accessing properties
            const contactNameEl = document.getElementById('chat-contact-name');
            const contactAvatarEl = document.getElementById('chat-avatar');
            const contactStatusEl = document.getElementById('chat-contact-status');

            const sidebarContactNameEl = document.getElementById('sidebar-contact-name');
            const sidebarAvatarEl = document.getElementById('sidebar-avatar');
            const sidebarContactStatusEl = document.getElementById('sidebar-contact-status');

            // Only proceed if all required elements exist
            if (contactNameEl && contactAvatarEl && contactStatusEl &&
                sidebarContactNameEl && sidebarAvatarEl && sidebarContactStatusEl) {

                // Update contact info in sidebar
                const contactName = contactNameEl.textContent;
                const contactAvatar = contactAvatarEl.src;
                const contactStatus = contactStatusEl.textContent;

                sidebarContactNameEl.textContent = contactName;
                sidebarAvatarEl.src = contactAvatar;
                sidebarContactStatusEl.textContent = contactStatus;
            } else {
                console.warn('Some required elements for sidebar not found:', {
                    'chat-contact-name': !!contactNameEl,
                    'chat-avatar': !!contactAvatarEl,
                    'chat-contact-status': !!contactStatusEl,
                    'sidebar-contact-name': !!sidebarContactNameEl,
                    'sidebar-avatar': !!sidebarAvatarEl,
                    'sidebar-contact-status': !!sidebarContactStatusEl
                });
            }

            // Load shared media regardless of contact info availability
            loadSharedMedia();
        }

        async function loadSharedMedia() {
            try {
                console.log('📥 Loading shared media for conversation:', currentConversationId);

                if (!window.firebaseDb) {
                    console.error('❌ Firebase not initialized');
                    return;
                }

                const db = window.firebaseDb;
                const isGroupConversation = !currentConversationId.includes('-') || currentConversationId.length > 10;

                let messagesSnapshot;

                if (isGroupConversation) {
                    // For group conversations - get all messages first, then filter
                    console.log('📥 Loading group media...');
                    messagesSnapshot = await db.collection('groups')
                        .doc(currentConversationId)
                        .collection('messages')
                        .limit(100) // Get more messages to find media
                        .get();
                } else {
                    // For direct conversations - try multiple possible conversation IDs
                    console.log('📥 Loading direct conversation media...');

                    let possibleIds = [currentConversationId];

                    // If the conversation ID contains a dash, try both original and normalized versions
                    if (currentConversationId.includes('-')) {
                        const parts = currentConversationId.split('-');
                        if (parts.length === 2) {
                            const userIds = [parseInt(parts[0]), parseInt(parts[1])];
                            // Add both possible combinations
                            possibleIds = [
                                currentConversationId, // Original
                                `${userIds[0]}-${userIds[1]}`, // As-is
                                `${userIds[1]}-${userIds[0]}`, // Reversed
                                `${Math.min(...userIds)}-${Math.max(...userIds)}` // Normalized (smaller first)
                            ];
                            // Remove duplicates
                            possibleIds = [...new Set(possibleIds)];
                        }
                    }

                    console.log('🔍 Trying conversation IDs:', possibleIds);

                    // Try each possible ID until we find one that exists
                    messagesSnapshot = null;
                    for (const id of possibleIds) {
                        try {
                            const snapshot = await db.collection('messages')
                                .doc(id)
                                .collection('chats')
                                .limit(100)
                                .get();

                            if (!snapshot.empty) {
                                console.log('✅ Found messages with ID:', id);
                                messagesSnapshot = snapshot;
                                break;
                            } else {
                                console.log('📭 No messages found with ID:', id);
                            }
                        } catch (error) {
                            console.log('❌ Error trying ID:', id, error.message);
                        }
                    }

                    // If no messages found with any ID, create empty snapshot
                    if (!messagesSnapshot) {
                        console.log('📭 No messages found with any conversation ID');
                        messagesSnapshot = { empty: true, forEach: () => { } };
                    }
                }

                const images = [];
                const files = [];
                const links = [];

                if (!messagesSnapshot.empty) {
                    // Process all messages and filter on client side
                    const allMessages = [];
                    messagesSnapshot.forEach((doc) => {
                        const data = doc.data();
                        const timestamp = isGroupConversation ?
                            (data.timestamp || 0) :
                            parseInt(data.time || 0);

                        allMessages.push({
                            ...data,
                            timestamp: timestamp
                        });
                    });

                    // Sort by timestamp (newest first) on client side
                    allMessages.sort((a, b) => b.timestamp - a.timestamp);

                    // Filter and categorize media
                    allMessages.forEach((data) => {
                        // Only process messages with media
                        if (data.type === 'image' && data.fileUrl) {
                            images.push(data);
                        } else if ((data.type === 'video' || data.type === 'file') && data.fileUrl) {
                            files.push(data);
                        }

                        // Extract links from text messages
                        if (data.text) {
                            const urlRegex = /(https?:\/\/[^\s]+)/g;
                            const foundLinks = data.text.match(urlRegex);
                            if (foundLinks) {
                                foundLinks.forEach(link => {
                                    links.push({
                                        url: link,
                                        text: data.text,
                                        time: data.time || data.timestamp,
                                        sender: data.sender
                                    });
                                });
                            }
                        }
                    });
                }

                console.log('✅ Shared media loaded:', {
                    images: images.length,
                    files: files.length,
                    links: links.length
                });

                displaySharedImages(images);
                displaySharedFiles(files);
                displaySharedLinks(links);

            } catch (error) {
                console.error('❌ Error loading shared media:', error);

                // Fallback: Show empty state
                document.getElementById('shared-images').innerHTML = '<p class="text-muted text-center">Erreur de chargement</p>';
                document.getElementById('shared-files').innerHTML = '<p class="text-muted text-center">Erreur de chargement</p>';
                document.getElementById('shared-links').innerHTML = '<p class="text-muted text-center">Erreur de chargement</p>';

                // Reset counts
                document.getElementById('images-count').textContent = '0';
                document.getElementById('files-count').textContent = '0';
                document.getElementById('links-count').textContent = '0';
            }
        }

        function displaySharedImages(images) {
            const container = document.getElementById('shared-images');
            const countElement = document.getElementById('images-count');

            countElement.textContent = images.length;

            if (images.length === 0) {
                container.innerHTML = '<p class="text-muted text-center">Aucune image partagée</p>';
                return;
            }

            let html = '';
            images.forEach((image, index) => {
                html += `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="media-item" onclick="openSharedImagesGrid(${index})">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <img src="${image.fileUrl}" alt="${image.fileName}">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                `;
            });

            container.innerHTML = html;

            // Store images globally for grid modal
            window.sharedImagesData = images;
        }

        function displaySharedFiles(files) {
            const container = document.getElementById('shared-files');
            const countElement = document.getElementById('files-count');

            countElement.textContent = files.length;

            if (files.length === 0) {
                container.innerHTML = '<p class="text-muted text-center">Aucun fichier partagé</p>';
                return;
            }

            let html = '';
            files.forEach((file, index) => {
                html += `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="file-grid-item" onclick="openSharedFilesGrid(${index})">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="file-grid-icon">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <i class="fas ${file.type === 'video' ? 'fa-video' : 'fa-file'}"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="file-grid-name">${file.fileName}</div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                `;
            });

            container.innerHTML = html;

            // Store files globally for grid modal
            window.sharedFilesData = files;
        }

        function displaySharedLinks(links) {
            const container = document.getElementById('shared-links');
            const countElement = document.getElementById('links-count');

            countElement.textContent = links.length;

            if (links.length === 0) {
                container.innerHTML = '<p class="text-muted text-center">Aucun lien partagé</p>';
                return;
            }

            let html = '';
            links.forEach(link => {
                const time = new Date(parseInt(link.time)).toLocaleDateString();
                const domain = extractDomain(link.url);
                const shortUrl = link.url.length > 40 ? link.url.substring(0, 37) + '...' : link.url;
                const linkText = link.text ? (link.text.length > 50 ? link.text.substring(0, 47) + '...' : link.text) : 'Lien partagé';

                html += `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="link-item">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="link-icon">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <i class="fas fa-link"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="link-info">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="link-domain">${domain}</div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="link-text">${linkText}</div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="link-url">${shortUrl}</div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="link-date">${time}</div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="link-actions">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <a href="${link.url}" target="_blank" class="btn btn-sm btn-outline-primary" title="Ouvrir le lien">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <i class="fas fa-external-link-alt"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </a>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                `;
            });

            container.innerHTML = html;
        }

        // Download image function
        async function downloadImage(url, fileName) {
            console.log('Downloading image:', url, fileName);

            try {
                // Method 1: Use fetch with proper CORS handling
                const response = await fetch(url, {
                    mode: 'cors'
                });

                if (response.ok) {
                    const blob = await response.blob();
                    const downloadUrl = URL.createObjectURL(blob);

                    const link = document.createElement('a');
                    link.href = downloadUrl;
                    link.download = fileName || 'image.jpg';
                    link.style.display = 'none';

                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    // Clean up
                    URL.revokeObjectURL(downloadUrl);
                    console.log('✅ Download completed for:', fileName);
                } else {
                    throw new Error('Network response was not ok');
                }
            } catch (error) {
                console.warn('⚠️ Fetch failed, trying alternative method:', error);

                // Method 2: Direct download attempt
                try {
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = fileName || 'image.jpg';
                    link.setAttribute('download', ''); // Force download attribute
                    link.target = '_blank'; // Open in new tab if download fails
                    link.style.display = 'none';

                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    console.log('🔄 Alternative download attempted for:', fileName);
                } catch (alternativeError) {
                    console.error('❌ All download methods failed:', alternativeError);
                    // Last resort: open in new tab
                    window.open(url, '_blank');
                }
            }
        }

        // Open shared images in grid modal
        function openSharedImagesGrid(startIndex = 0) {
            const images = window.sharedImagesData || [];
            if (images.length === 0) return;

            const modal = document.createElement('div');
            modal.className = 'shared-images-modal';
            modal.innerHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="shared-images-modal-content">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="shared-images-header">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <h5><i class="fas fa-images mr-2"></i>Images partagées (${images.length})</h5>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <button class="shared-images-close" onclick="this.parentElement.parentElement.parentElement.remove()">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <i class="fas fa-times"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </button>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="shared-images-grid">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ${images.map((image, index) => `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="shared-image-item ${index === startIndex ? 'active' : ''}" onclick="selectSharedImage(${index})">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <img src="${image.fileUrl}" alt="${image.fileName}">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="shared-image-overlay">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <button class="shared-image-download" onclick="event.stopPropagation(); downloadImage('${image.fileUrl}', '${image.fileName}')">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <i class="fas fa-download"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </button>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <button class="shared-image-fullscreen" onclick="event.stopPropagation(); openImageModal('${image.fileUrl}')">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <i class="fas fa-expand"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </button>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            `).join('')}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="shared-images-footer">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="shared-image-info">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <span id="current-image-name">${images[startIndex]?.fileName || 'Image'}</span>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <small id="current-image-time">${images[startIndex]?.time ? new Date(parseInt(images[startIndex].time)).toLocaleDateString() : ''}</small>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="shared-images-actions">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <button class="btn btn-sm btn-primary" onclick="downloadImage('${images[startIndex]?.fileUrl}', '${images[startIndex]?.fileName}')">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <i class="fas fa-download mr-1"></i>Télécharger
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </button>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <button class="btn btn-sm btn-secondary ml-2" onclick="openImageModal('${images[startIndex]?.fileUrl}')">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <i class="fas fa-eye mr-1"></i>Voir en grand
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </button>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                `;

            document.body.appendChild(modal);

            // Store current selection
            window.currentSharedImageIndex = startIndex;
        }

        // Select shared image in grid
        function selectSharedImage(index) {
            const images = window.sharedImagesData || [];
            if (!images[index]) return;

            // Update active state
            document.querySelectorAll('.shared-image-item').forEach((item, i) => {
                item.classList.toggle('active', i === index);
            });

            // Update info
            const image = images[index];
            document.getElementById('current-image-name').textContent = image.fileName || 'Image';
            document.getElementById('current-image-time').textContent = image.time ? new Date(parseInt(image.time)).toLocaleDateString() : '';

            // Update action buttons
            const downloadBtn = document.querySelector('.shared-images-actions .btn-primary');
            const viewBtn = document.querySelector('.shared-images-actions .btn-secondary');

            downloadBtn.onclick = () => downloadImage(image.fileUrl, image.fileName);
            viewBtn.onclick = () => openImageModal(image.fileUrl);

            window.currentSharedImageIndex = index;
        }

        // Open shared files in grid modal
        function openSharedFilesGrid(startIndex = 0) {
            const files = window.sharedFilesData || [];
            if (files.length === 0) return;

            const modal = document.createElement('div');
            modal.className = 'shared-files-modal';
            modal.innerHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="shared-files-modal-content">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="shared-files-header">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <h5><i class="fas fa-file mr-2"></i>Fichiers partagés (${files.length})</h5>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <button class="shared-files-close" onclick="this.parentElement.parentElement.parentElement.remove()">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <i class="fas fa-times"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </button>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="shared-files-grid">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    ${files.map((file, index) => `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="shared-file-item ${index === startIndex ? 'active' : ''}" onclick="selectSharedFile(${index})">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="shared-file-icon">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <i class="fas ${file.type === 'video' ? 'fa-video' : 'fa-file'}"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="shared-file-name">${file.fileName}</div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="shared-file-overlay">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <button class="shared-file-download" onclick="event.stopPropagation(); window.open('${file.fileUrl}', '_blank')">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <i class="fas fa-download"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </button>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <button class="shared-file-open" onclick="event.stopPropagation(); window.open('${file.fileUrl}', '_blank')">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <i class="fas fa-external-link-alt"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </button>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    `).join('')}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="shared-files-footer">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="shared-file-info">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <span id="current-file-name">${files[startIndex]?.fileName || 'Fichier'}</span>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <small id="current-file-time">${files[startIndex]?.time ? new Date(parseInt(files[startIndex].time)).toLocaleDateString() : ''}</small>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="shared-files-actions">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <button class="btn btn-sm btn-primary" onclick="window.open('${files[startIndex]?.fileUrl}', '_blank')">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <i class="fas fa-download mr-1"></i>Télécharger
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </button>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <button class="btn btn-sm btn-secondary ml-2" onclick="window.open('${files[startIndex]?.fileUrl}', '_blank')">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <i class="fas fa-eye mr-1"></i>Ouvrir
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </button>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        `;

            document.body.appendChild(modal);

            // Store current selection
            window.currentSharedFileIndex = startIndex;
        }

        // Select shared file in grid
        function selectSharedFile(index) {
            const files = window.sharedFilesData || [];
            if (!files[index]) return;

            // Update active state
            document.querySelectorAll('.shared-file-item').forEach((item, i) => {
                item.classList.toggle('active', i === index);
            });

            // Update info
            const file = files[index];
            document.getElementById('current-file-name').textContent = file.fileName || 'Fichier';
            document.getElementById('current-file-time').textContent = file.time ? new Date(parseInt(file.time)).toLocaleDateString() : '';

            // Update action buttons
            const downloadBtn = document.querySelector('.shared-files-actions .btn-primary');
            const viewBtn = document.querySelector('.shared-files-actions .btn-secondary');

            downloadBtn.onclick = () => window.open(file.fileUrl, '_blank');
            viewBtn.onclick = () => window.open(file.fileUrl, '_blank');

            window.currentSharedFileIndex = index;
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function (event) {
            const sidebar = document.getElementById('chat-sidebar');
            const toggle = document.getElementById('sidebarToggle');
            const backdrop = document.getElementById('sidebar-backdrop');

            // Only handle this on mobile (when backdrop is visible)
            if (window.innerWidth <= 1407 && sidebar && sidebar.classList.contains('open')) {
                // Check if click is outside sidebar and not on the toggle button
                if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                    closeChatSidebar();
                }
            }
        });

        // 🗑️ Delete Message Function
        async function deleteMessage(deleteButton) {
            const messageItem = deleteButton.closest('.message-item');
            if (!messageItem) return;

            const messageId = messageItem.dataset.messageId;
            const conversationId = messageItem.dataset.conversationId;
            const isGroup = messageItem.dataset.isGroup === 'true';

            // Show confirmation dialog
            if (!confirm('Êtes-vous sûr de vouloir supprimer ce message ?')) {
                return;
            }

            try {
                console.log('🗑️ Deleting message:', messageId, 'from conversation:', conversationId);

                if (!window.firebaseDb) {
                    console.error('❌ Firebase not initialized');
                    alert('Erreur: Firebase non initialisé');
                    return;
                }

                const db = window.firebaseDb;

                // Delete from Firebase based on conversation type
                if (isGroup) {
                    // Group message: Delete from groups/{groupId}/messages/{messageId}
                    await db.collection('groups')
                        .doc(conversationId)
                        .collection('messages')
                        .doc(messageId)
                        .delete();

                    console.log('✅ Group message deleted from Firebase');
                } else {
                    // Direct message: Delete from messages/{conversationId}/chats/{messageId}
                    // Normalize conversation ID
                    let normalizedId = conversationId;
                    if (conversationId.includes('-')) {
                        const parts = conversationId.split('-');
                        if (parts.length === 2) {
                            const userIds = [parseInt(parts[0]), parseInt(parts[1])].sort((a, b) => a - b);
                            normalizedId = `${userIds[0]}-${userIds[1]}`;
                        }
                    }

                    await db.collection('messages')
                        .doc(normalizedId)
                        .collection('chats')
                        .doc(messageId)
                        .delete();

                    console.log('✅ Direct message deleted from Firebase');
                }

                // Remove from UI with animation
                messageItem.style.opacity = '0';
                messageItem.style.transform = 'translateX(-20px)';
                messageItem.style.transition = 'all 0.3s ease';

                setTimeout(() => {
                    messageItem.remove();
                    console.log('✅ Message removed from UI');
                }, 300);

                // Optional: Log deletion to Laravel backend
                try {
                    await fetch('/messenger/log-message-deletion', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            message_id: messageId,
                            conversation_id: conversationId,
                            is_group: isGroup
                        })
                    });
                    console.log('✅ Message deletion logged to backend');
                } catch (logError) {
                    console.warn('⚠️ Failed to log deletion to backend (non-critical):', logError);
                }

            } catch (error) {
                console.error('❌ Error deleting message:', error);
                alert('Erreur lors de la suppression du message: ' + error.message);
            }
        }

        // Helper functions for message menu display
        function showMessageMenu(container) {
            const menuIcon = container.querySelector('.message-menu-icon');
            if (menuIcon) menuIcon.style.opacity = '1';
        }

        function hideMessageMenu(container) {
            const menuIcon = container.querySelector('.message-menu-icon');
            if (menuIcon) menuIcon.style.opacity = '0';
        }

        // Toggle message context menu
        function toggleMessageMenu(menuIcon, fileUrl, fileName, isOwn) {
            const contextMenu = menuIcon.parentElement.querySelector('.message-context-menu');
            const allMenus = document.querySelectorAll('.message-context-menu');

            // Close all other menus first
            allMenus.forEach(menu => {
                if (menu !== contextMenu) {
                    menu.style.opacity = '0';
                    menu.style.visibility = 'hidden';
                    menu.style.transform = 'translateY(-10px)';
                }
            });

            // Toggle current menu
            if (contextMenu.style.opacity === '1') {
                contextMenu.style.opacity = '0';
                contextMenu.style.visibility = 'hidden';
                contextMenu.style.transform = 'translateY(-10px)';
            } else {
                contextMenu.style.opacity = '1';
                contextMenu.style.visibility = 'visible';
                contextMenu.style.transform = 'translateY(0)';
            }

            // Close menu when clicking outside
            if (contextMenu.style.opacity === '1') {
                document.addEventListener('click', function closeMenu(e) {
                    if (!contextMenu.contains(e.target) && !menuIcon.contains(e.target)) {
                        contextMenu.style.opacity = '0';
                        contextMenu.style.visibility = 'hidden';
                        contextMenu.style.transform = 'translateY(-10px)';
                        document.removeEventListener('click', closeMenu);
                    }
                });
            }
        }

        // 📖 Mark messages as read when user opens conversation
        async function markMessagesAsRead(conversationId) {
            console.log('🔔 Marking messages as read for conversation:', conversationId);
            try {
                if (!window.firebaseDb) {
                    console.warn('⚠️ Firebase not initialized for marking messages as read');
                    return;
                }
                const db = window.firebaseDb;
                const userId = {{ auth()->user()->id }};
                const isGroupConversation = !conversationId.includes('-') || conversationId.length > 10;
                let collectionRef = isGroupConversation
                    ? db.collection('groups').doc(conversationId).collection('messages')
                    : db.collection('messages').doc(conversationId).collection('chats'); // Use original ID

                console.log('📋 Querying collection:', collectionRef.path);

                // Query messages that are not read
                const query = collectionRef
                    .where('receiver.id', '==', userId.toString())
                    .where('status', 'in', ['sent', 'delivered'])
                    .limit(50);
                const snapshot = await query.get();
                console.log('📋 Query snapshot size:', snapshot.size, 'for path:', collectionRef.path);

                if (snapshot.empty) {
                    console.log('📖 No unread messages found in primary path:', conversationId);
                    // Fallback to check inverse ID if direct query fails
                    if (conversationId.includes('-')) {
                        const [firstId, secondId] = conversationId.split('-').map(id => parseInt(id));
                        const inverseId = `${secondId}-${firstId}`;
                        const inverseRef = db.collection('messages').doc(inverseId).collection('chats');
                        console.log('🔍 Falling back to inverse conversation ID:', inverseId);
                        const inverseSnapshot = await inverseRef
                            .where('receiver.id', '==', userId.toString())
                            .where('status', 'in', ['sent', 'delivered'])
                            .limit(50)
                            .get();
                        console.log('📋 Inverse snapshot size:', inverseSnapshot.size, 'for path:', inverseRef.path);
                        if (!inverseSnapshot.empty) {
                            collectionRef = inverseRef;
                            snapshot = inverseSnapshot;
                        }
                    }
                }

                if (snapshot.empty) {
                    console.log('📖 No unread messages to mark as read in conversation:', conversationId);
                    clearUnreadCount(conversationId);
                    refreshConversationItem(conversations.find(c => c.id === conversationId));
                    return;
                }

                // Batch update messages to 'read'
                const batch = db.batch();
                snapshot.forEach(doc => {
                    const data = doc.data();
                    console.log('📋 Processing message:', doc.id, 'status:', data.status, 'receiver.id:', data.receiver?.id, 'time:', data.time);
                    batch.update(doc.ref, {
                        status: 'read',
                        read_at: Date.now() // Numeric timestamp
                    });
                });
                await batch.commit();
                console.log('📖 Marked', snapshot.size, 'messages as read in conversation:', conversationId);

                // Clear unread count and refresh conversation item
                clearUnreadCount(conversationId);
                const conv = conversations.find(c => c.id === conversationId);
                if (conv) {
                    conv.unread_count = 0; // Ensure unread count is cleared
                    refreshConversationItem(conv);
                    console.log('🔄 Refreshed conversation item after marking as read:', conversationId);
                }
            } catch (error) {
                console.error('❌ Error marking messages as read:', error);
            }
        }

        // 📖 Update message status icon when status changes
        function updateMessageStatusIcon(messageId, newStatus) {
            const messageElement = document.querySelector(`[data-message-id="${messageId}"]`);
            if (!messageElement) return;

            const statusIcon = messageElement.querySelector('.message-status-icon i');
            if (!statusIcon) return;

            if (newStatus === 'read') {
                statusIcon.className = 'fas fa-check-double';
                statusIcon.style.color = '#667eea';
                statusIcon.title = 'Lu';
                console.log('✅ Updated message status icon to read for:', messageId);
            } else if (newStatus === 'sent') {
                statusIcon.className = 'fas fa-check';
                statusIcon.style.color = '#9ca3af';
                statusIcon.title = 'Envoyé';
            }
        }

        // 🔔 Show browser notification for new message
        function showNewMessageNotification(messageData, conversationId) {
            console.log('🔔 Showing new message notification:', messageData, conversationId);
        }

        // 🔔 Show visual alert on page
        function showVisualAlert(senderName, message, conversationId) {
            // Remove existing alerts
            document.querySelectorAll('.message-alert').forEach(alert => alert.remove());

            const alert = document.createElement('div');
            alert.className = 'message-alert';
            alert.style.cssText = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        position: fixed;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        top: 20px;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        right: 20px;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        color: white;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        padding: 16px 20px;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        border-radius: 12px;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        box-shadow: 0 8px 25px rgba(0,0,0,0.3);
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        z-index: 10000;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        max-width: 350px;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        cursor: pointer;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        transform: translateX(400px);
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        border-left: 4px solid #fff;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    `;

            alert.innerHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div style="display: flex; align-items: center; margin-bottom: 8px;">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div style="width: 8px; height: 8px; background: #4ade80; border-radius: 50%; margin-right: 8px; animation: pulse 2s infinite;"></div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <strong style="font-size: 14px;">💬 Nouveau message</strong>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <button onclick="this.parentElement.parentElement.remove()" style="margin-left: auto; background: none; border: none; color: white; font-size: 18px; cursor: pointer; opacity: 0.7; padding: 0; width: 20px; height: 20px;">&times;</button>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div style="font-weight: 600; margin-bottom: 4px; font-size: 15px;">De: ${senderName}</div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div style="font-size: 13px; opacity: 0.9; line-height: 1.4; margin-bottom: 12px;">${message}</div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div style="text-align: center;">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <button onclick="openConversationFromAlert('${conversationId}')" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; padding: 6px 16px; border-radius: 6px; font-size: 12px; cursor: pointer; transition: all 0.2s;">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                📱 Voir la conversation
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </button>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    `;

            // Add pulse animation
            const style = document.createElement('style');
            style.textContent = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        @keyframes pulse {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            0%, 100% { opacity: 1; transform: scale(1); }
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            50% { opacity: 0.7; transform: scale(1.1); }
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        }
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    `;
            document.head.appendChild(style);

            document.body.appendChild(alert);

            // Animate in
            setTimeout(() => {
                alert.style.transform = 'translateX(0)';
            }, 100);

            // Auto remove after 8 seconds
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.style.transform = 'translateX(400px)';
                    setTimeout(() => alert.remove(), 300);
                }
            }, 8000);
        }

        // 🔔 Open conversation from alert
        function openConversationFromAlert(conversationId) {
            // Remove the alert
            document.querySelectorAll('.message-alert').forEach(alert => alert.remove());

            // Switch to conversations tab if not already there
            const conversationsTab = document.querySelector('[data-tab="conversations"]');
            if (conversationsTab && !conversationsTab.classList.contains('active')) {
                conversationsTab.click();
            }

            // Wait a moment for tab to load, then find and click conversation
            setTimeout(() => {
                const conversationElement = document.querySelector(`[onclick*="'${conversationId}'"]`);
                if (conversationElement) {
                    conversationElement.click();
                } else {
                    // If conversation not found, refresh the list
                    loadConversations();
                    setTimeout(() => {
                        const retryElement = document.querySelector(`[onclick*="'${conversationId}'"]`);
                        if (retryElement) {
                            retryElement.click();
                        }
                    }, 1000);
                }
            }, 500);
        }

        // 🔔 Play notification sound
        function playNotificationSound() {
            try {
                // Only try to play sound if user has interacted with the page
                if (!window.userHasInteracted) {
                    console.log('🔇 Skipping notification sound - user has not interacted with page yet');
                    return;
                }

                // Create audio element for notification sound
                const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIG2m98OScTgwOUarm7blmGgU7k9n1unEiBC13yO/eizEIHWq+8+OWT');
                audio.volume = 0.3;
                audio.play().catch(e => console.log('🔇 Could not play notification sound:', e));
            } catch (e) {
                console.log('🔇 Notification sound not available');
            }
        }
        function refreshConversationItem(conversation) {
            if (!conversation) return;
            const container = document.getElementById('conversations-tab');
            if (!container) return;
            let item = container.querySelector(`.conversation-item[onclick*="'${conversation.id}'"]`);
            if (!item) {
                console.log('🔄 Conversation item not found, re-rendering all conversations');
                displayConversations(conversations);
                return;
            }
            // Get corrected avatar and name (unchanged logic)
            let correctedAvatar = conversation.avatar || '/images/default-avatar.png';
            let correctedName = conversation.name;
            if (conversation.other_user && conversation.other_user.id) {
                const otherUserId = parseInt(conversation.other_user.id);
                if (window.patients && Array.isArray(window.patients)) {
                    const patient = window.patients.find(p => p.id === otherUserId);
                    if (patient) {
                        correctedAvatar = patient.avatar || '/images/default-avatar.png';
                        correctedName = `${patient.first_name || ''} ${patient.last_name || ''}`.trim() || `Patient ${patient.id}`;
                    }
                }
                if (correctedAvatar === (conversation.avatar || '/images/default-avatar.png') && window.friends && Array.isArray(window.friends)) {
                    const friend = window.friends.find(f => f.id === otherUserId);
                    if (friend) {
                        correctedAvatar = friend.avatar || '/images/default-avatar.png';
                        correctedName = friend.name;
                    }
                }
            }
            // Update existing item
            item.innerHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <img src="${correctedAvatar}" class="contact-avatar" alt="${correctedName}">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="contact-info">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <h6 class="contact-name">${correctedName}</h6>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <p class="last-message">${conversation.last_message || 'Aucun message'}</p>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="message-time">${conversation.last_message_time || ''}</div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    ${conversation.unread_count ? `<span class="badge badge-primary badge-pill">${conversation.unread_count}</span>` : ''}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                `;
            // Re-attach click event
            item.onclick = () => openConversation(conversation.id, correctedName.replace(/'/g, "\\'"), correctedAvatar);
            // Move item to top if it's the most recent
            const parent = item.parentNode;
            if (parent.firstChild !== item) {
                parent.insertBefore(item, parent.firstChild);
            }
            console.log('🔄 Refreshed conversation item:', conversation.id);
        }
        // 🔔 Flash browser tab
        function flashBrowserTab(message) {
            const originalTitle = document.title;
            let isFlashing = true;
            let flashCount = 0;
            const maxFlashes = 6;

            const flashInterval = setInterval(() => {
                if (flashCount >= maxFlashes) {
                    document.title = originalTitle;
                    clearInterval(flashInterval);
                    return;
                }

                document.title = isFlashing ? message : originalTitle;
                isFlashing = !isFlashing;
                flashCount++;
            }, 1000);

            // Stop flashing when user focuses window
            const stopFlashing = () => {
                document.title = originalTitle;
                clearInterval(flashInterval);
                window.removeEventListener('focus', stopFlashing);
            };
            window.addEventListener('focus', stopFlashing);
        }

        // 🔄 Refresh conversations list to show new messages
        function refreshConversationsList() {
            const activeTab = document.querySelector('.modern-tab-btn.active')?.dataset.tab;
            if (activeTab === 'conversations') {
                console.log('🔄 Refreshing conversations list for new message');
                loadConversations();
            }
        }

        // 🧹 Cleanup real-time listeners
        function cleanupRealtimeListeners() {
            if (window.globalRealtimeUnsubscribe) {
                console.log('🧹 Cleaning up real-time global listener');
                window.globalRealtimeUnsubscribe();
                window.globalRealtimeUnsubscribe = null;
            }

            if (window.notificationPollingInterval) {
                console.log('🧹 Cleaning up notification polling');
                clearInterval(window.notificationPollingInterval);
                window.notificationPollingInterval = null;
            }
        }

        // 🔄 Cleanup on page unload
        window.addEventListener('beforeunload', cleanupMessageListeners);

        // 🔄 Cleanup real-time listeners on page unload
        window.addEventListener('beforeunload', cleanupRealtimeListeners);

        // 🔔 Request notification permission on page load
        document.addEventListener('DOMContentLoaded', function () {
            // Request notification permission
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission().then(permission => {
                    console.log('🔔 Notification permission:', permission);
                });
            }

            // Track user interaction for audio playback
            const trackUserInteraction = () => {
                window.userHasInteracted = true;
                console.log('👆 User interaction detected - audio notifications enabled');
                // Remove listeners after first interaction
                document.removeEventListener('click', trackUserInteraction);
                document.removeEventListener('keydown', trackUserInteraction);
                document.removeEventListener('touchstart', trackUserInteraction);
            };

            document.addEventListener('click', trackUserInteraction);
            document.addEventListener('keydown', trackUserInteraction);
            document.addEventListener('touchstart', trackUserInteraction);
        });

        // 🔄 Optimized conversation updates (instead of full refresh)
        window.updateConversationItem = function (conversationId, messageData) {
            try {
                console.log(`🔄 Updating conversation item: ${conversationId}`, messageData);

                // Find the conversation element in the DOM - try multiple selectors
                let conversationElement = document.querySelector(`[onclick*="'${conversationId}'"]`);

                // If not found, try with double quotes
                if (!conversationElement) {
                    conversationElement = document.querySelector(`[onclick*='"${conversationId}"']`);
                }

                // If still not found, try to find by conversation ID in any attribute
                if (!conversationElement) {
                    conversationElement = document.querySelector(`[onclick*="${conversationId}"]`);
                }

                if (!conversationElement) {
                    console.log(`⚠️ Conversation element not found for ${conversationId}, will need full refresh`);
                    console.log('🔍 Available conversation elements:', document.querySelectorAll('[onclick*="openConversation"]'));
                    return false;
                }

                console.log(`✅ Found conversation element for ${conversationId}:`, conversationElement);

                // Update last message text
                const lastMessageElement = conversationElement.querySelector('.last-message');
                if (lastMessageElement) {
                    const shortMessage = messageData.text && messageData.text.length > 50
                        ? messageData.text.substring(0, 47) + '...'
                        : (messageData.text || 'Nouveau message');
                    lastMessageElement.textContent = shortMessage;
                    console.log(`✅ Updated last message for ${conversationId}: "${shortMessage}"`);
                } else {
                    console.log(`⚠️ Last message element not found in conversation ${conversationId}`);
                    console.log('🔍 Available elements in conversation:', conversationElement.innerHTML);
                }

                // Update timestamp
                const timeElement = conversationElement.querySelector('.message-time');
                if (timeElement && messageData.time) {
                    const messageTime = new Date(parseInt(messageData.time));
                    const formattedTime = window.formatMessageTime ? window.formatMessageTime(messageTime) : messageTime.toLocaleTimeString();
                    timeElement.textContent = formattedTime;
                    console.log(`✅ Updated timestamp for ${conversationId}: "${formattedTime}"`);
                } else {
                    console.log(`⚠️ Time element not found in conversation ${conversationId} or no time data`);
                }

                // Move conversation to top of list (smooth animation)
                const conversationsList = conversationElement.parentElement;
                if (conversationsList && conversationElement !== conversationsList.firstElementChild) {
                    // Add animation class
                    conversationElement.style.transition = 'all 0.3s ease';
                    conversationElement.style.transform = 'translateX(-10px)';
                    conversationElement.style.backgroundColor = '#f0f9ff';

                    // Move to top
                    conversationsList.insertBefore(conversationElement, conversationsList.firstElementChild);

                    // Reset animation after a moment
                    setTimeout(() => {
                        conversationElement.style.transform = 'translateX(0)';
                        conversationElement.style.backgroundColor = '';
                        setTimeout(() => {
                            conversationElement.style.transition = '';
                        }, 300);
                    }, 100);

                    console.log(`✅ Moved conversation ${conversationId} to top`);
                }

                return true; // Successfully updated
            } catch (error) {
                console.warn(`⚠️ Error updating conversation item ${conversationId}:`, error);
                return false;
            }
        };

        // 🕒 Format message time for display
        window.formatMessageTime = function (date) {
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);

            if (diffMins < 1) return 'À l\'instant';
            if (diffMins < 60) return `${diffMins}min`;
            if (diffHours < 24) return `${diffHours}h`;
            if (diffDays < 7) return `${diffDays}j`;

            return date.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit'
            });
        };
    </script>
@endpush

@push('styles')
    <style>
        /* 🎨 Modern Messenger Styles */
        .modern-messenger-container {
            height: calc(100vh - 120px);
            display: flex;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
            transition: all 0.3s ease;
        }

        /* 🔥 Friend Request Styles */
        .pending-friend-request {
            background-color: rgba(255, 193, 7, 0.1) !important;
            border-left: 3px solid #ffc107 !important;
        }

        /* Style for newly accepted friend requests */
        .new-friend-accepted {
            background-color: rgba(40, 167, 69, 0.1) !important;
            border-left: 3px solid #28a745 !important;
            animation: highlight-friend 2s ease-in-out;
        }

        @keyframes highlight-friend {
            0% { background-color: rgba(40, 167, 69, 0.3) !important; }
            100% { background-color: rgba(40, 167, 69, 0.1) !important; }
        }

        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-success {
            background-color: #28a745;
            color: #ffffff;
        }

        .pending-action {
            margin-top: 5px;
            text-align: center;
        }

        .pending-action i {
            font-size: 16px;
            color: #ffc107;
        }

        /* 📱 Modern Sidebar */
        .modern-sidebar {
            width: 380px;
            background: linear-gradient(180deg, #f8f9fb 0%, #ffffff 100%);
            border-right: 1px solid #e5e7eb;
            display: flex;
            flex-direction: column;
        }

        /* 👤 User Profile Header - Standardized height */
        .user-profile-header {
            padding: 12px 24px;
            background: #053178;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 84px;
            min-height: 84px;
            box-sizing: border-box;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar-container {
            position: relative;
        }

        .user-avatar {
            width: 44px;
            /* Match contact avatar size */
            height: 44px;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.3);
            object-fit: cover;
        }

        .online-indicator {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 12px;
            /* Match contact status indicator size */
            height: 12px;
            background: #10b981;
            border: 2px solid white;
            border-radius: 50%;
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
            color: white;
        }

        .user-status {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.8);
        }

        .header-actions {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            width: 40px;
            /* Match chat action button size */
            height: 40px;
            border: none;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .action-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
        }

        /* 🔍 Search Container */
        .search-container {
            padding: 20px;
            background: white;
        }

        .search-input-wrapper {
            position: relative;
            background: #f3f4f6;
            border-radius: 12px;
            overflow: hidden;
        }

        .search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 14px;
        }

        .search-input {
            width: 100%;
            padding: 14px 16px 14px 44px;
            border: none;
            background: transparent;
            font-size: 14px;
            outline: none;
            color: #374151;
        }

        .search-input::placeholder {
            color: #9ca3af;
        }

        /* 📑 Modern Tabs */
        .modern-tabs {
            display: flex;
            flex-direction: row;
            padding: 8px 12px;
            background: white;
            gap: 4px;
            justify-content: space-between;
        }

        .modern-tab-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 10px 12px;
            border: none;
            background: transparent;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            color: #6b7280;
            font-weight: 500;
            white-space: nowrap;
            flex: 1;
            justify-content: center;
            min-width: 0;
        }

        .modern-tab-btn:hover {
            background: #f3f4f6;
            color: #374151;
        }

        .modern-tab-btn.active {
            background: #053178;
            /* Replaced gradient with solid #001f3f */
            color: white;
            /* Kept white text for contrast */
            box-shadow: 0 4px 12px rgba(0, 31, 63, 0.4);
            /* Adjusted shadow to match #001f3f (RGB: 0, 31, 63) */
        }

        .modern-tab-btn i {
            font-size: 10px;
            width: 16px;
            text-align: center;
        }

        .modern-tab-btn span {
            font-size: 12px;
        }

        .tab-indicator {
            position: absolute;
            bottom: 4px;
            left: 50%;
            transform: translateX(-50%);
            width: 20px;
            height: 3px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 2px;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .modern-tab-btn.active .tab-indicator {
            opacity: 1;
        }

        /* Make conversations tab larger to fit the full word */
        .modern-tab-btn[data-tab="conversations"] {
            flex: 1.6;
        }

        /* 📋 Chat List Container */
        .chat-list-container {
            flex: 1;
            overflow: hidden;
            padding: 16px 12px;
        }

        .tab-content {
            display: none;
            height: 100%;
            overflow-y: auto;
        }

        .tab-content.active {
            display: block;
        }

        /* 🔄 Loading State */
        .loading-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 200px;
            color: #6b7280;
        }

        .loading-spinner {
            width: 32px;
            height: 32px;
            border: 3px solid #f3f4f6;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 16px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* 📝 Chat Items */
        .conversation-item,
        .friend-item,
        .patient-item,
        .group-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }

        .conversation-item:hover,
        .friend-item:hover,
        .patient-item:hover,
        .group-item:hover {
            background: #f8fafc;
            transform: translateX(4px);
        }

        .conversation-item .contact-avatar,
        .friend-item .contact-avatar,
        .patient-item .contact-avatar,
        .group-item .contact-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            margin-right: 12px;
            object-fit: cover;
        }

        .contact-info {
            flex: 1;
            min-width: 0;
        }

        .contact-name {
            font-weight: 600;
            font-size: 14px;
            color: #374151;
            margin: 0 0 4px 0;
        }

        .last-message {
            font-size: 13px;
            color: #6b7280;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .message-time {
            font-size: 12px;
            color: #9ca3af;
            white-space: nowrap;
        }

        /* 🔧 Create Group Section */
        .create-group-section {
            padding: 12px 16px 20px;
        }

        .create-group-btn {
            width: 100%;
            padding: 12px 16px;
            background: #11b8aa;
            color: white;
            border: none;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: transform 0.2s ease;
            font-weight: 500;
        }

        .create-group-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }

        /* 🔍 Add Friend Section */
        .add-friend-section {
            padding: 16px;
        }

        .search-user-container {
            margin-bottom: 20px;
        }

        .search-label {
            display: block;
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .email-search-wrapper {
            display: flex;
            gap: 8px;
        }

        .email-search-input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
        }

        .email-search-input:focus {
            border-color: #218838;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .search-user-btn {
            padding: 12px 16px;
            background: #218838;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .search-user-btn:hover {
            background: #218838;
        }

        /* 💬 Modern Chat Area */
        .modern-chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #ffffff;
            transition: all 0.3s ease;
        }

        /* 🎉 Welcome Screen */
        .welcome-screen {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }

        .welcome-content {
            text-align: center;
            max-width: 400px;
            padding: 40px;
        }

        .welcome-icon {
            font-size: 64px;
            color: #11b8aa;
            margin-bottom: 24px;
        }

        .welcome-title {
            font-size: 24px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 12px;
        }

        .welcome-text {
            font-size: 16px;
            color: #6b7280;
            margin-bottom: 32px;
            line-height: 1.5;
        }

        .welcome-features {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #6b7280;
            font-size: 14px;
        }

        .feature-item i {
            color: #11b8aa;
            width: 20px;
        }

        /* 💬 Modern Chat Interface */
        .modern-chat-interface {
            display: none;
            flex-direction: column;
            height: 100%;
        }

        /* 📱 Modern Chat Header - Standardized height */
        .modern-chat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            /* Consistent with other headers */
            background: white;
            border-bottom: 1px solid #e5e7eb;
            min-height: 84px;
            /* Ensure consistent height across all headers */
            box-sizing: border-box;
        }

        .chat-contact-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .contact-avatar-container {
            position: relative;
        }

        .contact-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
        }

        .contact-status-indicator {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 12px;
            height: 12px;
            background: #11b8aa;
            border: 2px solid white;
            border-radius: 50%;
        }

        .contact-details {
            display: flex;
            flex-direction: column;
        }

        .contact-name {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
            color: #374151;
        }

        .contact-status {
            font-size: 13px;
            color: #10b981;
        }

        .chat-actions {
            display: flex;
            gap: 8px;
        }

        .chat-action-btn {
            width: 40px;
            height: 40px;
            border: none;
            background: #f3f4f6;
            color: #6b7280;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .chat-action-btn:hover {
            background: #e5e7eb;
            color: #374151;
            transform: scale(1.05);
        }

        /* 💬 Modern Messages Container */
        .modern-messages-container {
            flex: 1;
            padding: 24px;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            overflow-y: auto;
            scroll-behavior: smooth;
        }

        .messages-wrapper {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        /* 💬 Message Bubbles */
        .message-item {
            display: flex;
            margin-bottom: 16px;
            position: relative;
        }

        .message-bubble {
            max-width: 60%;
            padding: 12px 16px;
            border-radius: 18px;
            position: relative;
            word-wrap: break-word;
        }

        .message-item .message-bubble {
            background: white;
            color: #374151;
            border-bottom-left-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .message-item.own {
            justify-content: flex-end;
        }

        .message-item.own .message-bubble {
            background: #053178;
            color: white;
            border-bottom-right-radius: 6px;
            border-bottom-left-radius: 18px;
        }

        .message-text {
            font-size: 14px;
            line-height: 1.4;
            margin: 0 0 4px 0;
        }

        .message-time {
            font-size: 11px;
            opacity: 0.7;
        }

        .message-sender-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            margin-right: 8px;
            align-self: flex-end;
        }

        /* 🗑️ Delete Message Button */
        .message-delete-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 28px;
            height: 28px;
            background: rgba(220, 38, 38, 0.9);
            color: white;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
            font-size: 12px;
            z-index: 12;
        }

        /* For images - position next to download icon */
        .message-bubble .download-icon {
            z-index: 11;
        }

        /* For images - special positioning for delete button when there's a download icon */
        .message-bubble [style*="relative; display: inline-block"] .message-delete-btn {
            top: 8px;
            right: 45px;
            /* Position next to download icon */
        }

        /* For text messages - position in top right of bubble */
        .message-delete-btn.text-delete {
            top: 8px;
            right: 8px;
            background: rgba(220, 38, 38, 0.8);
            backdrop-filter: blur(4px);
        }

        /* For file messages - position in top right of file container */
        .message-delete-btn.file-delete {
            top: 4px;
            right: 4px;
            width: 24px;
            height: 24px;
            font-size: 10px;
            background: rgba(220, 38, 38, 0.8);
        }

        .message-item.own:hover .message-delete-btn {
            opacity: 1;
            visibility: visible;
        }

        .message-delete-btn:hover {
            background: rgba(185, 28, 28, 1);
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
        }

        .message-delete-btn i {
            pointer-events: none;
        }

        /* Remove the general padding adjustment */
        .message-item.own {
            /* padding-right: 15px; - removed */
        }

        /* 🔗 Message Link Preview Styles */
        .message-link-preview {
            display: flex;
            align-items: center;
            padding: 12px;
            margin: 8px 0;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            backdrop-filter: blur(10px);
        }

        .message-item:not(.own) .message-link-preview {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .message-link-preview:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .message-item:not(.own) .message-link-preview:hover {
            background: #f1f5f9;
        }

        .link-preview-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            color: currentColor;
        }

        .message-item:not(.own) .link-preview-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .link-preview-content {
            flex: 1;
            min-width: 0;
        }

        .link-preview-title {
            font-size: 14px;
            font-weight: 600;
            color: currentColor;
            margin-bottom: 2px;
        }

        .link-preview-url {
            font-size: 12px;
            opacity: 0.8;
            word-break: break-all;
        }

        .link-preview-action {
            margin-left: 8px;
            opacity: 0.6;
            transition: opacity 0.2s ease;
        }

        .message-link-preview:hover .link-preview-action {
            opacity: 1;
        }

        /* ✍️ Modern Message Input */
        .modern-message-input {
            padding: 20px 24px;
            background: white;
            border-top: 1px solid #e5e7eb;
        }

        .input-container {
            display: flex;
            align-items: flex-end;
            gap: 12px;
            background: #f3f4f6;
            border-radius: 24px;
            padding: 8px;
        }

        .attach-btn,
        .emoji-btn {
            width: 36px;
            height: 36px;
            border: none;
            background: transparent;
            color: #6b7280;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .attach-btn:hover,
        .emoji-btn:hover {
            background: #e5e7eb;
            color: #374151;
        }

        .message-input-wrapper {
            flex: 1;
            position: relative;
            display: flex;
            align-items: flex-end;
        }

        .message-input {
            flex: 1;
            border: none;
            background: transparent;
            padding: 8px 12px;
            font-size: 14px;
            resize: none;
            outline: none;
            max-height: 120px;
            line-height: 1.4;
        }

        .send-btn {
            width: 36px;
            height: 36px;
            border: none;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .send-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        /* 💬 Enhanced Message Styling */
        .message-sender-name {
            margin-bottom: 4px;
        }

        .message-sender-name small {
            font-size: 12px;
            font-weight: 600;
            color: #667eea;
        }

        /* 🎭 Modern Modal */
        .modern-modal .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .modern-modal .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px 12px 0 0;
            border: none;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #1A2A44;
            /* Matches sidebar and content text */
            margin-bottom: 8px;
            font-size: 14px;
        }

        .modern-input {
            border: 1px solid #CED4DA;
            /* Lighter border to match theme */
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 14px;
            background-color: #FFFFFF;
            /* White background for input */
            color: #1A2A44;
        }

        .modern-input:focus {
            border-color: #28A745;
            /* Green accent to match buttons */
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
            /* Green shadow */
        }

        .modern-btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.2s ease;
            background-color: #28A745;
            /* Green button color */
            color: #FFFFFF;
            border: none;
        }

        .modern-btn:hover {
            background-color: #218838;
            /* Darker green on hover */
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
        }

        .friends-selection {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            padding: 12px;
            background-color: #F5F7FA;
            /* Matches content area */
        }

        .email-search-wrapper {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .email-search-input {
            flex: 1;
            padding: 10px 12px;
            border: 1px solid #CED4DA;
            /* Consistent border color */
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            background-color: #FFFFFF;
            color: #1A2A44;
        }

        .email-search-input:focus {
            border-color: #11b8aa;
            /* Green accent */
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
        }

        .search-user-btn {
            padding: 10px 16px;
            background-color: #11b8aa;
            /* Green to match theme */
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 14px;
        }

        .search-user-btn:hover {
            background-color: #218838;
            /* Darker green */
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
        }

        .user-result {
            background: #F5F7FA;
            /* Matches content area */
            border: 1px solid #E2E8F0;
            transition: all 0.2s ease;
        }

        .user-result:hover {
            background: #E6EAF1;
            /* Slightly darker for hover */
            border-color: #CBD5E1;
        }

        /* 📱 Responsive Design */
        @media (max-width: 768px) {
            .modern-messenger-container {
                flex-direction: column;
            }

            .modern-sidebar {
                width: 100%;
                height: 50vh;
                max-height: 50vh;
                border-right: none;
                border-bottom: 1px solid #e5e7eb;
            }

            .modern-chat-area {
                height: 50vh;
                flex: 1;
            }

            .chat-sidebar {
                display: none;
                /* Hide sidebar on mobile - could be shown as modal if needed */
            }

            .sidebar-content {
                padding: 12px;
            }

            .media-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .chat-sidebar {
                display: none;
                /* Hide completely on small mobile */
            }
        }

        /* 🎨 Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .conversation-item,
        .friend-item,
        .patient-item,
        .group-item {
            animation: fadeIn 0.3s ease;
        }

        /* 📝 Scrollbar Styling */
        .chat-list-container::-webkit-scrollbar,
        .modern-messages-container::-webkit-scrollbar {
            width: 6px;
        }

        .chat-list-container::-webkit-scrollbar-track,
        .modern-messages-container::-webkit-scrollbar-track {
            background: transparent;
        }

        .chat-list-container::-webkit-scrollbar-thumb,
        .modern-messages-container::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 3px;
        }

        .chat-list-container::-webkit-scrollbar-thumb:hover,
        .modern-messages-container::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        /* 😀 Emoji Picker Styles */
        .emoji-picker {
            display: none;
            position: absolute;
            bottom: 60px;
            right: 60px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            padding: 12px;
            width: 300px;
            height: 200px;
            overflow-y: auto;
            z-index: 1000;
        }

        .emoji-grid {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 4px;
        }

        .emoji-grid span {
            padding: 6px;
            text-align: center;
            cursor: pointer;
            border-radius: 6px;
            font-size: 18px;
            transition: background 0.2s ease;
        }

        .emoji-grid span:hover {
            background: #f3f4f6;
        }

        .emoji-btn.active {
            background: #e5e7eb;
            color: #374151;
        }

        /* 📁 File Upload Styles */
        .upload-progress-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 8px;
        }

        .upload-info {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            font-size: 14px;
            color: #374151;
        }

        .progress-bar {
            width: 100%;
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: width 0.3s ease;
        }

        /* 🖼️ Message Media Styles */
        .message-image img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .message-image img:hover {
            transform: scale(1.05);
        }

        .message-video video {
            max-width: 250px;
            max-height: 200px;
            border-radius: 8px;
        }

        .message-file {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 4px;
        }

        .message-file a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .message-file a:hover {
            text-decoration: underline;
        }

        /* 🖼️ Image Modal */
        .image-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }

        .image-modal-content {
            position: relative;
            max-width: 90%;
            max-height: 90%;
        }

        .image-modal-content img {
            max-width: 100%;
            max-height: 100%;
            border-radius: 8px;
        }

        .image-modal-close {
            position: absolute;
            top: -40px;
            right: 0;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .image-modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* 📋 Right Sidebar Styles - Integrated into layout */
        .chat-sidebar {
            width: 0;
            min-width: 0;
            background: #ffffff;
            border-left: 1px solid #e5e7eb;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 100;
        }

        .chat-sidebar.open {
            width: 350px;
            min-width: 350px;
        }

        /* 📋 Sidebar Header - Standardized height */
        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            /* Consistent with other headers */
            background-color: #053178;
            /* New dark blue color */
            color: white;
            /* Only top-left corner curved to match sidebar */
            min-height: 84px;
            /* Ensure consistent height across all headers */
            box-sizing: border-box;
        }

        .sidebar-title {
            font-size: 16px;
            /* Match chat contact name font size */
            font-weight: 600;
            margin: 0;
        }

        .sidebar-close-btn {
            width: 40px;
            /* Match action button size for consistency */
            height: 40px;
            border: none;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 16px;
            /* Consistent icon size */
        }

        .sidebar-close-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        .sidebar-content {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
            min-height: 0;
            /* Allows flex shrinking */
            display: flex;
            flex-direction: column;
        }

        .sidebar-section {
            margin-bottom: 20px;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 1px solid #e5e7eb;
        }

        .section-header h6 {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .media-count {
            background: #667eea;
            color: white;
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 500;
            min-width: 18px;
            text-align: center;
        }

        /* 👤 Contact Details in Sidebar */
        .contact-details-sidebar {
            text-align: center;
            padding: 12px;
            background: #f8fafc;
            border-radius: 8px;
        }

        .sidebar-contact-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            margin-bottom: 8px;
            object-fit: cover;
        }

        .sidebar-contact-name {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin: 0 0 2px 0;
        }

        .sidebar-contact-status {
            font-size: 12px;
            color: #10b981;
            margin: 0;
        }

        /* 🖼️ Media Grid */
        .media-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .media-item {
            position: relative;
            aspect-ratio: 1;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .media-item:hover {
            transform: scale(1.05);
        }

        .media-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* 📁 Files List */
        .files-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        /* 📁 File Grid Display */
        .shared-files {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .file-grid-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
        }

        .file-grid-item:hover {
            background: #f1f5f9;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .file-grid-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-bottom: 8px;
            font-size: 16px;
        }

        .file-grid-name {
            font-size: 12px;
            font-weight: 500;
            color: #374151;
            line-height: 1.2;
            word-break: break-word;
            max-width: 100%;
        }

        .file-item {
            display: flex;
            align-items: center;
            padding: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            transition: background 0.2s ease;
        }

        .file-item:hover {
            background: #f1f5f9;
        }

        .file-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 12px;
        }

        .file-info {
            flex: 1;
            min-width: 0;
        }

        .file-name {
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .file-date {
            font-size: 12px;
            color: #6b7280;
        }

        .file-actions {
            margin-left: 8px;
        }

        /* 🔗 Links List */
        .links-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .link-item {
            display: flex;
            align-items: center;
            padding: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            transition: background 0.2s ease;
        }

        .link-item:hover {
            background: #f1f5f9;
        }

        .link-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 12px;
        }

        .link-info {
            flex: 1;
            min-width: 0;
        }

        .link-domain {
            font-size: 14px;
            font-weight: 500;
            color: #374151;
        }

        .link-text {
            font-size: 12px;
            color: #6b7280;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin: 2px 0;
        }

        .link-url {
            font-size: 11px;
            color: #9ca3af;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-family: monospace;
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 4px;
            margin: 2px 0;
        }

        .link-date {
            font-size: 12px;
            color: #6b7280;
        }

        .link-actions {
            margin-left: 8px;
        }

        /* 🎯 Active States */
        .chat-action-btn.active {
            background: #e5e7eb;
            color: #374151;
        }

        /* 📱 Mobile Responsive Sidebar - Full screen overlay */
        @media (max-width: 768px) {
            .chat-sidebar {
                position: fixed;
                top: 0;
                right: -100%;
                width: 100vw;
                height: 100vh;
                z-index: 9999;
                background: #ffffff;
                border-left: none;
                border-radius: 0;
                box-shadow: -4px 0 20px rgba(0, 0, 0, 0.15);
            }

            .chat-sidebar.open {
                right: 0;
                width: 100vw;
                min-width: 100vw;
            }

            .sidebar-header {
                border-radius: 0;
                position: sticky;
                top: 0;
                z-index: 10;
            }

            .sidebar-content {
                padding: 16px;
                padding-bottom: 80px;
                /* Extra space for bottom navigation if any */
            }

            .media-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 12px;
            }

            .media-item {
                border-radius: 12px;
            }

            .file-item,
            .link-item {
                padding: 16px;
                border-radius: 12px;
            }

            .section-header h6 {
                font-size: 14px;
            }

            /* Add backdrop blur when sidebar is open */
            .modern-messenger-container::before {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background: rgba(0, 0, 0, 0.5);
                z-index: 9998;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }

            .modern-messenger-container.sidebar-open::before {
                opacity: 1;
                visibility: visible;
            }
        }

        /* 📱 Small Mobile Responsive */
        @media (max-width: 480px) {
            .chat-sidebar {
                width: 100vw;
                min-width: 100vw;
            }

            .chat-sidebar.open {
                width: 100vw;
                min-width: 100vw;
            }

            .sidebar-content {
                padding: 12px;
            }

            .media-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }

            .contact-details-sidebar {
                padding: 16px;
            }

            .sidebar-contact-avatar {
                width: 60px;
                height: 60px;
            }
        }

        /* 📋 Right Sidebar Styles - Integrated into layout */
        .chat-sidebar {
            width: 0;
            min-width: 0;
            background: #ffffff;
            border-left: 1px solid #e5e7eb;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 100;
        }

        .chat-sidebar.open {
            width: 350px;
            min-width: 350px;
        }

        /* Backdrop overlay for mobile */
        .sidebar-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 998;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .sidebar-backdrop.show {
            opacity: 1;
            visibility: visible;
        }

        /* 📱 Mobile Responsive Sidebar - Floating overlay */
        @media (max-width: 1407px) {
            .chat-sidebar {
                position: fixed;
                top: 0;
                right: -100%;
                width: 350px;
                max-width: 85vw;
                height: 100vh;
                z-index: 999;
                background: #ffffff;
                border-left: none;
                border-radius: 0;
                box-shadow: -8px 0 32px rgba(0, 0, 0, 0.2);
                transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .chat-sidebar.open {
                right: 0;
                width: 350px;
                max-width: 85vw;
                min-width: 350px;
            }

            .sidebar-header {
                border-radius: 0;
                position: sticky;
                top: 0;
                z-index: 10;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            }

            .sidebar-content {
                padding: 16px;
                padding-bottom: 80px;
            }

            .media-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 12px;
            }

            .media-item {
                border-radius: 12px;
            }

            .file-item,
            .link-item {
                padding: 16px;
                border-radius: 12px;
            }

            .section-header h6 {
                font-size: 14px;
            }
        }

        /* 📱 Small Mobile Responsive */
        @media (max-width: 768px) {
            .chat-sidebar {
                position: fixed;
                top: 0;
                right: -100%;
                width: 300px;
                max-width: 90vw;
                height: 100vh;
                z-index: 999;
                background: #ffffff;
                border-left: none;
                border-radius: 0;
                box-shadow: -8px 0 32px rgba(0, 0, 0, 0.2);
                transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .chat-sidebar.open {
                right: 0;
                width: 300px;
                max-width: 90vw;
                min-width: 300px;
            }

            .sidebar-content {
                padding: 12px;
            }

            .media-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }

            .contact-details-sidebar {
                padding: 16px;
            }

            .sidebar-contact-avatar {
                width: 60px;
                height: 60px;
            }

            /* Reduce backdrop opacity on small screens */
            .sidebar-backdrop {
                background: rgba(0, 0, 0, 0.3);
                backdrop-filter: blur(2px);
            }
        }

        @media (max-width: 480px) {
            .chat-sidebar {
                position: fixed;
                top: 0;
                right: -100%;
                width: 280px;
                max-width: 95vw;
                height: 100vh;
                z-index: 999;
                background: #ffffff;
                border-left: none;
                border-radius: 0;
                box-shadow: -8px 0 32px rgba(0, 0, 0, 0.2);
                transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .chat-sidebar.open {
                right: 0;
                width: 280px;
                max-width: 95vw;
                min-width: 280px;
            }

            /* Even lighter backdrop on very small screens */
            .sidebar-backdrop {
                background: rgba(0, 0, 0, 0.2);
                backdrop-filter: blur(1px);
            }

            .sidebar-content {
                padding: 8px;
            }

            .media-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 6px;
            }
        }

        /* 🖼️ Shared Images Grid Modal */
        .shared-images-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            animation: fadeIn 0.3s ease;
        }

        .shared-images-modal-content {
            background: #ffffff;
            border-radius: 12px;
            max-width: 90vw;
            max-height: 90vh;
            width: 800px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .shared-images-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .shared-images-header h5 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
        }

        .shared-images-close {
            width: 40px;
            height: 40px;
            border: none;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .shared-images-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
        }

        .shared-images-grid {
            flex: 1;
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 12px;
            overflow-y: auto;
            max-height: 400px;
        }

        .shared-image-item {
            position: relative;
            aspect-ratio: 1;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 3px solid transparent;
        }

        .shared-image-item:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .shared-image-item.active {
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.3);
        }

        .shared-image-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .shared-image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .shared-image-item:hover .shared-image-overlay {
            opacity: 1;
        }

        .shared-image-download,
        .shared-image-fullscreen {
            width: 36px;
            height: 36px;
            border: none;
            background: rgba(255, 255, 255, 0.9);
            color: #374151;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 14px;
        }

        .shared-image-download:hover,
        .shared-image-fullscreen:hover {
            background: white;
            transform: scale(1.1);
        }

        .shared-images-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 24px;
            border-top: 1px solid #e5e7eb;
            background: #f8fafc;
        }

        .shared-image-info {
            display: flex;
            flex-direction: column;
        }

        .shared-image-info span {
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }

        .shared-image-info small {
            color: #6b7280;
            font-size: 12px;
            margin-top: 2px;
        }

        .shared-images-actions {
            display: flex;
            gap: 8px;
        }

        /* 📱 Mobile responsive for shared images modal */
        @media (max-width: 768px) {
            .shared-images-modal-content {
                width: 95vw;
                max-height: 85vh;
            }

            .shared-images-grid {
                grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
                gap: 8px;
                padding: 16px;
                max-height: 300px;
            }

            .shared-images-header,
            .shared-images-footer {
                padding: 16px;
            }

            .shared-images-footer {
                flex-direction: column;
                gap: 12px;
                align-items: stretch;
            }

            .shared-images-actions {
                justify-content: center;
            }
        }

        /* 📁 Shared Files Grid Modal */
        .shared-files-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            animation: fadeIn 0.3s ease;
        }

        .shared-files-modal-content {
            background: #ffffff;
            border-radius: 12px;
            max-width: 90vw;
            max-height: 90vh;
            width: 800px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .shared-files-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .shared-files-header h5 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
        }

        .shared-files-close {
            width: 40px;
            height: 40px;
            border: none;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .shared-files-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
        }

        .shared-files-grid {
            flex: 1;
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 12px;
            overflow-y: auto;
            max-height: 400px;
        }

        .shared-file-item {
            position: relative;
            background: #f8fafc;
            border: 3px solid transparent;
            border-radius: 12px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            min-height: 120px;
        }

        .shared-file-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            background: #f1f5f9;
        }

        .shared-file-item.active {
            border-color: #10b981;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3);
            background: #f0fdf4;
        }

        .shared-file-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-bottom: 12px;
            font-size: 20px;
        }

        .shared-file-name {
            font-size: 13px;
            font-weight: 500;
            color: #374151;
            line-height: 1.3;
            word-break: break-word;
            max-width: 100%;
        }

        .shared-file-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .shared-file-item:hover .shared-file-overlay {
            opacity: 1;
        }

        .shared-file-download,
        .shared-file-open {
            width: 36px;
            height: 36px;
            border: none;
            background: rgba(255, 255, 255, 0.9);
            color: #374151;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 14px;
        }

        .shared-file-download:hover,
        .shared-file-open:hover {
            background: white;
            transform: scale(1.1);
        }

        .shared-files-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 24px;
            border-top: 1px solid #e5e7eb;
            background: #f8fafc;
        }

        .shared-file-info {
            display: flex;
            flex-direction: column;
        }

        .shared-file-info span {
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }

        .shared-file-info small {
            color: #6b7280;
            font-size: 12px;
            margin-top: 2px;
        }

        .shared-files-actions {
            display: flex;
            gap: 8px;
        }

        /* 📱 Mobile responsive for shared files modal */
        @media (max-width: 768px) {
            .shared-files-modal-content {
                width: 95vw;
                max-height: 85vh;
            }

            .shared-files-grid {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
                gap: 8px;
                padding: 16px;
                max-height: 300px;
            }

            .shared-file-item {
                padding: 12px;
                min-height: 100px;
            }

            .shared-file-icon {
                width: 36px;
                height: 36px;
                font-size: 16px;
                margin-bottom: 8px;
            }

            .shared-file-name {
                font-size: 12px;
            }

            .shared-files-header,
            .shared-files-footer {
                padding: 16px;
            }

            .shared-files-footer {
                flex-direction: column;
                gap: 12px;
                align-items: stretch;
            }

            .shared-files-actions {
                justify-content: center;
            }
        }

        /* 🖼️ Image Context Menu Styles */
        .image-context-menu .menu-item:hover {
            background-color: #f8fafc;
        }

        .image-context-menu .menu-item.delete-item:hover {
            background-color: #fef2f2;
            color: #dc2626;
        }

        .image-context-menu .menu-item:last-child {
            border-bottom: none;
        }

        /* Message Menu Icon Styles */
        .message-item:hover .message-menu-icon {
            opacity: 1 !important;
        }

        .message-menu-icon:hover {
            opacity: 1 !important;
            background: rgba(0, 0, 0, 0.9) !important;
        }

        .contact-initials-circle {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
            text-transform: uppercase;
            margin-right: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            flex-shrink: 0;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .messenger-welcome-container {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 80%;
            padding: 10px 0;
        }

        /* Espacement amélioré entre les cartes */
        .feature-cards-container {
            margin-bottom: 40px !important;
        }

        .feature-card {
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }

        .feature-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1) !important;
        }

        /* Meilleurs espacements pour les boutons */
        .btn {
            padding: 8px 20px;
            margin: 0 10px;
        }
    </style>
@endpush
</style>
<template>
  <div class="messenger-app">
    <!-- Messenger Container -->
    <div class="messenger-container">
      <!-- Sidebar -->
      <div class="messenger-sidebar" :class="{ collapsed: sidebarCollapsed }">
        <!-- Sidebar Header -->
        <div class="sidebar-header">
          <div class="user-info">
            <div class="user-avatar">
              <img :src="currentUser.avatar" :alt="currentUser.name" />
              <span
                class="online-indicator"
                v-if="currentUser.is_online"
              ></span>
            </div>
            <div class="user-details" v-if="!sidebarCollapsed">
              <h3>{{ currentUser.name }} {{ currentUser.lastname }}</h3>
              <p class="status">
                {{ currentUser.is_online ? "En ligne" : "Hors ligne" }}
              </p>
            </div>
          </div>
          <div class="header-actions" v-if="!sidebarCollapsed">
            <button
              @click="showNewGroupModal = true"
              class="btn-action"
              title="Nouveau groupe"
            >
              <i class="fas fa-users"></i>
            </button>
            <button
              @click="showInviteModal = true"
              class="btn-action"
              title="Inviter un ami"
            >
              <i class="fas fa-user-plus"></i>
            </button>
            <button
              @click="showSettings = true"
              class="btn-action"
              title="Paramètres"
            >
              <i class="fas fa-cog"></i>
            </button>
          </div>
        </div>

        <!-- Search Bar -->
        <div class="search-bar" v-if="!sidebarCollapsed">
          <div class="search-input-wrapper">
            <i class="fas fa-search"></i>
            <input
              type="text"
              v-model="searchQuery"
              placeholder="Rechercher des conversations..."
              @input="searchConversations"
            />
          </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="nav-tabs" v-if="!sidebarCollapsed">
          <button
            class="nav-tab"
            :class="{ active: activeTab === 'conversations' }"
            @click="activeTab = 'conversations'"
          >
            <i class="fas fa-comment"></i>
            Conversations
          </button>
          <button
            class="nav-tab"
            :class="{ active: activeTab === 'friends' }"
            @click="
              activeTab = 'friends';
              loadFriends();
            "
          >
            <i class="fas fa-users"></i>
            Amis
          </button>
          <button
            class="nav-tab"
            :class="{ active: activeTab === 'patients' }"
            @click="
              activeTab = 'patients';
              loadPatients();
            "
          >
            <i class="fas fa-user-injured"></i>
            Patients
          </button>
          <button
            class="nav-tab"
            :class="{ active: activeTab === 'groups' }"
            @click="
              activeTab = 'groups';
              loadGroups();
            "
          >
            <i class="fas fa-layer-group"></i>
            Groupes
          </button>
        </div>

        <!-- Invitations Badge -->
        <div
          class="invitations-badge"
          v-if="!sidebarCollapsed && pendingInvitations.length > 0"
        >
          <button @click="showInvitations = true" class="invitations-btn">
            <i class="fas fa-envelope"></i>
            {{ pendingInvitations.length }} invitation(s) en attente
          </button>
        </div>

        <!-- Content Area -->
        <div class="sidebar-content">
          <!-- Conversations List -->
          <ConversationsList
            v-show="activeTab === 'conversations'"
            :conversations="filteredConversations"
            :loading="loadingConversations"
            @select-conversation="selectConversation"
            :selected-conversation="selectedConversation"
            :collapsed="sidebarCollapsed"
          />

          <!-- Friends List -->
          <FriendsList
            v-show="activeTab === 'friends'"
            :friends="friends"
            :loading="loadingFriends"
            @start-chat="startDirectChat"
            :collapsed="sidebarCollapsed"
          />

          <!-- Patients List -->
          <PatientsList
            v-show="activeTab === 'patients'"
            :patients="patients"
            :loading="loadingPatients"
            @start-chat="startPatientChat"
            :collapsed="sidebarCollapsed"
          />

          <!-- Groups List -->
          <GroupsList
            v-show="activeTab === 'groups'"
            :groups="groups"
            :loading="loadingGroups"
            @select-group="selectGroup"
            :collapsed="sidebarCollapsed"
          />
        </div>

        <!-- Sidebar Toggle -->
        <button class="sidebar-toggle" @click="toggleSidebar">
          <i
            class="fas"
            :class="sidebarCollapsed ? 'fa-chevron-right' : 'fa-chevron-left'"
          ></i>
        </button>
      </div>

      <!-- Main Chat Area -->
      <div class="main-chat-area">
        <ChatArea
          v-if="selectedConversation"
          :conversation="selectedConversation"
          :current-user="currentUser"
          @send-message="sendMessage"
          @upload-file="uploadFile"
          @typing="handleTyping"
          @stop-typing="handleStopTyping"
          ref="chatArea"
        />

        <!-- Welcome Screen -->
        <div v-else class="welcome-screen">
          <div class="welcome-content">
            <div class="logo">
              <i class="fas fa-comments"></i>
            </div>
            <h2>Bienvenue dans WIC Doctor Messenger</h2>
            <p>Sélectionnez une conversation pour commencer à discuter</p>
            <div class="quick-actions">
              <button @click="showInviteModal = true" class="btn-primary">
                <i class="fas fa-user-plus"></i>
                Inviter un collègue
              </button>
              <button @click="showNewGroupModal = true" class="btn-secondary">
                <i class="fas fa-users"></i>
                Créer un groupe
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <InviteModal
      v-if="showInviteModal"
      @close="showInviteModal = false"
      @invite-sent="handleInviteSent"
    />

    <NewGroupModal
      v-if="showNewGroupModal"
      :friends="friends"
      @close="showNewGroupModal = false"
      @group-created="handleGroupCreated"
    />

    <InvitationsModal
      v-if="showInvitations"
      :invitations="pendingInvitations"
      @close="showInvitations = false"
      @invitation-handled="handleInvitationResponse"
    />

    <MediaLibraryModal
      v-if="showMediaLibrary"
      :conversation="selectedConversation"
      @close="showMediaLibrary = false"
    />

    <!-- Notification Container -->
    <div class="notifications-container">
      <div
        v-for="notification in notifications"
        :key="notification.id"
        class="notification"
        :class="notification.type"
      >
        <i class="fas" :class="getNotificationIcon(notification.type)"></i>
        <span>{{ notification.message }}</span>
        <button @click="removeNotification(notification.id)">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted, onUnmounted } from "vue";
import { useFirebase } from "@/composables/useFirebase";
import { useMessengerAPI } from "@/composables/useMessengerAPI";
import ConversationsList from "./ConversationsList.vue";
import FriendsList from "./FriendsList.vue";
import PatientsList from "./PatientsList.vue";
import GroupsList from "./GroupsList.vue";
import ChatArea from "./ChatArea.vue";
import InviteModal from "./Modals/InviteModal.vue";
import NewGroupModal from "./Modals/NewGroupModal.vue";
import InvitationsModal from "./Modals/InvitationsModal.vue";
import MediaLibraryModal from "./Modals/MediaLibraryModal.vue";

export default {
  name: "MessengerApp",
  components: {
    ConversationsList,
    FriendsList,
    PatientsList,
    GroupsList,
    ChatArea,
    InviteModal,
    NewGroupModal,
    InvitationsModal,
    MediaLibraryModal,
  },
  setup() {
    // Firebase composable
    const {
      initializeFirebase,
      updateUserPresence,
      subscribeToMessages,
      subscribeToTyping,
      sendMessage: firebaseSendMessage,
      markMessageAsRead,
    } = useFirebase();

    // API composable
    const {
      getCurrentUser,
      getConversations,
      getFriends,
      getPatients,
      getGroups,
      getInvitations,
      createDirectConversation,
    } = useMessengerAPI();

    // Reactive state
    const currentUser = ref({});
    const conversations = ref([]);
    const friends = ref([]);
    const patients = ref([]);
    const groups = ref([]);
    const pendingInvitations = ref([]);
    const selectedConversation = ref(null);
    const notifications = ref([]);

    // UI state
    const sidebarCollapsed = ref(false);
    const activeTab = ref("conversations");
    const searchQuery = ref("");
    const showInviteModal = ref(false);
    const showNewGroupModal = ref(false);
    const showInvitations = ref(false);
    const showMediaLibrary = ref(false);
    const showSettings = ref(false);

    // Loading states
    const loadingConversations = ref(false);
    const loadingFriends = ref(false);
    const loadingPatients = ref(false);
    const loadingGroups = ref(false);

    // Computed
    const filteredConversations = computed(() => {
      if (!searchQuery.value) return conversations.value;

      return conversations.value.filter((conv) =>
        conv.name.toLowerCase().includes(searchQuery.value.toLowerCase())
      );
    });

    // Methods
    const toggleSidebar = () => {
      sidebarCollapsed.value = !sidebarCollapsed.value;
    };

    const loadCurrentUser = async () => {
      try {
        const response = await getCurrentUser();
        currentUser.value = response.data;
        await updateUserPresence(currentUser.value.id, true);
      } catch (error) {
        console.error("Error loading current user:", error);
      }
    };

    const loadConversations = async () => {
      loadingConversations.value = true;
      try {
        const response = await getConversations();
        conversations.value = response.data;
      } catch (error) {
        console.error("Error loading conversations:", error);
      } finally {
        loadingConversations.value = false;
      }
    };

    const loadFriends = async () => {
      loadingFriends.value = true;
      try {
        const response = await getFriends();
        friends.value = response.data;
      } catch (error) {
        console.error("Error loading friends:", error);
      } finally {
        loadingFriends.value = false;
      }
    };

    const loadPatients = async () => {
      loadingPatients.value = true;
      try {
        const response = await getPatients();
        patients.value = response.data;
      } catch (error) {
        console.error("Error loading patients:", error);
      } finally {
        loadingPatients.value = false;
      }
    };

    const loadGroups = async () => {
      loadingGroups.value = true;
      try {
        const response = await getGroups();
        groups.value = response.data;
      } catch (error) {
        console.error("Error loading groups:", error);
      } finally {
        loadingGroups.value = false;
      }
    };

    const loadInvitations = async () => {
      try {
        const response = await getInvitations();
        pendingInvitations.value = response.data;
      } catch (error) {
        console.error("Error loading invitations:", error);
      }
    };

    const selectConversation = (conversation) => {
      selectedConversation.value = conversation;
      // Mark messages as read
      markMessageAsRead(
        conversation.firebase_conversation_id,
        currentUser.value.id
      );
    };

    const startDirectChat = async (friend) => {
      try {
        const response = await createDirectConversation(friend.id);
        const conversationData = response.data;

        // Find or add conversation to list
        let conversation = conversations.value.find(
          (conv) =>
            conv.firebase_conversation_id ===
            conversationData.firebase_conversation_id
        );

        if (!conversation) {
          conversation = {
            id: conversationData.conversation_id,
            firebase_conversation_id: conversationData.firebase_conversation_id,
            type: "direct",
            name: `${friend.name} ${friend.lastname}`,
            avatar: friend.avatar,
            other_user: friend,
          };
          conversations.value.unshift(conversation);
        }

        selectConversation(conversation);
        activeTab.value = "conversations";
      } catch (error) {
        console.error("Error starting direct chat:", error);
        showNotification(
          "Erreur lors de la création de la conversation",
          "error"
        );
      }
    };

    const startPatientChat = async (patient) => {
      // Similar to startDirectChat but for patients
      console.log("Starting patient chat:", patient);
    };

    const selectGroup = (group) => {
      // Find conversation for this group
      const conversation = conversations.value.find(
        (conv) => conv.type === "group" && conv.reference_id === group.id
      );
      if (conversation) {
        selectConversation(conversation);
        activeTab.value = "conversations";
      }
    };

    const sendMessage = async (messageData) => {
      try {
        await firebaseSendMessage(
          selectedConversation.value.firebase_conversation_id,
          {
            ...messageData,
            senderId: currentUser.value.id,
            timestamp: Date.now(),
          }
        );
      } catch (error) {
        console.error("Error sending message:", error);
        showNotification("Erreur lors de l'envoi du message", "error");
      }
    };

    const uploadFile = async (file) => {
      // Handle file upload to Firebase Storage
      console.log("Uploading file:", file);
    };

    const handleTyping = (conversationId) => {
      // Handle typing indicator
      console.log("User typing in:", conversationId);
    };

    const handleStopTyping = (conversationId) => {
      // Handle stop typing
      console.log("User stopped typing in:", conversationId);
    };

    const searchConversations = () => {
      // Search is handled by computed property
    };

    const handleInviteSent = () => {
      showNotification("Invitation envoyée avec succès", "success");
      showInviteModal.value = false;
    };

    const handleGroupCreated = (group) => {
      groups.value.unshift(group);
      showNotification("Groupe créé avec succès", "success");
      showNewGroupModal.value = false;
      loadConversations(); // Refresh conversations
    };

    const handleInvitationResponse = () => {
      loadInvitations();
      loadFriends();
    };

    const showNotification = (message, type = "info") => {
      const notification = {
        id: Date.now(),
        message,
        type,
      };
      notifications.value.push(notification);

      setTimeout(() => {
        removeNotification(notification.id);
      }, 5000);
    };

    const removeNotification = (id) => {
      const index = notifications.value.findIndex((n) => n.id === id);
      if (index > -1) {
        notifications.value.splice(index, 1);
      }
    };

    const getNotificationIcon = (type) => {
      switch (type) {
        case "success":
          return "fa-check-circle";
        case "error":
          return "fa-exclamation-circle";
        case "warning":
          return "fa-exclamation-triangle";
        default:
          return "fa-info-circle";
      }
    };

    // Lifecycle
    onMounted(async () => {
      await initializeFirebase();
      await loadCurrentUser();
      await loadConversations();
      await loadInvitations();

      // Set up real-time listeners
      subscribeToMessages((message) => {
        // Handle incoming messages
        console.log("New message:", message);
      });

      subscribeToTyping((typingData) => {
        // Handle typing indicators
        console.log("Typing data:", typingData);
      });
    });

    onUnmounted(() => {
      updateUserPresence(currentUser.value.id, false);
    });

    return {
      // State
      currentUser,
      conversations,
      friends,
      patients,
      groups,
      pendingInvitations,
      selectedConversation,
      notifications,

      // UI State
      sidebarCollapsed,
      activeTab,
      searchQuery,
      showInviteModal,
      showNewGroupModal,
      showInvitations,
      showMediaLibrary,
      showSettings,

      // Loading states
      loadingConversations,
      loadingFriends,
      loadingPatients,
      loadingGroups,

      // Computed
      filteredConversations,

      // Methods
      toggleSidebar,
      loadFriends,
      loadPatients,
      loadGroups,
      selectConversation,
      startDirectChat,
      startPatientChat,
      selectGroup,
      sendMessage,
      uploadFile,
      handleTyping,
      handleStopTyping,
      searchConversations,
      handleInviteSent,
      handleGroupCreated,
      handleInvitationResponse,
      showNotification,
      removeNotification,
      getNotificationIcon,
    };
  },
};
</script>

<style scoped>
.messenger-app {
  height: 100vh;
  display: flex;
  flex-direction: column;
  background: #f0f2f5;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.messenger-container {
  flex: 1;
  display: flex;
  overflow: hidden;
}

/* Sidebar Styles */
.messenger-sidebar {
  width: 320px;
  background: #ffffff;
  border-right: 1px solid #e4e6ea;
  display: flex;
  flex-direction: column;
  transition: width 0.3s ease;
  position: relative;
}

.messenger-sidebar.collapsed {
  width: 60px;
}

.sidebar-header {
  padding: 16px;
  border-bottom: 1px solid #e4e6ea;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
}

.user-avatar {
  position: relative;
  width: 40px;
  height: 40px;
}

.user-avatar img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
}

.online-indicator {
  position: absolute;
  bottom: 2px;
  right: 2px;
  width: 12px;
  height: 12px;
  background: #42b883;
  border: 2px solid #ffffff;
  border-radius: 50%;
}

.user-details h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: #1c1e21;
}

.user-details .status {
  margin: 0;
  font-size: 12px;
  color: #65676b;
}

.header-actions {
  display: flex;
  gap: 8px;
}

.btn-action {
  width: 36px;
  height: 36px;
  border: none;
  background: #f0f2f5;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #65676b;
  transition: background-color 0.2s;
}

.btn-action:hover {
  background: #e4e6ea;
}

/* Search Bar */
.search-bar {
  padding: 8px 16px;
  border-bottom: 1px solid #e4e6ea;
}

.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.search-input-wrapper i {
  position: absolute;
  left: 12px;
  color: #65676b;
  font-size: 14px;
}

.search-input-wrapper input {
  width: 100%;
  padding: 10px 12px 10px 36px;
  border: none;
  background: #f0f2f5;
  border-radius: 20px;
  font-size: 14px;
  outline: none;
}

.search-input-wrapper input:focus {
  background: #ffffff;
  box-shadow: 0 0 0 2px #0084ff;
}

/* Navigation Tabs */
.nav-tabs {
  display: flex;
  flex-direction: column;
  border-bottom: 1px solid #e4e6ea;
}

.nav-tab {
  padding: 12px 16px;
  border: none;
  background: none;
  text-align: left;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 12px;
  color: #65676b;
  font-size: 14px;
  transition: background-color 0.2s;
}

.nav-tab:hover {
  background: #f0f2f5;
}

.nav-tab.active {
  background: #e7f3ff;
  color: #0084ff;
  font-weight: 600;
}

.nav-tab i {
  width: 16px;
  text-align: center;
}

/* Invitations Badge */
.invitations-badge {
  padding: 8px 16px;
  border-bottom: 1px solid #e4e6ea;
}

.invitations-btn {
  width: 100%;
  padding: 8px 12px;
  border: none;
  background: #fff3cd;
  border-radius: 8px;
  color: #856404;
  font-size: 12px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
}

.invitations-btn:hover {
  background: #ffeaa7;
}

/* Sidebar Content */
.sidebar-content {
  flex: 1;
  overflow-y: auto;
}

/* Sidebar Toggle */
.sidebar-toggle {
  position: absolute;
  top: 50%;
  right: -12px;
  transform: translateY(-50%);
  width: 24px;
  height: 24px;
  border: 1px solid #e4e6ea;
  background: #ffffff;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #65676b;
  font-size: 10px;
  z-index: 10;
}

.sidebar-toggle:hover {
  background: #f0f2f5;
}

/* Main Chat Area */
.main-chat-area {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: #ffffff;
}

/* Welcome Screen */
.welcome-screen {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
}

.welcome-content {
  text-align: center;
  max-width: 400px;
}

.welcome-content .logo {
  font-size: 64px;
  color: #0084ff;
  margin-bottom: 24px;
}

.welcome-content h2 {
  font-size: 24px;
  font-weight: 300;
  color: #1c1e21;
  margin-bottom: 12px;
}

.welcome-content p {
  color: #65676b;
  margin-bottom: 32px;
}

.quick-actions {
  display: flex;
  gap: 16px;
  justify-content: center;
}

.btn-primary,
.btn-secondary {
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;
}

.btn-primary {
  background: #0084ff;
  color: #ffffff;
}

.btn-primary:hover {
  background: #0073e6;
}

.btn-secondary {
  background: #f0f2f5;
  color: #1c1e21;
}

.btn-secondary:hover {
  background: #e4e6ea;
}

/* Notifications */
.notifications-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 1000;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.notification {
  min-width: 300px;
  padding: 16px;
  background: #ffffff;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 12px;
  animation: slideIn 0.3s ease;
}

.notification.success {
  border-left: 4px solid #42b883;
}

.notification.error {
  border-left: 4px solid #e74c3c;
}

.notification.warning {
  border-left: 4px solid #f39c12;
}

.notification.info {
  border-left: 4px solid #0084ff;
}

.notification button {
  margin-left: auto;
  border: none;
  background: none;
  color: #65676b;
  cursor: pointer;
  padding: 4px;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* Responsive Design */
@media (max-width: 768px) {
  .messenger-sidebar {
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    z-index: 100;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
  }

  .messenger-sidebar:not(.collapsed) {
    transform: translateX(0);
  }

  .quick-actions {
    flex-direction: column;
  }
}
</style>

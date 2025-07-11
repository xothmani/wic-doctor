class GlobalNotificationManager {
    constructor() {
        console.log('🚀 Initializing GlobalNotificationManager');
        this.userId = document.body.dataset.userId;
        console.log('👤 User ID from body:', this.userId);

        // Check if userId is valid
        if (!this.userId) {
            console.error('❌ User ID is undefined, cannot initialize');
            return;
        }

        this.unreadCount = 0;
        
        // Load previously processed messages from sessionStorage
        const processedMessagesJson = sessionStorage.getItem(`user_${this.userId}_processed_messages`);
        this.processedMessages = processedMessagesJson ? 
            new Set(JSON.parse(processedMessagesJson)) : 
            new Set();
        
        // Load previously notified messages from sessionStorage
        const notifiedMessagesJson = sessionStorage.getItem(`user_${this.userId}_notified_messages`);
        this.notifiedMessages = notifiedMessagesJson ? 
            new Set(JSON.parse(notifiedMessagesJson)) : 
            new Set();
        
        this.initTimestamp = Date.now() - 5000; // 5 second buffer to account for initial load
        this.isInitializing = true;

        this.initialize();
        this.setupConversationTracking();
    }

    // Add method to save processed messages to sessionStorage
    saveProcessedMessages() {
        const messagesArray = Array.from(this.processedMessages);
        sessionStorage.setItem(`user_${this.userId}_processed_messages`, JSON.stringify(messagesArray));
    }

    // Add method to save notified messages to sessionStorage
    saveNotifiedMessages() {
        const notifiedArray = Array.from(this.notifiedMessages);
        sessionStorage.setItem(`user_${this.userId}_notified_messages`, JSON.stringify(notifiedArray));
    }

    initialize() {
        console.log('🔔 Starting global message listener...');
    
        if (!window.firebaseDb) {
            console.error('❌ Firebase not initialized');
            return;
        }
    
        const db = window.firebaseDb;
        console.log('👤 Setting up listener for user:', this.userId);
    
        // Use collectionGroup to find all chats for the user
        db.collectionGroup('chats')
            .where('receiver.id', '==', this.userId)
            .get()
            .then(chatSnapshot => {
                const conversationIds = new Set();
                chatSnapshot.forEach(chatDoc => {
                    const conversationId = chatDoc.ref.parent.parent.id;
                    conversationIds.add(conversationId);
                    console.log('🔍 Detected conversation from chats:', conversationId);
                });
    
                conversationIds.forEach(conversationId => {
                    const chatsRef = db.collection('messages').doc(conversationId).collection('chats');
                    console.log('📡 Setting up listener for path:', `/messages/${conversationId}/chats`);
    
                    this.setupListenerWithRetry(
                        chatsRef,
                        snapshot => {
                            console.log('📨 Received update for conversation:', conversationId);
                            snapshot.docChanges().forEach(change => {
                                if (change.type === 'added' || change.type === 'modified') {
                                    const messageData = change.doc.data();
                                    const messageId = change.doc.id;
    
                                    if (messageData.receiver?.id !== this.userId.toString()) {
                                        console.log('⚠️ Message not for current user:', messageData.receiver?.id);
                                        return;
                                    }
    
                                    if (messageData.status !== 'sent' && messageData.status !== 'delivered') {
                                        console.log('⚠️ Message status not valid:', messageData.status);
                                        return;
                                    }

                                    // Get message timestamp
                                    const messageTime = this.getMessageTimestamp(messageData);
                                    
                                    // Check if this is a truly new message (arrived after initialization)
                                    const isRealTimeMessage = messageTime > this.initTimestamp;
                                    
                                    // Check if this is the first time processing this message
                                    const isFirstTimeProcessing = !this.processedMessages.has(messageId);
    
                                    console.log('📨 Message detected:', {
                                        conversationId,
                                        messageId,
                                        from: messageData.sender?.name,
                                        text: messageData.text?.substring(0, 50),
                                        status: messageData.status,
                                        isRealTime: isRealTimeMessage,
                                        isFirstTime: isFirstTimeProcessing,
                                        isInitializing: this.isInitializing,
                                        messageTime: new Date(messageTime).toISOString(),
                                        initTime: new Date(this.initTimestamp).toISOString()
                                    });
    
                                    // Always add to processed messages
                                    if (isFirstTimeProcessing) {
                                        this.processedMessages.add(messageId);
                                        this.saveProcessedMessages();
                                    }
                                    
                                    // Handle the message
                                    this.handleNewMessage(messageData, messageId, conversationId, isRealTimeMessage && !this.isInitializing);
                                }
                            });
                        },
                        `conversationListener_${conversationId}`
                    );
                });
    
                console.log('✅ All conversation listeners set up');
                
                // Update unread count and then mark initialization as complete
                this.updateUnreadCount().then(() => {
                    // Give a moment for initial messages to be processed
                    setTimeout(() => {
                        this.isInitializing = false;
                        console.log('✅ Initialization complete - ready for real-time notifications');
                    }, 2000);
                });
                
                this.startFallbackPolling();
            }).catch(error => {
                console.error('❌ Error setting up listeners:', error);
            });
    }

    setupConversationTracking() {
        // Track which conversation is currently open
        this.currentConversationId = null;
        
        // Method to detect current conversation from URL
        this.detectCurrentConversation = () => {
            const url = window.location.href;
            // Adjust these patterns based on your URL structure
            const conversationMatch = url.match(/\/conversation\/([^\/\?#]+)/i) || 
                                    url.match(/\/chat\/([^\/\?#]+)/i) ||
                                    url.match(/\/messages\/([^\/\?#]+)/i);
            
            if (conversationMatch) {
                this.currentConversationId = conversationMatch[1];
                console.log('📱 Current conversation detected:', this.currentConversationId);
            } else {
                this.currentConversationId = null;
                console.log('📱 No active conversation detected');
            }
        };
        
        // Detect current conversation on page load
        this.detectCurrentConversation();
        
        // Listen for URL changes (for SPAs)
        window.addEventListener('popstate', () => {
            this.detectCurrentConversation();
        });
        
        // Listen for pushstate/replacestate (for SPAs)
        const originalPushState = history.pushState;
        const originalReplaceState = history.replaceState;
        
        history.pushState = function() {
            originalPushState.apply(history, arguments);
            setTimeout(() => {
                window.globalNotificationManager?.detectCurrentConversation();
            }, 100);
        };
        
        history.replaceState = function() {
            originalReplaceState.apply(history, arguments);
            setTimeout(() => {
                window.globalNotificationManager?.detectCurrentConversation();
            }, 100);
        };
        
        // Store reference for global access
        window.globalNotificationManager = this;
        
        // Method to manually set current conversation (call this from your chat page)
        this.setCurrentConversation = (conversationId) => {
            this.currentConversationId = conversationId;
            console.log('📱 Manually set current conversation:', conversationId);
        };
        
        // Method to clear current conversation
        this.clearCurrentConversation = () => {
            this.currentConversationId = null;
            console.log('📱 Cleared current conversation');
        };
    }

    // Method to check if user is currently viewing a conversation
    isViewingConversation(conversationId) {
        const isViewing = this.currentConversationId === conversationId;
        console.log('👁️ Is viewing conversation?', {
            messageConversation: conversationId,
            currentConversation: this.currentConversationId,
            isViewing: isViewing
        });
        return isViewing;
    }

    getMessageTimestamp(messageData) {
        // Handle different timestamp formats
        if (messageData.time) {
            if (messageData.time.toMillis) {
                return messageData.time.toMillis();
            } else if (messageData.time.seconds) {
                return messageData.time.seconds * 1000;
            } else if (typeof messageData.time === 'number') {
                return messageData.time > 1000000000000 ? messageData.time : messageData.time * 1000;
            }
        }
        
        if (messageData.timestamp) {
            return typeof messageData.timestamp === 'number' ? 
                (messageData.timestamp > 1000000000000 ? messageData.timestamp : messageData.timestamp * 1000) : 
                messageData.timestamp;
        }
        
        // Fallback to current time if no timestamp found
        return Date.now();
    }

    setupListenerWithRetry(ref, onNext, listenerName) {
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
                        setTimeout(attemptListen, 1000 * retryCount);
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

    startFallbackPolling() {
        console.log('🔄 Starting fallback polling...');
        if (window.pollingInterval) {
            clearInterval(window.pollingInterval);
        }

        window.pollingInterval = setInterval(() => {
            if (!this.isInitializing) {
                this.checkForNewMessages().catch(error => {
                    console.error('❌ Fallback polling error:', error);
                });
            }
        }, 5000);
    }

    async checkForNewMessages() {
        const db = window.firebaseDb;
        const conversationsQuery = db.collection('messages')
            .where('participants', 'array-contains', this.userId);

        const conversationsSnapshot = await conversationsQuery.get();
        for (const conversationDoc of conversationsSnapshot.docs) {
            const conversationId = conversationDoc.id;
            const query = db.collection('messages')
                .doc(conversationId)
                .collection('chats')
                .where('receiver.id', '==', this.userId)
                .where('status', 'in', ['sent', 'delivered'])
                .where('read_at', '==', null)
                .orderBy('time', 'desc')
                .limit(1);

            const snapshot = await query.get();
            if (!snapshot.empty) {
                const messageData = snapshot.docs[0].data();
                const messageId = snapshot.docs[0].id;
                if (!this.processedMessages.has(messageId)) {
                    const messageTime = this.getMessageTimestamp(messageData);
                    const isRealTimeMessage = messageTime > this.initTimestamp;
                    
                    console.log('📨 New message detected via polling:', {
                        conversationId,
                        messageId,
                        from: messageData.sender?.name,
                        text: messageData.text?.substring(0, 50),
                        status: messageData.status,
                        isRealTime: isRealTimeMessage
                    });
                    
                    this.processedMessages.add(messageId);
                    this.saveProcessedMessages();
                    this.handleNewMessage(messageData, messageId, conversationId, isRealTimeMessage);
                    this.updateUnreadCount();
                }
            }
        }
    }

    async updateUnreadCount() {
        try {
            const db = window.firebaseDb;
            let totalUnread = 0;
    
            const chatQuery = db.collectionGroup('chats')
                .where('receiver.id', '==', this.userId)
                .where('status', 'in', ['sent', 'delivered'])
                .where('read_at', '==', null);
    
            const chatSnapshot = await chatQuery.get();
            console.log('🔍 Found', chatSnapshot.size, 'unread chats');
    
            const conversationCounts = new Map();
            chatSnapshot.forEach(chatDoc => {
                const conversationId = chatDoc.ref.parent.parent.id;
                conversationCounts.set(conversationId, (conversationCounts.get(conversationId) || 0) + 1);
            });
    
            conversationCounts.forEach((count, conversationId) => {
                console.log(`📌 ${conversationId} has ${count} unread messages`);
                totalUnread += count;
            });
    
            this.unreadCount = totalUnread;
            console.log('📊 Total unread messages for user', this.userId + ':', this.unreadCount);
            this.updateBadge();
        } catch (error) {
            console.error('❌ Error updating unread count:', error);
        }
    }

    updateBadge() {
        const badge = document.getElementById('global-unread-count');
        if (badge) {
            console.log('🔔 Updating badge with count:', this.unreadCount, 'at', new Date().toISOString());
            badge.textContent = this.unreadCount > 0 ? this.unreadCount : '';
            badge.style.display = this.unreadCount > 0 ? 'inline' : 'none';
            badge.offsetHeight; // Force reflow
        } else {
            console.warn('⚠️ Badge element not found, check ID "global-unread-count" in app.blade.php');
        }
    }

    handleNewMessage(messageData, messageId, conversationId, shouldNotify = false) {
        console.log('🔔 Handling message:', {
            messageId: messageId,
            conversationId: conversationId,
            shouldNotify: shouldNotify,
            from: messageData.sender?.name,
            isFromSelf: messageData.sender?.id === this.userId.toString(),
            isRead: !!messageData.read_at,
            isViewingConversation: this.isViewingConversation(conversationId)
        });
    
        // Only show notifications for messages that should notify and are not from self
        if (shouldNotify && 
            messageData.sender?.id !== this.userId.toString() && 
            !messageData.read_at &&
            !this.isViewingConversation(conversationId)) { // Don't notify if viewing the conversation
            
            // Check if we've already notified about this message
            if (!this.notifiedMessages.has(messageId)) {
                console.log('🔔 Showing notification for new message:', messageId);
                this.showNotification(messageData);
                this.notifiedMessages.add(messageId);
                this.saveNotifiedMessages();
            } else {
                console.log('⏭️ Already notified about message:', messageId);
            }
        } else {
            console.log('🔇 Not showing notification:', {
                shouldNotify,
                isFromSelf: messageData.sender?.id === this.userId.toString(),
                isRead: !!messageData.read_at,
                isViewingConversation: this.isViewingConversation(conversationId)
            });
        }
        
        // Always update unread count for valid unread messages
        this.updateUnreadCount();
    }

    showNotification(messageData) {
    console.log('🔔 Showing notification for message from:', messageData.sender?.name);
    
    // Vérifier si nous sommes sur HTTPS ou localhost
    const isLocalhost = window.location.hostname === 'localhost' || 
                        window.location.hostname === '127.0.0.1';
    const isSecure = window.location.protocol === 'https:';
    
    console.log('🔐 Protocol check:', { 
        protocol: window.location.protocol,
        hostname: window.location.hostname,
        isLocalhost: isLocalhost, 
        isSecure: isSecure 
    });
    
    // Play notification sound and flash tab (ceux-ci fonctionnent même sans HTTPS)
    this.playNotificationSound();
    this.flashBrowserTab('New Message');

    // Vérifier que nous pouvons utiliser les notifications
    if (!('Notification' in window)) {
        console.warn('⚠️ Ce navigateur ne prend pas en charge les notifications');
        return;
    }
    
    // Vérifier la sécurité (sauf pour localhost)
    if (!isSecure && !isLocalhost) {
        console.warn('⚠️ Les notifications nécessitent HTTPS sauf sur localhost');
        return;
    }

    // Vérifier/demander les permissions de notification
    if (Notification.permission === 'granted') {
        this.createNotification(messageData);
    } else if (Notification.permission !== 'denied') {
        console.log('🔔 Demande de permission pour les notifications...');
        Notification.requestPermission().then(permission => {
            if (permission === 'granted') {
                this.createNotification(messageData);
            } else {
                console.warn('⚠️ Permission de notification refusée');
            }
        }).catch(error => {
            console.error('❌ Erreur lors de la demande de permission:', error);
        });
    } else {
        console.warn('⚠️ Notifications déjà refusées par l\'utilisateur');
    }
}

// Ajoutez cette nouvelle méthode
createNotification(messageData) {
    try {
        console.log('🔔 Création de la notification...');
        const notificationOptions = {
            body: `${messageData.sender?.name || 'Someone'}: ${messageData.text?.substring(0, 50) || 'Sent you a message'}`,
            icon: '/images/logo.png',
            data: { url: '/messenger' }
        };
        
        const notification = new Notification('New Message', notificationOptions);
        
        notification.onclick = function() {
            console.log('🔔 Notification cliquée, redirection vers messenger');
            window.focus();
            window.location.href = '/messenger';
        };
        
        console.log('✅ Notification créée avec succès');
    } catch (error) {
        console.error('❌ Erreur lors de la création de la notification:', error);
    }
}

    playNotificationSound() {
        console.log('🔊 Notification sound would play here');
        // Uncomment the following lines to enable sound
        // const audio = new Audio('/sounds/notification.mp3');
        // audio.play().catch(e => console.log('Could not play sound:', e));
    }

    flashBrowserTab(title) {
        const originalTitle = document.title;
        let flashCount = 0;
        const maxFlashes = 5;
        const flashInterval = setInterval(() => {
            document.title = flashCount % 2 === 0 ? title : originalTitle;
            flashCount++;
            if (flashCount >= maxFlashes * 2) {
                clearInterval(flashInterval);
                document.title = originalTitle;
            }
        }, 1000);
    }
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', () => {
    // Function to check if Firebase is initialized
    const waitForFirebase = () => {
        if (window.firebaseDb) {
            console.log('🔥 Firebase is ready, initializing GlobalNotificationManager');
            new GlobalNotificationManager();
        } else {
            console.log('⏳ Waiting for Firebase to initialize...');
            setTimeout(waitForFirebase, 100); // Poll every 100ms
        }
    };
    waitForFirebase();
});
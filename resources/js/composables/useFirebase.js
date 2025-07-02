// Firebase v8 Compatible Composable
// This version works with Firebase v8 SDK loaded via script tags

function useFirebase() {
    // State management (using simple objects instead of Vue refs for broader compatibility)
    const state = {
        isInitialized: false,
        activeListeners: new Map(),
        database: null,
        firestore: null,
        storage: null
    };

    // Initialize Firebase services
    const initializeFirebase = () => {
        try {
            if (!window.firebase) {
                throw new Error('Firebase SDK not loaded');
            }

            state.database = window.firebaseDb || firebase.database();
            state.firestore = window.firebaseFirestore || firebase.firestore();
            state.storage = window.firebaseStorage || firebase.storage();
            state.isInitialized = true;
            
            console.log('✅ Firebase services initialized');
            return true;
        } catch (error) {
            console.error('❌ Error initializing Firebase services:', error);
            throw error;
        }
    };

    // User presence management
    const updateUserPresence = async (userId, isOnline) => {
        if (!state.database) throw new Error('Firebase not initialized');
        
        const presenceRef = state.database.ref(`userPresence/${userId}`);
        await presenceRef.set({
            online: isOnline,
            lastSeen: firebase.database.ServerValue.TIMESTAMP
        });
    };

    // Subscribe to user presence
    const subscribeToUserPresence = (userId, callback) => {
        if (!state.database) return null;
        
        const presenceRef = state.database.ref(`userPresence/${userId}`);
        const listener = presenceRef.on('value', callback);
        
        const unsubscribe = () => presenceRef.off('value', listener);
        state.activeListeners.set(`presence_${userId}`, unsubscribe);
        return unsubscribe;
    };

    // Messages functionality
    const sendMessage = async (conversationId, chatId, messageData) => {
        if (!state.database) throw new Error('Firebase not initialized');
        
        const messagesRef = state.database.ref(`messages/${conversationId}/chats/${chatId}`);
        const newMessageRef = messagesRef.push();
        
        const message = {
            ...messageData,
            timestamp: firebase.database.ServerValue.TIMESTAMP,
            id: newMessageRef.key
        };
        
        await newMessageRef.set(message);
        
        // Update conversation last message
        const conversationRef = state.database.ref(`conversations/${conversationId}`);
        await conversationRef.update({
            lastMessage: messageData.content || messageData.type,
            lastMessageTime: firebase.database.ServerValue.TIMESTAMP,
            updatedAt: firebase.database.ServerValue.TIMESTAMP
        });
        
        return newMessageRef.key;
    };

    // Subscribe to messages
    const subscribeToMessages = (conversationId, chatId, callback, limit = 50) => {
        if (!state.database) return null;
        
        const messagesRef = state.database.ref(`messages/${conversationId}/chats/${chatId}`);
        const messagesQuery = messagesRef.orderByChild('timestamp').limitToLast(limit);
        
        const listener = messagesQuery.on('value', (snapshot) => {
            const messages = [];
            snapshot.forEach((childSnapshot) => {
                messages.push({
                    id: childSnapshot.key,
                    ...childSnapshot.val()
                });
            });
            callback(messages);
        });
        
        const unsubscribe = () => messagesQuery.off('value', listener);
        state.activeListeners.set(`messages_${conversationId}_${chatId}`, unsubscribe);
        return unsubscribe;
    };

    // Load more messages (pagination)
    const loadMoreMessages = async (conversationId, chatId, beforeTimestamp, limit = 20) => {
        if (!state.database) throw new Error('Firebase not initialized');
        
        const messagesRef = state.database.ref(`messages/${conversationId}/chats/${chatId}`);
        const messagesQuery = messagesRef.orderByChild('timestamp').limitToLast(limit);
        
        return new Promise((resolve) => {
            messagesQuery.once('value', (snapshot) => {
                const messages = [];
                snapshot.forEach((childSnapshot) => {
                    const message = {
                        id: childSnapshot.key,
                        ...childSnapshot.val()
                    };
                    if (message.timestamp < beforeTimestamp) {
                        messages.push(message);
                    }
                });
                resolve(messages.reverse());
            });
        });
    };

    // Mark message as read
    const markMessageAsRead = async (conversationId, chatId, messageId, userId) => {
        if (!state.database) throw new Error('Firebase not initialized');
        
        const readRef = state.database.ref(`messages/${conversationId}/chats/${chatId}/${messageId}/readBy/${userId}`);
        await readRef.set(firebase.database.ServerValue.TIMESTAMP);
    };

    // Typing indicators
    const setTyping = async (conversationId, userId, isTyping) => {
        if (!state.database) throw new Error('Firebase not initialized');
        
        const typingRef = state.database.ref(`typing/${conversationId}/${userId}`);
        
        if (isTyping) {
            await typingRef.set(firebase.database.ServerValue.TIMESTAMP);
        } else {
            await typingRef.remove();
        }
    };

    // Subscribe to typing indicators
    const subscribeToTyping = (conversationId, currentUserId, callback) => {
        if (!state.database) return null;
        
        const typingRef = state.database.ref(`typing/${conversationId}`);
        
        const listener = typingRef.on('value', (snapshot) => {
            const typingUsers = [];
            if (snapshot.exists()) {
                snapshot.forEach((childSnapshot) => {
                    const userId = childSnapshot.key;
                    const timestamp = childSnapshot.val();
                    
                    if (userId !== currentUserId.toString()) {
                        const now = Date.now();
                        const typingTime = timestamp;
                        
                        if (now - typingTime < 3000) {
                            typingUsers.push(userId);
                        }
                    }
                });
            }
            callback(typingUsers);
        });
        
        const unsubscribe = () => typingRef.off('value', listener);
        state.activeListeners.set(`typing_${conversationId}`, unsubscribe);
        return unsubscribe;
    };

    // File upload functionality
    const uploadFile = async (file, path) => {
        if (!state.storage) throw new Error('Firebase not initialized');
        
        const fileRef = state.storage.ref(path);
        const snapshot = await fileRef.put(file);
        const downloadURL = await snapshot.ref.getDownloadURL();
        
        return {
            url: downloadURL,
            path: snapshot.ref.fullPath,
            size: snapshot.metadata.size,
            contentType: snapshot.metadata.contentType
        };
    };

    // Upload image
    const uploadImage = async (file, conversationId) => {
        const timestamp = Date.now();
        const filename = `${timestamp}_${file.name}`;
        const imagePath = `conversations/${conversationId}/images/${filename}`;
        
        return uploadFile(file, imagePath);
    };

    // Upload document
    const uploadDocument = async (file, conversationId) => {
        const timestamp = Date.now();
        const filename = `${timestamp}_${file.name}`;
        const filePath = `conversations/${conversationId}/files/${filename}`;
        
        return uploadFile(file, filePath);
    };

    // Search messages
    const searchMessages = async (conversationId, chatId, searchTerm) => {
        if (!state.database) throw new Error('Firebase not initialized');
        
        const messagesRef = state.database.ref(`messages/${conversationId}/chats/${chatId}`);
        
        return new Promise((resolve) => {
            messagesRef.once('value', (snapshot) => {
                const results = [];
                if (snapshot.exists()) {
                    snapshot.forEach((childSnapshot) => {
                        const message = {
                            id: childSnapshot.key,
                            ...childSnapshot.val()
                        };
                        
                        if (message.content && 
                            message.content.toLowerCase().includes(searchTerm.toLowerCase())) {
                            results.push(message);
                        }
                    });
                }
                resolve(results.reverse());
            });
        });
    };

    // Cleanup function
    const cleanup = () => {
        state.activeListeners.forEach((unsubscribe, key) => {
            if (typeof unsubscribe === 'function') {
                unsubscribe();
            }
        });
        state.activeListeners.clear();
    };

    // Unsubscribe from specific listener
    const unsubscribe = (listenerKey) => {
        const unsubscribeFunc = state.activeListeners.get(listenerKey);
        if (unsubscribeFunc && typeof unsubscribeFunc === 'function') {
            unsubscribeFunc();
            state.activeListeners.delete(listenerKey);
        }
    };

    return {
        // Initialization
        initializeFirebase,
        isInitialized: () => state.isInitialized,
        
        // User presence
        updateUserPresence,
        subscribeToUserPresence,
        
        // Messages
        sendMessage,
        subscribeToMessages,
        loadMoreMessages,
        markMessageAsRead,
        searchMessages,
        
        // Typing indicators
        setTyping,
        subscribeToTyping,
        
        // File uploads
        uploadImage,
        uploadDocument,
        
        // Cleanup
        cleanup,
        unsubscribe
    };
}

// Make it available globally
window.useFirebase = useFirebase;
import { ref, reactive } from 'vue'
import { initializeApp } from 'firebase/app'
import { 
  getDatabase, 
  ref as dbRef, 
  push, 
  set, 
  onValue, 
  off,
  serverTimestamp,
  query,
  orderByChild,
  limitToLast,
  update,
  remove
} from 'firebase/database'
import { 
  getStorage, 
  ref as storageRef, 
  uploadBytes, 
  getDownloadURL,
  deleteObject
} from 'firebase/storage'

// Firebase configuration
const firebaseConfig = {
  apiKey: "AIzaSyCONylt3t8MDw_02k5H9ceXTEmdtxmQtu8",
  authDomain: "wic-doctor-b83e0.firebaseapp.com",
  databaseURL: "https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app",
  projectId: "wic-doctor-b83e0",
  storageBucket: "wic-doctor-b83e0.firebasestorage.app",
  messagingSenderId: "895957208558",
  appId: "1:895957208558:web:322c25347af966f5f512ff",
  measurementId: "G-J2RKG7ZXE5"
}

export function useFirebase() {
  let app = null
  let database = null
  let storage = null
  
  const isInitialized = ref(false)
  const activeListeners = reactive(new Map())

  // Initialize Firebase
  const initializeFirebase = async () => {
    try {
      app = initializeApp(firebaseConfig)
      database = getDatabase(app)
      storage = getStorage(app)
      isInitialized.value = true
      console.log('Firebase initialized successfully')
    } catch (error) {
      console.error('Error initializing Firebase:', error)
      throw error
    }
  }

  // User presence management
  const updateUserPresence = async (userId, isOnline) => {
    if (!database) throw new Error('Firebase not initialized')
    
    const presenceRef = dbRef(database, `userPresence/${userId}`)
    await set(presenceRef, {
      online: isOnline,
      lastSeen: serverTimestamp()
    })
  }

  // Subscribe to user presence
  const subscribeToUserPresence = (userId, callback) => {
    if (!database) return null
    
    const presenceRef = dbRef(database, `userPresence/${userId}`)
    const unsubscribe = onValue(presenceRef, callback)
    
    activeListeners.set(`presence_${userId}`, unsubscribe)
    return unsubscribe
  }

  // Messages functionality
  const sendMessage = async (conversationId, chatId, messageData) => {
    if (!database) throw new Error('Firebase not initialized')
    
    // Use the correct path structure
    const messagesRef = dbRef(database, `messages/${conversationId}/chats/${chatId}`)
    const newMessageRef = push(messagesRef)
    
    const message = {
      ...messageData,
      timestamp: serverTimestamp(),
      id: newMessageRef.key
    }
    
    await set(newMessageRef, message)
    
    // Update conversation last message
    const conversationRef = dbRef(database, `conversations/${conversationId}`)
    await update(conversationRef, {
      lastMessage: messageData.content || messageData.type,
      lastMessageTime: serverTimestamp(),
      updatedAt: serverTimestamp()
    })
    
    return newMessageRef.key
  }

  // Subscribe to messages
  const subscribeToMessages = (conversationId, chatId, callback, limit = 50) => {
    if (!database) return null
    
    // Use the correct path structure: messages/{conversationId}/chats/{chatId}
    const messagesRef = dbRef(database, `messages/${conversationId}/chats/${chatId}`)
    const messagesQuery = query(
      messagesRef,
      orderByChild('timestamp'),
      limitToLast(limit)
    )
    
    const unsubscribe = onValue(messagesQuery, (snapshot) => {
      const messages = []
      snapshot.forEach((childSnapshot) => {
        messages.push({
          id: childSnapshot.key,
          ...childSnapshot.val()
        })
      })
      callback(messages)
    })
    
    activeListeners.set(`messages_${conversationId}_${chatId}`, unsubscribe)
    return unsubscribe
  }

  // Load more messages (pagination)
  const loadMoreMessages = async (conversationId, chatId, beforeTimestamp, limit = 20) => {
    if (!database) throw new Error('Firebase not initialized')
    
    const messagesRef = dbRef(database, `messages/${conversationId}/chats/${chatId}`)
    const messagesQuery = query(
      messagesRef,
      orderByChild('timestamp'),
      limitToLast(limit)
    )
    
    return new Promise((resolve) => {
      onValue(messagesQuery, (snapshot) => {
        const messages = []
        snapshot.forEach((childSnapshot) => {
          const message = {
            id: childSnapshot.key,
            ...childSnapshot.val()
          }
          if (message.timestamp < beforeTimestamp) {
            messages.push(message)
          }
        })
        resolve(messages.reverse())
      }, { onlyOnce: true })
    })
  }

  // Mark message as read
  const markMessageAsRead = async (conversationId, chatId, messageId, userId) => {
    if (!database) throw new Error('Firebase not initialized')
    
    const readRef = dbRef(database, `messages/${conversationId}/chats/${chatId}/${messageId}/readBy/${userId}`)
    await set(readRef, serverTimestamp())
  }

  // Mark all messages as read
  const markAllMessagesAsRead = async (conversationId, userId) => {
    if (!database) throw new Error('Firebase not initialized')
    
    const messagesRef = dbRef(database, `messages/${conversationId}`)
    
    return new Promise((resolve) => {
      onValue(messagesRef, async (snapshot) => {
        const updates = {}
        snapshot.forEach((childSnapshot) => {
          const messageId = childSnapshot.key
          updates[`${messageId}/readBy/${userId}`] = serverTimestamp()
        })
        
        if (Object.keys(updates).length > 0) {
          await update(messagesRef, updates)
        }
        resolve()
      }, { onlyOnce: true })
    })
  }

  // Typing indicators
  const setTyping = async (conversationId, userId, isTyping) => {
    if (!database) throw new Error('Firebase not initialized')
    
    const typingRef = dbRef(database, `typing/${conversationId}/${userId}`)
    
    if (isTyping) {
      await set(typingRef, serverTimestamp())
    } else {
      await remove(typingRef)
    }
  }

  // Subscribe to typing indicators
  const subscribeToTyping = (conversationId, currentUserId, callback) => {
    if (!database) return null
    
    const typingRef = dbRef(database, `typing/${conversationId}`)
    
    const unsubscribe = onValue(typingRef, (snapshot) => {
      const typingUsers = []
      if (snapshot.exists()) {
        snapshot.forEach((childSnapshot) => {
          const userId = childSnapshot.key
          const timestamp = childSnapshot.val()
          
          // Only include other users and recent typing
          if (userId !== currentUserId.toString()) {
            const now = Date.now()
            const typingTime = new Date(timestamp).getTime()
            
            // Consider typing if less than 3 seconds old
            if (now - typingTime < 3000) {
              typingUsers.push(userId)
            }
          }
        })
      }
      callback(typingUsers)
    })
    
    activeListeners.set(`typing_${conversationId}`, unsubscribe)
    return unsubscribe
  }

  // Conversations functionality
  const createConversation = async (conversationId, conversationData) => {
    if (!database) throw new Error('Firebase not initialized')
    
    const conversationRef = dbRef(database, `conversations/${conversationId}`)
    await set(conversationRef, {
      ...conversationData,
      createdAt: serverTimestamp(),
      updatedAt: serverTimestamp()
    })
  }

  // Subscribe to conversation updates
  const subscribeToConversation = (conversationId, callback) => {
    if (!database) return null
    
    const conversationRef = dbRef(database, `conversations/${conversationId}`)
    const unsubscribe = onValue(conversationRef, callback)
    
    activeListeners.set(`conversation_${conversationId}`, unsubscribe)
    return unsubscribe
  }

  // File upload functionality
  const uploadFile = async (file, path) => {
    if (!storage) throw new Error('Firebase not initialized')
    
    const fileRef = storageRef(storage, path)
    const snapshot = await uploadBytes(fileRef, file)
    const downloadURL = await getDownloadURL(snapshot.ref)
    
    return {
      url: downloadURL,
      path: snapshot.ref.fullPath,
      size: snapshot.metadata.size,
      contentType: snapshot.metadata.contentType
    }
  }

  // Upload image with compression
  const uploadImage = async (file, conversationId) => {
    if (!storage) throw new Error('Firebase not initialized')
    
    const timestamp = Date.now()
    const filename = `${timestamp}_${file.name}`
    const imagePath = `conversations/${conversationId}/images/${filename}`
    
    return uploadFile(file, imagePath)
  }

  // Upload general file
  const uploadDocument = async (file, conversationId) => {
    if (!storage) throw new Error('Firebase not initialized')
    
    const timestamp = Date.now()
    const filename = `${timestamp}_${file.name}`
    const filePath = `conversations/${conversationId}/files/${filename}`
    
    return uploadFile(file, filePath)
  }

  // Delete file
  const deleteFile = async (filePath) => {
    if (!storage) throw new Error('Firebase not initialized')
    
    const fileRef = storageRef(storage, filePath)
    await deleteObject(fileRef)
  }

  // Get shared media for conversation
  const getSharedMedia = async (conversationId, type = 'images') => {
    if (!storage) throw new Error('Firebase not initialized')
    
    // This would typically use Firebase Storage list() function
    // For now, we'll track shared media in the database
    const mediaRef = dbRef(database, `sharedMedia/${conversationId}/${type}`)
    
    return new Promise((resolve) => {
      onValue(mediaRef, (snapshot) => {
        const media = []
        if (snapshot.exists()) {
          snapshot.forEach((childSnapshot) => {
            media.push({
              id: childSnapshot.key,
              ...childSnapshot.val()
            })
          })
        }
        resolve(media.reverse()) // Most recent first
      }, { onlyOnce: true })
    })
  }

  // Add media to shared media collection
  const addToSharedMedia = async (conversationId, mediaData) => {
    if (!database) throw new Error('Firebase not initialized')
    
    const mediaType = mediaData.type.startsWith('image/') ? 'images' : 'files'
    const mediaRef = dbRef(database, `sharedMedia/${conversationId}/${mediaType}`)
    const newMediaRef = push(mediaRef)
    
    await set(newMediaRef, {
      ...mediaData,
      timestamp: serverTimestamp()
    })
  }

  // Message reactions
  const addReaction = async (conversationId, messageId, userId, emoji) => {
    if (!database) throw new Error('Firebase not initialized')
    
    const reactionRef = dbRef(database, `messages/${conversationId}/${messageId}/reactions/${userId}`)
    await set(reactionRef, emoji)
  }

  const removeReaction = async (conversationId, messageId, userId) => {
    if (!database) throw new Error('Firebase not initialized')
    
    const reactionRef = dbRef(database, `messages/${conversationId}/${messageId}/reactions/${userId}`)
    await remove(reactionRef)
  }

  // Search messages
  const searchMessages = async (conversationId, chatId, searchTerm) => {
    if (!database) throw new Error('Firebase not initialized')
    
    const messagesRef = dbRef(database, `messages/${conversationId}/chats/${chatId}`)
    
    return new Promise((resolve) => {
      onValue(messagesRef, (snapshot) => {
        const results = []
        if (snapshot.exists()) {
          snapshot.forEach((childSnapshot) => {
            const message = {
              id: childSnapshot.key,
              ...childSnapshot.val()
            }
            
            if (message.content && 
                message.content.toLowerCase().includes(searchTerm.toLowerCase())) {
              results.push(message)
            }
          })
        }
        resolve(results.reverse())
      }, { onlyOnce: true })
    })
  }
  const getConversationChats = async (conversationId) => {
    if (!database) throw new Error('Firebase not initialized')
    
    const chatsRef = dbRef(database, `messages/${conversationId}/chats`)
    
    return new Promise((resolve) => {
      onValue(chatsRef, (snapshot) => {
        const chats = []
        if (snapshot.exists()) {
          snapshot.forEach((childSnapshot) => {
            chats.push({
              chatId: childSnapshot.key,
              // Get the last message or message count
              messageCount: Object.keys(childSnapshot.val()).length
            })
          })
        }
        resolve(chats)
      }, { onlyOnce: true })
    })
  }
  // Cleanup function
  const cleanup = () => {
    activeListeners.forEach((unsubscribe, key) => {
      if (typeof unsubscribe === 'function') {
        unsubscribe()
      }
    })
    activeListeners.clear()
  }

  // Unsubscribe from specific listener
  const unsubscribe = (listenerKey) => {
    const unsubscribeFunc = activeListeners.get(listenerKey)
    if (unsubscribeFunc && typeof unsubscribeFunc === 'function') {
      unsubscribeFunc()
      activeListeners.delete(listenerKey)
    }
  }

  return {
    // Initialization
    initializeFirebase,
    isInitialized,
    
    // User presence
    updateUserPresence,
    subscribeToUserPresence,
    
    // Messages
    sendMessage,
    subscribeToMessages,
    loadMoreMessages,
    markMessageAsRead,
    markAllMessagesAsRead,
    searchMessages,
    
    // Typing indicators
    setTyping,
    subscribeToTyping,
    
    // Conversations
    createConversation,
    subscribeToConversation,
    
    // File uploads
    uploadImage,
    uploadDocument,
    deleteFile,
    getSharedMedia,
    addToSharedMedia,
    
    // Reactions
    addReaction,
    removeReaction,
    
    // Cleanup
    cleanup,
    unsubscribe
  }
} 
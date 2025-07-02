import axios from 'axios'

// Set up axios defaults
axios.defaults.baseURL = '/api'
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.common['Content-Type'] = 'application/json'

// Add token to requests if available
const token = localStorage.getItem('api_token') || document.querySelector('meta[name="api-token"]')?.getAttribute('content')
if (token) {
  axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
}

export function useMessengerAPI() {
  
  // Error handler
  const handleError = (error) => {
    console.error('API Error:', error)
    
    if (error.response) {
      // Server responded with error status
      const message = error.response.data?.message || 'Une erreur est survenue'
      throw new Error(message)
    } else if (error.request) {
      // Network error
      throw new Error('Erreur de connexion au serveur')
    } else {
      // Other error
      throw new Error('Une erreur inattendue est survenue')
    }
  }

  // Authentication & User Management
  const getCurrentUser = async () => {
    try {
      const response = await axios.get('/user/profile')
      return response.data
    } catch (error) {
      handleError(error)
    }
  }

  const updateOnlineStatus = async (isOnline) => {
    try {
      const response = await axios.put('/user/online-status', { is_online: isOnline })
      return response.data
    } catch (error) {
      handleError(error)
    }
  }

  // Friend/Connection System
  const sendInvitation = async (receiverId, message = null) => {
    try {
      const response = await axios.post('/invitations/send', {
        receiver_id: receiverId,
        message
      })
      return response.data
    } catch (error) {
      handleError(error)
    }
  }

  const getInvitations = async () => {
    try {
      const response = await axios.get('/invitations/received')
      return response.data
    } catch (error) {
      handleError(error)
    }
  }

  const acceptInvitation = async (invitationId) => {
    try {
      const response = await axios.put(`/invitations/${invitationId}/accept`)
      return response.data
    } catch (error) {
      handleError(error)
    }
  }

  const declineInvitation = async (invitationId) => {
    try {
      const response = await axios.put(`/invitations/${invitationId}/decline`)
      return response.data
    } catch (error) {
      handleError(error)
    }
  }

  const getFriends = async () => {
    try {
      const response = await axios.get('/friends')
      return response.data
    } catch (error) {
      handleError(error)
    }
  }

  const removeFriend = async (friendId) => {
    try {
      const response = await axios.delete(`/friends/${friendId}`)
      return response.data
    } catch (error) {
      handleError(error)
    }
  }

  // Patient Access
  const getPatients = async () => {
    try {
      const response = await axios.get('/patients')
      return response.data
    } catch (error) {
      handleError(error)
    }
  }

  // Group Management
  const createGroup = async (groupData) => {
    try {
      const response = await axios.post('/groups', groupData)
      return response.data
    } catch (error) {
      handleError(error)
    }
  }

  const getGroups = async () => {
    try {
      const response = await axios.get('/groups')
      return response.data
    } catch (error) {
      handleError(error)
    }
  }

  const addGroupMember = async (groupId, userId) => {
    try {
      const response = await axios.post(`/groups/${groupId}/members`, {
        user_id: userId
      })
      return response.data
    } catch (error) {
      handleError(error)
    }
  }

  const removeGroupMember = async (groupId, userId) => {
    try {
      const response = await axios.delete(`/groups/${groupId}/members/${userId}`)
      return response.data
    } catch (error) {
      handleError(error)
    }
  }

  // Conversations
  const getConversations = async () => {
    try {
      const response = await axios.get('/conversations')
      return response.data
    } catch (error) {
      handleError(error)
    }
  }

  const createDirectConversation = async (userId) => {
    try {
      const response = await axios.post('/conversations/direct', {
        user_id: userId
      })
      return response.data
    } catch (error) {
      handleError(error)
    }
  }

  // Search
  const searchDoctors = async (query) => {
    try {
      const response = await axios.get('/search/doctors', {
        params: { query }
      })
      return response.data
    } catch (error) {
      handleError(error)
    }
  }

  // Media Library (if needed for backend tracking)
  const getSharedMedia = async (conversationId) => {
    try {
      const response = await axios.get(`/media/conversations/${conversationId}`)
      return response.data
    } catch (error) {
      handleError(error)
    }
  }

  const getAllSharedMedia = async () => {
    try {
      const response = await axios.get('/media/shared')
      return response.data
    } catch (error) {
      handleError(error)
    }
  }

  return {
    // Authentication & User Management
    getCurrentUser,
    updateOnlineStatus,

    // Friend/Connection System
    sendInvitation,
    getInvitations,
    acceptInvitation,
    declineInvitation,
    getFriends,
    removeFriend,

    // Patient Access
    getPatients,

    // Group Management
    createGroup,
    getGroups,
    addGroupMember,
    removeGroupMember,

    // Conversations
    getConversations,
    createDirectConversation,

    // Search
    searchDoctors,

    // Media
    getSharedMedia,
    getAllSharedMedia
  }
} 
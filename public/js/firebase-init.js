// Simplified Firebase Initialization
// This file provides additional Firebase utilities and ensures proper initialization

// Wait for global Firebase to be available
function waitForFirebase(callback, maxRetries = 10) {
    let retries = 0;
    
    function check() {
        if (window.firebase && window.firebase.apps && window.firebase.apps.length > 0) {
            callback();
        } else if (retries < maxRetries) {
            retries++;
            setTimeout(check, 100);
        } else {
            console.error('❌ Firebase not available after waiting');
        }
    }
    
    check();
}

// Enhanced initialization with all services
function initializeFirebaseServices() {
    if (!window.firebase || window.firebase.apps.length === 0) {
        console.error('❌ Firebase core not initialized');
        return false;
    }

    try {
        console.log('🔧 Initializing Firebase services...');

        // Initialize Realtime Database
        if (typeof firebase.database === 'function') {
            window.firebaseRealtime = firebase.database();
            console.log('✅ Realtime Database initialized');
        }

        // Initialize Firestore
        if (typeof firebase.firestore === 'function') {
            window.firebaseFirestore = firebase.firestore();
            console.log('✅ Firestore initialized');
        }

        // Initialize Storage
        if (typeof firebase.storage === 'function') {
            window.firebaseStorage = firebase.storage();
            console.log('✅ Storage initialized');
        }

        // Initialize Auth
        if (typeof firebase.auth === 'function') {
            window.firebaseAuth = firebase.auth();
            console.log('✅ Auth initialized');
        }

        // Initialize Messaging (with error handling)
        if (typeof firebase.messaging === 'function') {
            try {
                // Check if messaging is supported
                if ('serviceWorker' in navigator && 'Notification' in window) {
                    window.firebaseMessaging = firebase.messaging();
                    console.log('✅ Messaging initialized');
                } else {
                    console.warn('⚠️ Messaging not supported in this browser');
                }
            } catch (messagingError) {
                console.warn('⚠️ Messaging initialization failed:', messagingError.message);
                // Continue without messaging
            }
        }

        console.log('🚀 All Firebase services initialized successfully');
        return true;

    } catch (error) {
        console.error('❌ Error initializing Firebase services:', error);
        return false;
    }
}

// Setup Firebase for current user context
function setupFirebaseForUser(user) {
    if (!user || !user.id) {
        console.warn('⚠️ No user provided for Firebase setup');
        return;
    }

    console.log('👤 Setting up Firebase for user:', user.name || user.id);

    // Set up user presence if realtime database is available
    if (window.firebaseRealtime) {
        const userPresenceRef = window.firebaseRealtime.ref(`userPresence/${user.id}`);
        
        // Set user as online
        userPresenceRef.set({
            online: true,
            lastSeen: firebase.database.ServerValue.TIMESTAMP,
            name: user.name || 'User'
        });

        // Set user as offline when they disconnect
        userPresenceRef.onDisconnect().set({
            online: false,
            lastSeen: firebase.database.ServerValue.TIMESTAMP,
            name: user.name || 'User'
        });
    }

    // Store user globally for easy access
    window.currentFirebaseUser = user;
}

// Utility function to get Firebase service
function getFirebaseService(serviceName) {
    const services = {
        'database': window.firebaseRealtime,
        'firestore': window.firebaseFirestore,
        'storage': window.firebaseStorage,
        'auth': window.firebaseAuth,
        'messaging': window.firebaseMessaging
    };

    return services[serviceName] || null;
}

// Test Firebase connection
function testFirebaseConnection() {
    console.log('🧪 Testing Firebase connection...');

    // Test Realtime Database
    if (window.firebaseRealtime) {
        window.firebaseRealtime.ref('.info/connected').once('value', (snapshot) => {
            if (snapshot.val() === true) {
                console.log('✅ Realtime Database connected');
            } else {
                console.warn('⚠️ Realtime Database not connected');
            }
        });
    }

    // Test Firestore
    if (window.firebaseFirestore) {
        window.firebaseFirestore.enableNetwork().then(() => {
            console.log('✅ Firestore connected');
        }).catch((error) => {
            console.warn('⚠️ Firestore connection issue:', error);
        });
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    waitForFirebase(() => {
        if (initializeFirebaseServices()) {
            testFirebaseConnection();
            
            // Trigger custom event for other scripts
            document.dispatchEvent(new CustomEvent('firebaseReady'));
        }
    });
});

// Export functions for global use
window.initializeFirebaseServices = initializeFirebaseServices;
window.setupFirebaseForUser = setupFirebaseForUser;
window.getFirebaseService = getFirebaseService;
window.testFirebaseConnection = testFirebaseConnection;
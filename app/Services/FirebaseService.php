<?php

namespace App\Services;

use Kreait\Firebase\Auth;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Factory;
use Lcobucci\JWT\UnencryptedToken;

class FirebaseService
{
    private static $instance = null;
    private static $credentialsCache = null;

    protected $auth;
    protected $firestore;

    // Use a singleton pattern to reuse the Firebase connection
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        try {
            // Suppress gRPC warnings
            putenv('GRPC_PHP_SUPPRESS_CREDENTIALS_WARNINGS=1');

            // Load credentials from file once and cache it in memory
            if (self::$credentialsCache === null) {
                self::$credentialsCache = json_decode(file_get_contents(base_path('firebase-credentials.json')), true);
            }

            // Create the Firebase factory
            $factory = (new Factory)
                ->withServiceAccount(self::$credentialsCache) // Use cached credentials
                ->withDatabaseUri(env('FIREBASE_DATABASE_URL'));

            // Limit connection creation to once
            $this->auth = $factory->createAuth();
            $this->firestore = $factory->createFirestore();
        } catch (\Exception $e) {
            \Log::error("Firebase initialization error: " . $e->getMessage());
            throw $e;
        }
    }

    public function createCustomToken($userId)
    {
        try {
            // Get token from Firebase
            $customToken = $this->auth->createCustomToken($userId);

            // Convert to string safely
            if (method_exists($customToken, 'toString')) {
                return $customToken->toString();
            }
            return (string) $customToken;
        } catch (\Exception $e) {
            \Log::error('Firebase token creation error: ' . $e->getMessage());
            return '';
        }
    }

    // Queue system for presence updates
    private $presenceUpdates = [];
    private $lastPresenceFlush = 0;
    private $presenceFlushInterval = 5; // seconds

    public function updateUserPresence($userId, $isOnline): void
    {
        // Queue the update
        $this->presenceUpdates[$userId] = $isOnline;

        // Flush the queue if it's been long enough
        $now = time();
        if ($now - $this->lastPresenceFlush >= $this->presenceFlushInterval) {
            $this->flushPresenceUpdates();
        }
    }

    private function flushPresenceUpdates(): void
    {
        if (empty($this->presenceUpdates)) {
            return;
        }

        try {
            foreach ($this->presenceUpdates as $userId => $isOnline) {
                $this->firestore->database()->collection('presence')->document($userId)->set([
                    'online' => $isOnline,
                    'lastSeen' => time() * 1000 // Milliseconds
                ]);
            }

            // Clear the queue
            $this->presenceUpdates = [];
            $this->lastPresenceFlush = time();
        } catch (\Exception $e) {
            \Log::error("Firebase batch presence update error: " . $e->getMessage());
        }
    }

    // Message batching system
    private $messageQueue = [];
    private $lastMessageFlush = 0;
    private $messageFlushInterval = 3; // seconds
    private $messageQueueSize = 5; // max messages before flushing

    public function saveMessage($senderId, $receiverId, $message): void
    {
        // Add message to queue
        $this->messageQueue[] = [
            'senderId' => $senderId,
            'receiverId' => $receiverId,
            'text' => $message,
            'timestamp' => time() * 1000
        ];

        // Flush if queue is full or interval has passed
        $now = time();
        if (
            count($this->messageQueue) >= $this->messageQueueSize ||
            $now - $this->lastMessageFlush >= $this->messageFlushInterval
        ) {
            $this->flushMessageQueue();
        }
    }

    private function flushMessageQueue(): void
    {
        if (empty($this->messageQueue)) {
            return;
        }

        try {
            // Group messages by room ID
            $roomMessages = [];
            foreach ($this->messageQueue as $msg) {
                $roomId = [$msg['senderId'], $msg['receiverId']];
                sort($roomId);
                $roomId = implode('_', $roomId);

                if (!isset($roomMessages[$roomId])) {
                    $roomMessages[$roomId] = [];
                }
                $roomMessages[$roomId][] = $msg;
            }

            // Process each room's messages
            foreach ($roomMessages as $roomId => $messages) {
                foreach ($messages as $msg) {
                    $this->firestore->database()->collection('chats')->document($roomId)
                        ->collection('messages')->add([
                                'senderId' => $msg['senderId'],
                                'text' => $msg['text'],
                                'timestamp' => $msg['timestamp']
                            ]);

                    // Update unread counter once per sender/receiver pair
                    $this->firestore->database()->collection('unread')->document($msg['receiverId'])
                        ->collection('senders')->document($msg['senderId'])->set([
                                'count' => ['increment' => count($messages)]
                            ], ['merge' => true]);
                }
            }

            // Clear the queue
            $this->messageQueue = [];
            $this->lastMessageFlush = time();
        } catch (\Exception $e) {
            \Log::error("Firebase flush message queue error: " . $e->getMessage());
        }
    }

    public function markMessagesAsRead($userId, $senderId): void
    {
        try {
            $this->firestore->database()->collection('unread')->document($userId)
                ->collection('senders')->document($senderId)->set([
                        'count' => 0
                    ], ['merge' => true]);
        } catch (\Exception $e) {
            \Log::error("Firebase mark messages as read error: " . $e->getMessage());
        }
    }

    // Cache system for unread counts
    private $unreadCountCache = [];
    private $unreadCountExpiry = [];
    private $unreadCacheTTL = 60; // seconds

    public function getUnreadCount($userId)
    {
        $now = time();

        // Return cached value if available and not expired
        if (
            isset($this->unreadCountCache[$userId]) &&
            $this->unreadCountExpiry[$userId] > $now
        ) {
            return $this->unreadCountCache[$userId];
        }

        try {
            $snapshot = $this->firestore->database()->collection('unread')
                ->document($userId)->collection('senders')->documents();

            $totalCount = 0;
            foreach ($snapshot as $doc) {
                $data = $doc->data();
                if (isset($data['count'])) {
                    $totalCount += $data['count'];
                }
            }

            // Cache the result
            $this->unreadCountCache[$userId] = $totalCount;
            $this->unreadCountExpiry[$userId] = $now + $this->unreadCacheTTL;

            return $totalCount;
        } catch (\Exception $e) {
            \Log::error("Firebase get unread count error: " . $e->getMessage());
            return 0;
        }
    }

    // Cache system for user status
    private $userStatusCache = [];
    private $userStatusExpiry = [];
    private $userStatusCacheTTL = 30; // seconds

    public function getUserStatus($userId)
    {
        $now = time();

        // Return cached value if available and not expired
        if (
            isset($this->userStatusCache[$userId]) &&
            $this->userStatusExpiry[$userId] > $now
        ) {
            return $this->userStatusCache[$userId];
        }

        try {
            $snapshot = $this->firestore->database()->collection('presence')
                ->document($userId)->snapshot();

            $status = ['online' => false, 'lastSeen' => null];

            if ($snapshot->exists()) {
                $status = $snapshot->data();
            }

            // Cache the result
            $this->userStatusCache[$userId] = $status;
            $this->userStatusExpiry[$userId] = $now + $this->userStatusCacheTTL;

            return $status;
        } catch (\Exception $e) {
            \Log::error("Firebase get user status error: " . $e->getMessage());
            return ['online' => false, 'lastSeen' => null];
        }
    }

    // Make sure to flush any pending operations when the service is destroyed
    public function __destruct()
    {
        $this->flushPresenceUpdates();
        $this->flushMessageQueue();
    }
}
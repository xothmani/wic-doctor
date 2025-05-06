<?php

namespace App\Services;

use Kreait\Firebase\Auth;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Factory;
use Lcobucci\JWT\UnencryptedToken;

class FirebaseService
{
    protected $auth;
    protected $firestore;

    public function __construct()
    {
        try {
            $factory = (new Factory)
                ->withServiceAccount(base_path('firebase-credentials.json'));

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
            // Return an empty string or throw an exception based on your preference
            return '';
        }
    }

    public function updateUserPresence($userId, $isOnline): void
    {
        try {
            $this->firestore->database()->collection('presence')->document($userId)->set([
                'online' => $isOnline,
                'lastSeen' => time() * 1000 // Milliseconds
            ]);
        } catch (\Exception $e) {
            \Log::error("Firebase update presence error: " . $e->getMessage());
            throw $e;
        }
    }

    public function saveMessage($senderId, $receiverId, $message): void
    {
        try {
            // Create a unique room ID by sorting and combining the user IDs
            $roomId = [$senderId, $receiverId];
            sort($roomId);
            $roomId = implode('_', $roomId);

            $this->firestore->database()->collection('chats')->document($roomId)
                ->collection('messages')->add([
                        'senderId' => $senderId,
                        'text' => $message,
                        'timestamp' => time() * 1000
                    ]);

            // Update unread messages counter - using the correct method
            $this->firestore->database()->collection('unread')->document($receiverId)
                ->collection('senders')->document($senderId)->set([
                        'count' => ['increment' => 1]  // Use FieldValue syntax for Firestore
                    ], ['merge' => true]);
        } catch (\Exception $e) {
            \Log::error("Firebase save message error: " . $e->getMessage());
            throw $e;
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
            throw $e;
        }
    }

    public function getUnreadCount($userId)
    {
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

            return $totalCount;
        } catch (\Exception $e) {
            \Log::error("Firebase get unread count error: " . $e->getMessage());
            return 0;
        }
    }

    public function getUserStatus($userId)
    {
        try {
            $snapshot = $this->firestore->database()->collection('presence')
                ->document($userId)->snapshot();

            if ($snapshot->exists()) {
                return $snapshot->data();
            }

            return ['online' => false, 'lastSeen' => null];
        } catch (\Exception $e) {
            \Log::error("Firebase get user status error: " . $e->getMessage());
            return ['online' => false, 'lastSeen' => null];
        }
    }
}
<?php

namespace App\Notifications;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Log;

class FCMServices
{
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(config('services.firebase.credentials'));
        $this->messaging = $factory->createMessaging();
    }

    public function sendNotification(string $token, string $title, string $body, array $data = [])
    {
        Log::info("Send Notification Firebase FCMServices ", ["title" => $title, "body" => $body]);
        $notification = Notification::create($title, $body);
        
        $message = CloudMessage::withTarget('token', $token)
            ->withNotification($notification)
            ->withData($data)
            ->withAndroidConfig([
                'priority' => 'high',
                /*'notification' => [
                    'sound' => 'default',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                ]*/
            ])
            ->withApnsConfig([
                'headers' => [
                    'apns-priority' => '10',
                ],
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                        'content-available' => 1
                    ]
                ]
            ]);
        Log::info("Send Notification Firebase FCMServices ", ["message" => json_encode($message)]);
        return $this->messaging->send($message);
    }
}
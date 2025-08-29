<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Http\Request;

class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(storage_path('app/firebase/mediverse-1bc4d-firebase-adminsdk-fbsvc-601ce038f7.json'));
        $this->messaging = $factory->createMessaging();
    }

    public function sendNotification($token, $title, $body, $data = [])
    {
        if (empty($token)) {
            throw new \InvalidArgumentException("FCM token is required but null or empty was given.");
        }

        $message = CloudMessage::new()->withTarget('token', $token)
            ->withNotification(Notification::create($title, $body))
            ->withData($data);

        return $this->messaging->send($message);
    }

}
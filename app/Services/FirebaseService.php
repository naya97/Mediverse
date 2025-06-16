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
            ->withServiceAccount(storage_path('app/firebase/mediverse-1bc4d-firebase-adminsdk-fbsvc-e845b66289.json'))
            ->withDatabaseUri('https://mediverse-1bc4d.firebaseio.com'); // اضف URI الخاص بك;

        $this->messaging = $factory->createMessaging();
    }

    public function sendToToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        $token = $request->input('token');
        $title = $request->input('title');
        $body = $request->input('body');

        $message = CloudMessage::withTarget('token', $token)
            ->withNotification(Notification::create($title, $body));

        return $this->messaging->send($message);
    }

}

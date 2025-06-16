<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Services\FirebaseService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $firebaseService;
    
    public function __construct(FirebaseService $firebase_service){
        $this->firebaseService = $firebase_service;

    }

    public function sendPushNotification(Request $request){
        $request->validate([
            'token' => 'required|string',
            'title' => 'required|string',
            'body' => 'required|string',
            'data' => 'nullable|array',
        ]);

        $token = $request->input('token');
        $title = $request->input('title');
        $body = $request->input('body');
        $data = $request->input('data' ,[]);

        $this->firebaseService->sendNotification($token,$title,$body,$data);

        return response()->json(['message' => 'Notification sent successfully!']);
    }
}
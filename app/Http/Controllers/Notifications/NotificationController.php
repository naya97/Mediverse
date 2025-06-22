<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function getAllNotifications()
    {
        $auth = Auth::user();
        if(!$auth) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = User::where('id', $auth->id)->first();
        if(!$user) return response()->json(['message' => 'user not found'], 404);

        $notifications = $user->notifications()->orderBy('created_at', 'desc')->get()->map(function($notification) {
            return [
                'id' => $notification->id,
                'type' => $notification->type,
                'data' => $notification->data,
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at,
            ];
        });

        return response()->json($notifications, 200);
    }

    public function getUnreadNotificationsCount()
    {
        $auth = Auth::user();
        if(!$auth) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = User::where('id', $auth->id)->first();
        if(!$user) return response()->json(['message' => 'user not found'], 404);

        $count = $user->unreadNotifications()->count();

        return response()->json(['unread_count' => $count], 200);
    }

    public function markNotificationAsRead(Request $request)
    {
        $auth = Auth::user();
        if(!$auth) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = User::where('id', $auth->id)->first();
        if(!$user) return response()->json(['message' => 'user not found'], 404);

        $notification = $user->notifications()->where('id', $request->notification_id)->first();

        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->is_read = true;
        $notification->read_at = now();
        $notification->save();

        return response()->json(['success' => true], 200);
    }
}
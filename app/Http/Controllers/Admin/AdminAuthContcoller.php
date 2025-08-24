<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AuthTrait;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AdminAuthContcoller extends Controller
{
    use AuthTrait;

    public function adminLogin(Request $request)
    {
        return $this->login($request, 'admin');
    }
    /////
    public function adminLogout()
    {
        return $this->logout();
    }
    /////
    public function adminSaveFcmToken(Request $request)
    {
        return $this->saveFcmToken($request, 'admin');
    }

    public function getAllAdminNotifications() {
        $auth = Auth::user();
        if(!$auth) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if($auth->role != 'admin') return response()->json(['message' => "You don't have permission"], 400);


        $user = User::where('role', 'admin')->where('id', $auth->id)->first();
        if(!$user) return response()->json(['message' => 'user not found'], 404);

        $unreadNotifications = $user->notifications()->where('is_read', false)->get();

        foreach($unreadNotifications as $unreadNotification) {
            $unreadNotification->is_read = true;
            $unreadNotification->read_at = now();
            $unreadNotification->save();
        }


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
}

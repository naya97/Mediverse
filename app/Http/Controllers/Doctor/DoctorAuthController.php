<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AuthTrait;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DoctorAuthController extends Controller
{
    use AuthTrait;

    public function doctorLogin(Request $request)
    {
        return $this->login($request, 'doctor');
    }
    /////
    public function doctorLogout()
    {
        return $this->logout();
    }
    /////
    public function doctorSaveFcmToken(Request $request)
    {
        return $this->saveFcmToken($request, 'doctor');
    }

    public function getAllDoctorNotifications() {
        $auth = Auth::user();
        if(!$auth) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        if($auth->role != 'doctor') return response()->json(['message' => "You don't have permission"], 400);

        $user = User::where('role', 'doctor')->where('id', $auth->id)->first();
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

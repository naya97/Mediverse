<?php

namespace App;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

trait AuthTrait
{
    public function login(Request $request, $role)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'string|email|max:255|required_without:phone',
            'phone' => 'phone:SY|required_without:email',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[0-9]/',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
            ],
        ],[
            'phone.phone' => 'enter a valid syrian phone number' ,
            'phone.unique' => 'this phone has already been taken'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->all()
            ], 400);
        }

        $user = null;

        if ($request->filled('email')) {
            $user = User::where('email', $request->get('email'))->first();
        } elseif ($request->filled('phone')) {
            $user = User::where('phone', $request->get('phone'))->first();
        }

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if (!Hash::check($request->get('password'), $user->password)) {
            return response()->json(['error' => 'Wrong password'], 401);
        }
        if ($user->role != $role) {
            return response()->json('You do not have permission in this page', 400);
        }

        try {
            $token = JWTAuth::claims(['role' => $user->role])->fromUser($user);

            return response()->json([
                'message' => 'User successfully logged in',
                'user' => $user,
                'token' => $token
            ], 200);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Successfully logged out'], 200);
    }

    public function saveFcmToken(Request $request, $role)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($user->role != $role) {
            return response()->json([
                'message' => 'you do not have permission to access this page',
            ], 401);
        }


        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $active_user = User::where('id', $user->id)->where('role', $role)->first();
        if (!$active_user) return response()->json(['message' => 'user not found'], 404);

        $active_user->fcm_token = $request->fcm_token;
        $active_user->save();

        return response()->json([
            'message' => 'Token saved successfully',
            'user' => $user,
        ], 200);
    }
}

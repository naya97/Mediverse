<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminAuthContcoller extends Controller
{
    public function login(Request $request)
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
        if ($user->role != 'admin') {
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
}

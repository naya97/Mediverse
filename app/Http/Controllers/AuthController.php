<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'string|email|max:255|required_without:phone',
            'phone' => 'phone:SY|required_without:email',
            'password' => ['required', 'string', 'min:8', 'regex:/[0-9]/', 'regex:/[a-z]/', 'regex:/[A-Z]/',],

        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $user = User::where('email', $request->get('email'))
            ->orWhere('phone', $request->get('phone'))
            ->first();

        if (!$user || !Hash::check($request->get('password'), $user->password)) {
            return response()->json(['error' => 'wrong password'], 401);
        }

        try {
            $token = JWTAuth::claims(['role' => $user->role])->fromUser($user);

            return response()->json(['message' => 'User successfully loggedin', 'token' => $token], 200);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
    }

    /////
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'string|email|max:255|unique:users|required_without:phone',
            'phone' => 'phone:SY|required_without:email|unique:users',
            'password' => ['required', 'string', 'min:8', 'regex:/[0-9]/', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'confirmed',],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'email' => $request->get('email') ?? null,
            'phone' => $request->get('phone') ?? null,
            'password' => Hash::make($request->get('password')),
            'role' => 'patient',
        ]);
        $patient = Patient::create([
            'user_id' => $user->id,
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user', 'token'), 201);
    }
    //
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Successfully logged out']);
    }
}

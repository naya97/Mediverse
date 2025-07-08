<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;

class AuthController extends Controller
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

        if($user->role == 'doctor') {
            try {
            $token = JWTAuth::claims(['role' => $user->role])->fromUser($user);

            $doctor = Doctor::find($user->id);
            $redirect = true;
            if($doctor) $redirect = false;

            return response()->json([
                'message' => 'User successfully logged in',
                'user' => $user,
                'complete_profile' => $redirect,
                'token' => $token
            ], 200);
            } catch (JWTException $e) {
                return response()->json(['error' => 'Could not create token'], 500);
            }
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


    /////
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'string|email|max:255|unique:users|required_without:phone',
            'phone' => 'phone:SY|required_without:email|unique:users',
            'password' => ['required', 'string', 'min:8', 'regex:/[0-9]/', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'confirmed',],
        ],[
            'phone.phone' => 'enter a valid syrian phone number' ,
            'phone.unique' => 'this phone has already been taken'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
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

        return response()->json([
            'message' => 'user successfully registered',
            'user' => $user,
            'token' => $token,
        ], 201);
    }
    //
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Successfully logged out'], 200);
    }
    /////
    public function getUser()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => 'unauthorized'
            ], 401);
        }
        $user = User::select('id', 'first_name', 'last_name', 'email', 'phone', 'role')
            ->where('id', $user->id)
            ->first();
        return response()->json($user, 200);
    }
    /////
    public function handleGoogleLogin(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        $client = new Client();

        try {
            $response = $client->get('https://oauth2.googleapis.com/tokeninfo', [
                'query' => ['id_token' => $request->id_token]
            ]);

            $googleUser = json_decode($response->getBody());

            if (!$googleUser->email_verified) {
                return response()->json(['error' => 'Email not verified'], 403);
            }

            $user = User::updateOrCreate(
                ['email' => $googleUser->email],
                [
                    'name' => $googleUser->name ?? 'Google User',
                    'avatar' => $googleUser->picture ?? null,
                    'password' => Hash::make(uniqid()) // كلمة مرور وهمية
                ]
            );

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'message' => 'Logged in with Google successfully',
                'token' => $token,
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid Google token'], 401);
        }
    }

    public function saveFcmToken(Request $request)
    {
        $user = Auth::user(); 

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $active_user = User::where('id', $user->id)->first();
        if(!$active_user) return response()->json(['message' => 'user not found'], 404);
        $active_user->fcm_token = $request->fcm_token;
        $active_user->save();

        return response()->json([
            'message' => 'Token saved successfully', 
            'user'=>$user,
        ], 200);
    }
}

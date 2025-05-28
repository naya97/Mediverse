<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Google\Client;

class GoogleAuthController extends Controller
{
    public function loginWithGoogle(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        $client = new Client(['client_id' => env('GOOGLE_CLIENT_ID')]); // from Google Console

        $payload = $client->verifyIdToken($request->id_token);

        if (!$payload) {
            return response()->json(['error' => 'Invalid Google Token'], 401);
        }

        $googleId = $payload['sub'];
        $email = $payload['email'];
        $name = $payload['name'];

        $user = User::where('google_id', $googleId)->orWhere('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'google_id' => $googleId,
                'password' => bcrypt('dummy123') // won't be used
            ]);
        }

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'user successuffly loged in',
            'token' => $token,
            'user' => $user
        ]);
    }
}

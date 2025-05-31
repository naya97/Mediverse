<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Google_Client;
use App\Models\Patient;

class GoogleAuthController extends Controller
{
    public function googleLogin(Request $request)
    {
        $idToken = $request->input('id_token');

        $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
        $payload = $client->verifyIdToken($idToken);

        if (!$payload) {
            return response()->json(['error' => 'Invalid ID Token'], 401);
        }

        $email = $payload['email'];

        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $payload['name'], 'password' => bcrypt(uniqid())]
        );

        if ($user->wasRecentlyCreated) {
            $patient = Patient::create([
                'user_id' => $user->id,
            ]);
        }


        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'patient successfully loged in',
            'token' => $token,
            'user' => $user,
        ]);
    }
}

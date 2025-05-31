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
        $fullName = trim($payload['name']);

        $nameParts = explode(' ', $fullName);
        $firstName = $nameParts[0];
        $lastName = isset($nameParts[1]) ? implode(' ', array_slice($nameParts, 1)) : '';

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'role' => 'patient',
                'password' => bcrypt(uniqid()),
            ]
        );

        $patient = Patient::updateOrCreate(
            ['user_id' => $user->id],
            [
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]
        );

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Patient successfully logged in',
            'token' => $token,
            'user' => $user,
        ]);
    }
}

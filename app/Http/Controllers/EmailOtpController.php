<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\SendOtpMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class EmailOtpController extends Controller
{
    public function send(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $otp = rand(1000, 9999);

        $text = "Your verification code is: $otp\nIf you did not request this code, please ignore this message.";

        Mail::raw($text, function ($message) use ($request) {
            $message->to($request->email)
                ->subject('Your OTP Code');
        });
        Cache::put('otp_email_' . $request->email, $otp, now()->addMinutes(30));
        return response()->json([
            'message' => 'OTP sent successfully',
        ], 200);
    }
    /////
    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:4',
        ]);

        $cachedOtp = Cache::get('otp_email_' . $request->email);

        if ($cachedOtp && $cachedOtp == $request->otp) {
            Cache::forget('otp_email_' . $request->email);

            $resetToken = Str::random(64);
            Cache::put('reset_token_' . $request->email, $resetToken, now()->addMinutes(15));

            return response()->json([
                'message' => 'OTP verified successfully',
                'reset_token' => $resetToken
            ], 200);
        }

        return response()->json([
            'message' => 'Invalid OTP',
        ], 400);
    }
    /////
    public function email_resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'reset_token' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $cachedToken = Cache::get('reset_token_' . $request->email);

        if ($cachedToken && $cachedToken === $request->reset_token) {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            $user->password = bcrypt($request->password);
            $user->save();

            Cache::forget('reset_token_' . $request->email);

            return response()->json(['message' => 'Password changed successfully'], 200);
        }

        return response()->json(['message' => 'Invalid or expired reset token'], 400);
    }
}

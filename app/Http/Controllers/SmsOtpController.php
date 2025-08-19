<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SmsGatewayService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class SmsOtpController extends Controller
{
    protected $smsService;

    public function __construct(SmsGatewayService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function send(Request $request)
    {
        $request->validate(['phone' => 'required']);
        $phone = $request->phone;

        if (strpos($phone, '+963') === 0) {
            $phone = '0' . substr($phone, 4);
        }
        $user = User::where('phone', $phone)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $otpCode = rand(1000, 9999);
        $message = "Your Babel OTP code is";
        $this->smsService->sendSMS($request->phone, $otpCode, $message);
        Cache::put('otp_phone_' . $request->phone, $otpCode, now()->addMinutes(30));
        return response()->json(['message' => 'OTP Sent successfully'], 200);
    }

    /////
    public function verify(Request $request)
    {
        $request->validate(['phone' => 'required', 'otp' => 'required|digits:4']);
        $cachedOtp = Cache::get('otp_phone_' . $request->phone);

        if ($cachedOtp && $cachedOtp == $request->otp) {
            Cache::forget('otp_phone_' . $request->phone);

            $resetToken = Str::random(64);
            Cache::put('reset_token_' . $request->phone, $resetToken, now()->addMinutes(15));

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
    public function phone_resetPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'reset_token' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $cachedToken = Cache::get('reset_token_' . $request->phone);

        if ($cachedToken && $cachedToken === $request->reset_token) {
            $phone = $request->phone;

            if (strpos($phone, '+963') === 0) {
                $phone = '0' . substr($phone, 4);
            }
            $user = User::where('phone', $phone)->first();
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

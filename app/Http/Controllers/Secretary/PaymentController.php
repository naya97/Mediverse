<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function addBill(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $appointment = Appointment::where('status', 'pending')
            ->where('id', $request->appointment_id)
        ->first();

        $appointment->price = $request->price;
        $appointment->save();

        return response()->json('successfully payed');
    }

    public function auth() {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => 'unauthorized'
            ], 401);
        }
        if ($user->role != 'secretary') {
            return response()->json('You do not have permission in this page', 400);
        }
    }
}

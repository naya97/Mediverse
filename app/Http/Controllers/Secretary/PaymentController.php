<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function addBill(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $appointment = Appointment::with('schedule.doctor')->where('status', 'pending')
            ->where('payment_status', 'pending')
            ->where('id', $request->appointment_id)
            ->first();

        if (!$appointment) return response()->json(['message' => 'appointment not found'], 404);

        $appointment->price = $request->price;
        $appointment->payment_status = 'paid';
        $appointment->save();

        $clinic = Clinic::where('id', $appointment->doctor->clinic_id)->first();
        if(!$clinic) return response()->json(['messsage' => 'clinic not found'], 404);

        $clinic->money += $appointment->price;
        $clinic->save();

        return response()->json(['message' => 'successfully payed'], 200);
    }

    public function auth()
    {
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

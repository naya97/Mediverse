<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function addBill(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $appointment = Appointment::with('schedule.doctor')
            ->where('id', $request->appointment_id)
            ->first();

        if (!$appointment) return response()->json(['message' => 'appointment not found'], 404);
        if ($appointment->payment_status == 'paid') return response()->json(['message' => 'you already paid for this appointment'], 409);

        $visit_fee = $appointment->schedule->doctor->visit_fee;

        if ($request->has('discount_code')) {
            $discount = Discount::where('discount_code', $request->discount_code)->first();
            if (!$discount) return response()->json(['message' => 'discount not found'], 404);
            $discount_rate = $discount->discount_rate;
            $appointment->price = ($visit_fee * $discount_rate) / 100;
        } else {
            $appointment->price = $visit_fee;
        }
        $appointment->payment_status = 'paid';
        $appointment->save();

        $clinic = Clinic::where('id', $appointment->schedule->doctor->clinic_id)->first();
        if (!$clinic) return response()->json(['messsage' => 'clinic not found'], 404);

        $clinic->money += $appointment->price;
        $clinic->save();

        return response()->json([
            'message' => 'successfully payed',
            'Bill' => $appointment->price,
        ], 200);
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

<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Discount;
use App\Models\Patient;
use App\Models\VaccinationRecord;
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

        $patient = Patient::find($appointment->patient_id);
        $discount = 0;
        $pointsToDeduct = 0;

        $totalPrice = $appointment->expected_price;


        if($request->has('discount_points') && $request->discount_points == true) {
            $points = $patient->discount_points;
            if($points < 6) {
                return response()->json([
                    'message' => "you don't have enough points, Points must be equal or more than 6",
                ], 400);
            }
            if($points >= 6 && $points < 10) {
                $discount = 0.05;
                $pointsToDeduct = 6;
            }
            elseif($points >= 10 && $points < 20) {
                $discount = 0.10;
                $pointsToDeduct = 10;
            }
            elseif($points >= 20 && $points < 30) {
                $discount = 0.20;
                $pointsToDeduct = 20;
            }
            elseif ($points >= 30) {
                $discount = 0.30;
                $pointsToDeduct = 30;
            }
        }

        $finalPrice = $totalPrice * (1 - $discount);
        $appointment->paid_price = $finalPrice;
        $appointment->save();
        
        $patient->discount_points -= $pointsToDeduct;
        $patient->save();

        $clinic = Clinic::where('id', $appointment->schedule->doctor->clinic_id)->first();
        if (!$clinic) return response()->json(['messsage' => 'clinic not found'], 404);

        $clinic->money += $finalPrice;
        $clinic->save();

        return response()->json([
            'message' => 'successfully payed',
            'Bill' => $finalPrice,
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

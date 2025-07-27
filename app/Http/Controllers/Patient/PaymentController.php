<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Discount;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\VaccinationRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Stripe;

class PaymentController extends Controller
{

    //------------------------------------Charge The Wallet-------------------------------------------------------

    public function createPaymentIntent(Request $request) {

        $auth = $this->auth();
        if($auth) return $auth;

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
               'message' =>  $validator->errors()->all()
            ], 400);
        }

        $amountInCents = $request->amount * 100;

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $amountInCents,
                'currency' => 'usd',
                'payment_method_types' => ['card'],
                'description' => 'Wallet recharge',
            ]);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
                'paymentIntentId' => $paymentIntent->id,
            ],200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    
    }

    public function confirmWalletRecharge(Request $request)
    {
        $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'message' => 'unauthorized'
                ], 401);
            }
        if ($user->role != 'patient') {
            return response()->json('You do not have permission in this page', 400);
        }

        $patient = Patient::where('user_id', $user->id)->first();
        

        $validator = Validator::make($request->all(), [
            'payment_intent_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
               'message' =>  $validator->errors()->all()
            ], 400);
        }
        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $intent = PaymentIntent::retrieve($request->payment_intent_id);

            if ($intent->status == 'succeeded') {
                $amount = $intent->amount / 100;

                $user = Auth::user();

                $patient->wallet += $amount;
                $patient->save();

                return response()->json([
                    'message' => 'wallet charged successfully',
                    'wallet' => $patient->wallet
                ], 200);
            }

            return response()->json([
                'message' => 'payment failed'
            ], 400);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    //-------------------------------------Reservation Payment------------------------------------------------


    // public function createReservationPaymentIntent(Request $request)
    // {
    //     $auth = $this->auth();
    //     if($auth) return $auth;

    //     $validator = Validator::make($request->all(), [
    //         'reservation_id' => 'required|exists:appointments,id',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['message' => $validator->errors()->all()], 400);
    //     }

    //     $reservation = Appointment::with('schedule.doctor')->find($request->reservation_id);
    //     if(!$reservation) return response()->json(['message' => 'reservation not found'], 404);

    //     $doctorAmount = $reservation->schedule->doctor->visit_fee ?? null;

    //     if (!$doctorAmount || $doctorAmount < 0.5) {
    //         return response()->json(['message' => 'Invalid doctor fee amount. Must be at least $0.50'], 400);
    //     }

    //     if ($reservation->payment_status == 'paid') {
    //         return response()->json(['message' => 'Reservation already paid'], 400);
    //     }

    //     $patient = Patient::where('id', $reservation->patient_id)->first();
    //     if(!$patient) return response()->json(['message' => 'Patient Not Found'], 404);

    //     if($patient->wallet < $doctorAmount) {
    //         return response()->json(['message' => 'You do not have enough money to pay'], 400);
    //     }

    //     Stripe::setApiKey(env('STRIPE_SECRET'));

    //     try {
    //         $paymentIntent = PaymentIntent::create([
    //             'amount' => $doctorAmount * 100, 
    //             'currency' => 'usd',
    //             'payment_method_types' => ['card'],
    //             'description' => "Payment for reservation #{$reservation->id}",
    //         ]);

    //         $reservation->payment_intent_id = $paymentIntent->id;
    //         $reservation->save();

    //         return response()->json([
    //             'clientSecret' => $paymentIntent->client_secret,
    //             'paymentIntentId' => $paymentIntent->id,
    //         ], 200);

    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }

    // public function confirmReservationPayment(Request $request)
    // {
    //      $user = Auth::user();
    //         if (!$user) {
    //             return response()->json([
    //                 'message' => 'unauthorized'
    //             ], 401);
    //         }
    //     if ($user->role != 'patient') {
    //         return response()->json('You do not have permission in this page', 400);
    //     }

    //     $patient = Patient::where('user_id', $user->id)->first();

    //     $validator = Validator::make($request->all(), [
    //         'payment_intent_id' => 'required|string',
    //         'reservation_id' => 'required|exists:appointments,id',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['message' => $validator->errors()->all()], 400);
    //     }

    //     $reservation = Appointment::with('schedule.doctor')->find($request->reservation_id);

    //     if ($reservation->patient_id !== $patient->id) {
    //         return response()->json(['message' => 'You do not have permission to confirm this reservation'], 403);
    //     }

    //     Stripe::setApiKey(env('STRIPE_SECRET'));

    //     try {
    //         $intent = PaymentIntent::retrieve($request->payment_intent_id);

    //         if ($intent->status === 'succeeded' && $reservation->payment_intent_id === $request->payment_intent_id) {
    //             $reservation->payment_status = 'paid';
    //             $reservation->price = $reservation->schedule->doctor->visit_fee;
    //             $reservation->save();

    //             $patient->wallet -= $reservation->schedule->doctor->visit_fee;
    //             $patient->save();

    //             $clinic = Clinic::where('id', $reservation->schedule->doctor->clinic->id)->first();
    //             if(!$clinic) return response()->json(['messsage' => 'clinic not found'], 404);

    //             $clinic->money += $reservation->price;
    //             $clinic->save();

    //             return response()->json([
    //                 'message' => 'Reservation payment confirmed successfully.',
    //                 'reservation' => $reservation,
    //             ], 200);
    //         }

    //         return response()->json(['message' => 'Payment not successful or does not match reservation'], 400);

    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }

    public function ReservationPayment(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $validator = Validator::make($request->all(), [
            'reservation_id' => 'required|exists:appointments,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->all()], 400);
        }

        $reservation = Appointment::with('schedule.doctor')->find($request->reservation_id);
        if(!$reservation) return response()->json(['message' => 'reservation not found'], 404);

        $doctorAmount = $reservation->schedule->doctor->visit_fee ?? null;

        if (!$doctorAmount || $doctorAmount < 0.5) {
            return response()->json(['message' => 'Invalid doctor fee amount. Must be at least $0.50'], 400);
        }

        if ($reservation->payment_status == 'paid') {
            return response()->json(['message' => 'Reservation already paid'], 400);
        }

        $patient = Patient::find($reservation->patient_id);
        if(!$patient) return response()->json(['message' => 'Patient Not Found'], 404);
        
        $walletOwner = $patient;

        if ($patient->parent_id) {
            $parent = Patient::find($patient->parent_id);
            
            if (!$parent) {
                return response()->json(['message' => 'Patient Not Found'], 404);
            }

            $walletOwner = $parent;
        }

        $totalPrice = $doctorAmount;

        if($reservation->appointment_type == 'vaccination') {
            $vaccinationRecord = VaccinationRecord::with('vaccine')
            ->where('appointment_id', $reservation->id)
            ->first();

            if($vaccinationRecord && $vaccinationRecord->vaccine) {
                $vaccinationPrice = $vaccinationRecord->vaccine->price;
                $totalPrice += $vaccinationPrice;
            }else {
                return response()->json(['message' => 'vaccination record or vaccination not found'], 404);
            }
        }

        if($walletOwner->wallet < $doctorAmount) {
            return response()->json(['message' => 'You do not have enough money to pay'], 400);
        }

        $discount = 0;
        $pointsToDeduct = 0;

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
        $reservation->price = $finalPrice;

        $reservation->payment_status = 'paid';
        $reservation->save();

        $walletOwner->wallet -= $finalPrice;
        $walletOwner->save();

        $patient->discount_points -= $pointsToDeduct;
        $patient->save();

        $clinic = Clinic::where('id', $reservation->schedule->doctor->clinic->id)->first();
        if(!$clinic) return response()->json(['messsage' => 'clinic not found'], 404);

        $clinic->money += $finalPrice;
        $clinic->save();

        return response()->json([
            'message' => 'Reservation payment confirmed successfully.',
            'reservation' => $reservation,
        ], 200);
        
    }



    public function cancelReservationAndRefund(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'reservation_id' => 'required|exists:appointments,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->all()], 400);
        }

        $reservation = Appointment::with('schedule.doctor')->where('status', 'pending')->find($request->reservation_id);
        if(!$reservation) return response()->json(['message' => 'Reservaion Not Found'], 404);
        $patient = Patient::find($reservation->patient_id);

        if ($reservation->payment_status != 'paid') {
            $reservation->status = 'cancelled';
            $reservation->price = 0;
            $reservation->save();

            return response()->json(['message' => 'Reservation cancelled (not paid).'], 200);
        }


        $reservation->status = 'cancelled';
        $reservation->save();

        $walletOwner = $patient;

        if ($patient->parent_id) {
            $parent = Patient::find($patient->parent_id);

            if (!$parent) {
                return response()->json(['message' => 'Patient Not Found'], 404);
            }

            $walletOwner = $parent;
        }

        $walletOwner->wallet += $reservation->price;
        $walletOwner->save();
        
        $clinic = Clinic::where('id', $reservation->schedule->doctor->clinic->id)->first();
        if(!$clinic) return response()->json(['messsage' => 'clinic not found'], 404);

        $clinic->money -= $reservation->price;
        $clinic->save();

        return response()->json([
            'message' => 'Reservation cancelled and payment refunded.',
        ], 200);

        
    }

    public function showWalletRange() {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => 'unauthorized'
            ], 401);
        }
        if ($user->role != 'patient') {
            return response()->json('You do not have permission in this page', 400);
        }

        $patient = Patient::where('user_id', $user->id)->first();
        if(!$patient) return response()->json(['message' => 'Patient Not Found'], 404);

        return response()->json([
            'wallet' => $patient->wallet,
        ], 200);
    }

    public function auth() {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => 'unauthorized'
            ], 401);
        }
        if ($user->role != 'patient') {
            return response()->json('You do not have permission in this page', 400);
        }
    }
}

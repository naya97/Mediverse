<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
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
        Stripe::setApiKey(config('services.stripe.secret'));

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


    public function createReservationPaymentIntent(Request $request)
    {
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


        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $doctorAmount * 100, 
                'currency' => 'usd',
                'payment_method_types' => ['card'],
                'description' => "Payment for reservation #{$reservation->id}",
            ]);

            $reservation->payment_intent_id = $paymentIntent->id;
            $reservation->save();

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
                'paymentIntentId' => $paymentIntent->id,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function confirmReservationPayment(Request $request)
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
            'reservation_id' => 'required|exists:appointments,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->all()], 400);
        }

        $reservation = Appointment::with('schedule.doctor')->find($request->reservation_id);

        if ($reservation->patient_id !== $patient->id) {
            return response()->json(['message' => 'You do not have permission to confirm this reservation'], 403);
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $intent = PaymentIntent::retrieve($request->payment_intent_id);

            if ($intent->status === 'succeeded' && $reservation->payment_intent_id === $request->payment_intent_id) {
                $reservation->payment_status = 'paid';
                $reservation->price = $reservation->schedule->doctor->visit_fee;
                $reservation->save();

                return response()->json([
                    'message' => 'Reservation payment confirmed successfully.',
                    'reservation' => $reservation,
                ], 200);
            }

            return response()->json(['message' => 'Payment not successful or does not match reservation'], 400);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
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

        $reservation = Appointment::find($request->reservation_id);
        if(!$reservation) return response()->json(['message' => 'Reservaion Not Found'], 404);
        $patient = Patient::where('user_id', $user->id)->first();

        if ($reservation->patient_id !== $patient->id) {
            return response()->json(['message' => 'You do not have permission to cancel this reservation'], 403);
        }

        if ($reservation->payment_status != 'paid') {
            $reservation->status = 'cancelled';
            $reservation->price = 0;
            $reservation->save();

            return response()->json(['message' => 'Reservation cancelled (not paid).'], 200);
        }

        if (!$reservation->payment_intent_id) {
            return response()->json(['message' => 'No payment intent associated with this reservation'], 400);
        }

        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));

            $refund = Refund::create([
                'payment_intent' => $reservation->payment_intent_id,
            ]);

            $reservation->status = 'cancelled';
            $reservation->save();

            return response()->json([
                'message' => 'Reservation cancelled and payment refunded.',
                'refund_status' => $refund->status,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
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

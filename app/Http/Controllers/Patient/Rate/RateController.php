<?php

namespace App\Http\Controllers\Patient\Rate;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientReview;
use App\Models\Review;
use App\Notifications\DoctorRated;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RateController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebase_service)
    {
        $this->firebaseService = $firebase_service;
    }

    public function patientRate(Request $request)
    {
        $user = Auth::user();

        //check the auth
        if (!$user) {
            return response()->json([
                'message' => 'unauthorized'
            ], 401);
        }

        if ($user->role != 'patient') {
            return response()->json([
                'message' => 'you dont have permission'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'rate' => 'required|integer|min:0|max:5',
            'comment' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }

        // create new rate 
        $review = Review::create([
            'rate' => $request->rate,
            'comment' => $request->comment,
        ]);

        $patient = Patient::with('user')->where('user_id', $user->id)->first();

        $patient_review = PatientReview::create([
            'patient_id' => $patient->id,
            'doctor_id' => $request->doctor_id,
            'review_id' => $review->id,
        ]);

        // update final doctor rate 
        $doctor = Doctor::with('user')->where('id', $request->doctor_id)->first();
        if (!$doctor) return response()->json(['message' => 'Not Found', 404]);
        $lastRate = $doctor->finalRate;
        $newRate = $request->rate;
        $finalRate = ($lastRate + $newRate) / 2;
        if ($lastRate == 0) $finalRate = $newRate;
        $doctor->update([
            'finalRate' => $finalRate,
        ]);

        //notification rate
        if ($doctor->user->fcm_token) {
            $this->firebaseService->sendNotification(
                $doctor->user->fcm_token,
                $patient->user->first_name . ' ' . $patient->user->last_name . ' , rated you ',
                'rate ' . $request->rate,
            );

            $doctor->user->notify(new DoctorRated([
                'user_id' => $patient->user->id,
                'user_name' => $patient->user->first_name . ' ' . $patient->user->last_name,
                'rating' => $request->rate,
            ]));
        }

        return response()->json([
            'message' => 'ok',
            'data' => $review,
        ], 200);
    }
    /////
    public function showDoctorReviews(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'unauthorized'
            ], 401);
        }

        if ($user->role != 'patient') {
            return response()->json([
                'message' => 'you dont have permission'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required|exists:doctors,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }

        $doctor = Doctor::find($request->doctor_id);

        if (!$doctor) {
            return response()->json([
                'message' => 'Doctor not found'
            ], 404);
        }

        $reviews = PatientReview::with(['review', 'patient'])
            ->where('doctor_id', $doctor->id)
            ->get();

        $response = $reviews->map(function ($item) {
            return [
                'patient_name' => trim($item->patient->first_name . ' ' . $item->patient->last_name),
                'rate' => $item->review->rate,
                'comment' => $item->review->comment,
            ];
        });

        return response()->json($response, 200);
    }
}

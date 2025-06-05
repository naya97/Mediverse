<?php

namespace App\Http\Controllers\Patient\Rate;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientReview;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RateController extends Controller
{
    public function patientRate(Request $request) {
        $user = Auth::user();

        $user = Auth::user();
        //check the auth
        if(!$user) {
            return response()->json([
                'message' => 'unauthorized'
            ],401);
        }

        if($user->role != 'patient') {
            return response()->json([
                'message' => 'you dont have permission'
            ],401);
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

        $patient = Patient::where('user_id',$user->id)->first();

        $patient_review = PatientReview::create([
            'patient_id' => $patient->id,
            'doctor_id' => $request->doctor_id,
            'review_id' => $review->id,
        ]);

        // update final doctor rate 
        $doctor = Doctor::where('id',$request->doctor_id)->first();
        if(!$doctor) return response()->json(['message' => 'Not Found', 404]);
        $lastRate = $doctor->finalRate;
        $newRate = $request->rate;
        $finalRate = ($lastRate + $newRate) / 2;
        if($lastRate == 0) $finalRate = $newRate;
        $doctor->update([
            'finalRate' => $finalRate,
        ]);

        return response()->json([
            'message' => 'ok',
            'data' => $review,
        ], 200);

    }
}

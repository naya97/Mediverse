<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\PatientReview;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class DoctorController extends Controller
{
    public function showDoctors() {
        $auth = $this->auth();
        if($auth) return $auth;

        $doctors = Doctor::select(
                'first_name',
                'last_name',
                'clinic_id',
                'photo',
                'speciality',
                'finalRate',
                'visit_fee',
                'treated',
                'status'
                )
            -> get();
        return response()->json($doctors, 200);
    }

    public function addDoctor(Request $request)  {
        $auth = $this->auth();
        if($auth) return $auth;
        
        $validator = Validator::make($request->all(), [
            'department' => 'required|string',
            'first_name' => 'string|required',
            'last_name' => 'string|required',
            'email' => 'string|email|max:255|required|unique:users',
            'phone' => 'phone:SY|unique:users',
            'password' => ['required', 'string', 'min:8', 'regex:/[0-9]/', 'regex:/[a-z]/', 'regex:/[A-Z]/',],
        ]);

        if ($validator->fails()) {
            return response()->json([
               'message' =>  $validator->errors()->all()
            ], 400);
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->get('password')),
            'role' => 'doctor',
        ]);

        $user->save();

        $clinic = Clinic::where('name',$request->department)->first();

        $doctor = Doctor::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
        ]);

        $clinic->numOfDoctors += 1;
        $clinic->save();

        return response()->json([
            'message' => 'created',
            'data' => $doctor,
        ],201);

    }

    public function removeDoctor(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $doctor = Doctor::where('id', $request->doctor_id)->first();
        $clinic = Clinic::where('id', $doctor->clinic_id)->first();
        $user = User::where('id',$doctor->user_id)->first();

        $doctor->delete();
        $user->delete();

        $clinic->numOfDoctors -= 1;
        $clinic->save();

        return response()->json('deleted successfully', 200);
    }

    public function showDoctorReviews(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $reviews = PatientReview::where('doctor_id', $request->doctor_id)->get();
        $review_ids = $reviews->pluck('review_id')->all();

        foreach($review_ids as $review_id) {
            $response [] = Review::where('id', $review_id)->first();
        }

        return response()->json($response, 200);
    }

    public function auth() {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => 'unauthorized'
            ], 401);
        }
        if ($user->role != 'admin') {
            return response()->json('You do not have permission in this page', 400);
        }
    }
}

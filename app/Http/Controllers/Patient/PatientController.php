<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PatientController extends Controller
{
    public function completePatientInfo(Request $request) {
        $user = Auth::user();

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
        $patient = Patient::where('user_id',$user->id)->first();

        $validator = Validator::make($request->all(), [
            'first_name' => 'string|required',
            'last_name' => 'string|required',
            'age' => 'integer|required',
            'gender' => 'in:male,female|required',
            'blood_type' => 'string|nullable',
            'address' => 'string|nullable',

        ]);

        if ($validator->fails()) {
            return response()->json([
               'message' =>  $validator->errors()->all()
            ], 400);
        }

        $current_user = User::where('id',$user->id)->first();
        $current_user->update([
            'first_name'=>$request->first_name,
            'last_name'=>$request->last_name,
        ]);

        $patient->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'age' => $request->age,
            'gender' => $request->gender,
            'blood_type' => $request->blood_type,
            'address' => $request->address,
        ]);
        
        return response()->json([
            'message' => 'patient data completed successfully',
            'data' => $patient
        ],200);

    }

    public function showProfile() {
        $user = Auth::user();

        //check the auth
        if(!$user) {
            return response()->json([
                'message' => 'unauthorized'
            ],401);
        }

        if(!$user->role == 'patient') {
            return response()->json([
                'message' => 'you dont have permission'
            ],401);
        }

        $patient = Patient::where('user_id',$user->id)->first();

        $response = [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'age' => $patient->age,
            'gender' => $patient->gender,
            'blood_type' => $patient->blood_type,
            'address' => $patient->address,
        ];

        return response()->json([
            'message' => 'ok',
            'data' => $response,
        ],200);
    }

    public function editProfile(Request $request) {
        $user = Auth::user();
        //check the auth
        if(!$user) {
            return response()->json([
                'message' => 'unauthorized'
            ],401);
        }

        if(!$user->role == 'patient') {
            return response()->json([
                'message' => 'you dont have permission'
            ],401);
        }

        // check the request
        $validator = Validator::make($request->all(), [
            'first_name' => 'string|nullable',
            'last_name' => 'string|nullable',
            'email' => 'string|email|max:255|nullable',
            'phone' => 'phone:SY|nullable',
            'old_password' => [ 'string', 'min:8', 'regex:/[0-9]/', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'nullable'],
            'password' => [ 'string', 'min:8', 'regex:/[0-9]/', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'confirmed', 'nullable'],
            'age' => 'integer|nullable',
            'gender' => 'in:male,female|nullable',
            'blood_type' => 'string|nullable',
            'address' => 'string|nullable',

        ]);

        if ($validator->fails()) {
            return response()->json([
               'message' =>  $validator->errors()->all()
            ], 400);
        }

        //fetch the patient and user 
        $patient = Patient::where('user_id', $user->id)->first();
        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }
        $user = $patient->user()->first();

        // if the user descide to change the pass
        if($request->filled('password')){
            if(! $request->filled('old_password')){
                return response()->json(['message'=>'you have to enter old_password to change password']);
            }
            if(! Hash::check($request->old_password,$user->password)){
                return response()->json(['message'=>'old password is wrong']);
            }
        }

        $user->update($request->all());
        $patient->update($request->all());

        $response = [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'age' => $patient->age,
            'gender' => $patient->gender,
            'blood_type' => $patient->blood_type,
            'address' => $patient->address,
        ];

        return response()->json([
            'message' => 'profile has been updated',
            'data' => $response
        ],200);
    }
}

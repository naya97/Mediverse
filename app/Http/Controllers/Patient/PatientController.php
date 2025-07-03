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
    public function completePatientInfo(Request $request)
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
        $patient = Patient::where('user_id', $user->id)->first();

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

        $current_user = User::where('id', $user->id)->first();
        $current_user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
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
        ], 200);
    }

    public function showProfile(Request $request)
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
        if ($request->has('child_id')) {
            $patient = Patient::where('id', $request->child_id)->first();
            $phone = null;
            $email = null;
        } else {
            $patient = Patient::where('user_id', $user->id)->first();
            $phone = $user->phone;
            $email = $user->email;
        }
        if (!$patient) return response()->json(['message' => 'Patient Not Found'], 404);

        $response = [
            'id' => $patient->id,
            'first_name' => $patient->first_name,
            'last_name' => $patient->last_name,
            'email' => $email,
            'phone' => $phone,
            'age' => $patient->age,
            'gender' => $patient->gender,
            'blood_type' => $patient->blood_type,
            'address' => $patient->address,
        ];

        return response()->json([
            'message' => 'ok',
            'data' => $response,
        ], 200);
    }


    public function editProfile(Request $request)
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

        // check the request
        $validator = Validator::make($request->all(), [
            'first_name' => 'string|nullable',
            'last_name' => 'string|nullable',
            'email' => 'string|email|max:255|nullable',
            'phone' => 'phone:SY|nullable',
            'old_password' => ['string', 'min:8', 'regex:/[0-9]/', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'nullable'],
            'password' => ['string', 'min:8', 'regex:/[0-9]/', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'confirmed', 'nullable'],
            'age' => 'integer|nullable',
            'gender' => 'in:male,female|nullable',
            'blood_type' => 'string|nullable',
            'address' => 'string|nullable',

        ],[
            'phone.phone' => 'enter a valid syrian phone number' ,
            'phone.unique' => 'this phone has already been taken'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }

        //fetch the patient and user

        if ($request->has('child_id')) {
            $patient = Patient::where('id', $request->child_id)->first();
            $phone = null;
            $email = null;
            if (!$patient) {
                return response()->json(['message' => 'Patient not found'], 404);
            }
        } else {
            $patient = Patient::where('user_id', $user->id)->first();
            $phone = $user->phone;
            $email = $user->email;
            if (!$patient) {
                return response()->json(['message' => 'Patient not found'], 404);
            }
            $user = $patient->user()->first();

            // if the user descide to change the pass
            if ($request->filled('password')) {
                if (! $request->filled('old_password')) {
                    return response()->json(['message' => 'you have to enter old_password to change password'], 422);
                }
                if (! Hash::check($request->old_password, $user->password)) {
                    return response()->json(['message' => 'old password is wrong'], 422);
                }
            }

            $user->update($request->all());
        }

        $patient->update($request->all());

        $response = [
            'id' => $patient->id,
            'first_name' => $patient->first_name,
            'last_name' => $patient->last_name,
            'email' => $email,
            'phone' => $phone,
            'age' => $patient->age,
            'gender' => $patient->gender,
            'blood_type' => $patient->blood_type,
            'address' => $patient->address,
        ];

        return response()->json([
            'message' => 'profile has been updated',
            'data' => $response
        ], 200);
    }
    /////
    public function addChild(Request $request)
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
        $patient = Patient::where('user_id', $user->id)->first();
        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }
        $validator = Validator::make($request->all(), [
            'first_name' => 'string|nullable',
            'last_name' => 'string|nullable',
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
        $child = Patient::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'age' => $request->age,
            'gender' => $request->gender,
            'blood_type' => $request->blood_type,
            'address' => $request->address,
            'parent_id' => $patient->id,
        ]);

        return response()->json([
            'message' => 'child added successfully',
            'child' => $child
        ], 200);
    }
    /////
    public function deleteChild(Request $request)
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
        $parent = Patient::where('user_id', $user->id)->first();
        if (!$parent) {
            return response()->json(['message' => 'parent not found'], 404);
        }
        $child = Patient::where('id', $request->child_id)->first();
        if (!$child) {
            return response()->json(['message' => 'child not found'], 404);
        }

        $child->delete();

        return response()->json([
            'message' => 'child deleted successfully'
        ], 200);
    }
    /////
    public function showAllChildren()
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
        $patient = Patient::where('user_id', $user->id)->first();
        if (!$patient) {
            return response()->json(['message' => 'parent not found'], 404);
        }
        $children = Patient::where('parent_id', $patient->id)->get()->all();
        $response = [];
        foreach ($children as $child) {
            $response[] = [
                'id' => $child->id,
                'first_name' => $child->first_name,
                'last_name' => $child->last_name,
            ];
        }
        return response()->json($response, 200);
    }
}

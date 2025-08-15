<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\ChildRecord;
use App\Models\Patient;
use App\Models\User;
use App\Models\VaccinationRecord;
use App\Models\Vaccine;
use Carbon\Carbon;
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
            'birth_date' => 'date|required',
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
            'birth_date' => $request->birth_date,
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

            
            $child_record = ChildRecord::where('child_id', $request->child_id)->first();
            if(!$child_record) $record = null;
            else $record = $child_record->id;
            
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
            'birth_date' => $patient->birth_date,
            'gender' => $patient->gender,
            'blood_type' => $patient->blood_type,
            'address' => $patient->address,
            'discount_points' => $patient->discount_points,
            'child_record' => $record ?? null,
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
            'birth_date' => 'date|nullable',
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
            'birth_date' => $patient->birth_date,
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

    function ageStringToMonths($ageStr) {
        $ageStr = strtolower(trim($ageStr));
        if ($ageStr == 'at birth' || $ageStr == 'birth' || $ageStr == 'newborn') return 0; 
        $number = (int) filter_var($ageStr, FILTER_SANITIZE_NUMBER_INT);
        return strpos($ageStr, 'year') !== false ? $number * 12 :
        (strpos($ageStr, 'month') !== false ? $number : null);
    }

    private function generateVaccinationRecordsForChild($child) {

        $birthDate = Carbon::parse($child->birth_date); // get the age months or years 
        $now = Carbon::now();
        $ageInMonths = (int) $birthDate->diffInMonths($now);

        $recommendedNow = [];
        $upcomingVaccines = [];
        
        $vaccines = Vaccine::all();

        foreach($vaccines as $vaccine) {
            $ageGroups = explode(',', $vaccine->age_group);

            foreach($ageGroups as $groupAge) {
                $groupAge = trim($groupAge);
                $groupAgeMonths = $this->ageStringToMonths($groupAge);
                if ($groupAgeMonths !== null) {
                    $vaccineDose = [
                        'vaccine_id' => $vaccine->id,
                        'name' => $vaccine->name,
                        'description' => $vaccine->description,
                        'dose_age' => $groupAge,
                        'dose_age_months' => $groupAgeMonths,
                        'price' => $vaccine->price,
                    ];
                    if ($groupAgeMonths == $ageInMonths) {
                        $recommendedNow[] = $vaccineDose;
                    } elseif ($groupAgeMonths > $ageInMonths){
                        $upcomingVaccines[] = $vaccineDose;
                    }
                }
            }

        }

        foreach ($recommendedNow as $vaccine) {
            VaccinationRecord::create([
                'child_id' => $child->id,
                'vaccine_id' => $vaccine['vaccine_id'],
                'appointment_id' => null,
                'dose_number' => 1,
                'notes' => null,
                'isTaken' => false,
                'recommended' => 'now',
                'when_to_take' => $vaccine['dose_age'],
                'next_vaccine_date' => null,
            ]);
        }

        foreach ($upcomingVaccines as $vaccine) {
            VaccinationRecord::create([
                'child_id' => $child->id,
                'vaccine_id' => $vaccine['vaccine_id'],
                'appointment_id' => null,
                'dose_number' => 1,
                'notes' => null,
                'isTaken' => false,
                'recommended' => 'upcoming',
                'when_to_take' => $vaccine['dose_age'],
                'next_vaccine_date' => null,
            ]);
        }

         return [
            'age_in_months' => $ageInMonths,
            'recommended_now' => $recommendedNow,
            'upcoming_vaccines' => $upcomingVaccines,
        ];
    }

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
            'first_name' => 'string|required',
            'last_name' => 'string|required',
            'birth_date' => 'date|required',
            'gender' => 'in:male,female|required',
            'blood_type' => 'string|required',
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
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
            'blood_type' => $request->blood_type,
            'address' => $request->address,
            'parent_id' => $patient->id,
        ]);

        $result = $this->generateVaccinationRecordsForChild($child);

        return response()->json([
            'message' => 'child added successfully',
            'child' => $child,
            'age_in_months' => $result['age_in_months'],
            'recommended_now' => $result['recommended_now'],
            'upcoming_vaccines' => $result['upcoming_vaccines'],
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

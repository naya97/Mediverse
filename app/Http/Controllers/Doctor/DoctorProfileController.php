<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientReview;
use App\Models\Review;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class DoctorProfileController extends Controller
{
    public function profile()
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();
        if (!$doctor) return response()->json(['message' => 'Doctor Not Found'], 404);
        $clinic = Clinic::where('id', $doctor->clinic_id)->first();
        if (!$clinic) return response()->json(['message' => 'Clinic Not Found'], 404);
        $workDays = Schedule::where('doctor_id', $doctor->id)->where('clinic_id', $clinic->id)->get();
        if ($workDays->isEmpty()) {
            return response()->json(['message' => 'No schedule available yet'], 404);
        }
        $schedule = [];
        foreach ($workDays as $workDay) {
            $schedule[] = [
                'day' => $workDay->day,
                'Shift' => $workDay->Shift,
            ];
        }
        $response = [
            'first_name' => $doctor->first_name,
            'last_name' => $doctor->last_name,
            'photo' => $doctor->photo,
            'clinic' => $clinic->name,
            'speciality' => $doctor->speciality,
            'professional_title' => $doctor->professional_title,
            'finalRate' => $doctor->finalRate,
            'average_visit_duration' => $doctor->average_visit_duration,
            'visit_fee' => $doctor->visit_fee,
            'experience' => $doctor->experience,
            'treated' => $doctor->treated,
            'status' => $doctor->status,
            'sign' => $doctor->sign,
            'schedule' => $schedule
        ];
        return response()->json($response, 200);
    }
    /////
    public function editProfile(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'first_name' => 'string|nullable',
            'last_name' => 'string|nullable',
            'email' => 'string|email|max:255|nullable',
            'phone' => 'phone:SY|nullable',
            'old_password' => ['string', 'min:8', 'regex:/[0-9]/', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'nullable'],
            'password' => ['string', 'min:8', 'regex:/[0-9]/', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'confirmed', 'nullable'],
            'photo' => 'image',
            'speciality' => 'string|nullable',
            'professional_title' => 'string|nullable',
            'average_visit_duration' => 'required|string|nullable',
            'visit_fee' => 'required|nullable',
            'experience' => 'integer|nullable',
            'sign' => 'image',
            'status' => 'string|nullable'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }

        $doctor = Doctor::where('user_id', $user->id)->first();
        if (!$doctor) return response()->json(['message' => 'Doctor Not Found'], 404);
        if ($request->hasFile('photo')) {
            if ($doctor->photo) {
                $previousImagePath = public_path($doctor->photo);
                if (File::exists($previousImagePath)) {
                    File::delete($previousImagePath);
                }
            }
            $path1 = $request->photo->store('images/doctors/profiles', 'public');
            $doctor->photo = '/storage/' . $path1;
        }

        if ($request->hasFile('sign')) {
            if ($doctor->sign) {
                $previousImagePath = public_path($doctor->sign);
                if (File::exists($previousImagePath)) {
                    File::delete($previousImagePath);
                }
            }
            $path2 = $request->sign->store('images/doctors/signs', 'public');
            $doctor->sign = '/storage/' . $path2;
        }


        if ($request->filled('password')) {
            if (! $request->filled('old_password')) {
                return response()->json(['message' => 'you have to enter old_password to change password']);
            }
            if (! Hash::check($request->old_password, $user->password)) {
                return response()->json(['message' => 'old password is wrong']);
            }
        }
        $user = $doctor->user()->first();
        $user->update($request->all());
        $user->save();
        $doctor->update($request->except(['photo', 'sign']));
        $doctor->save();
        return response()->json(['message' => 'Updated successfully'], 200);
    }
    /////
    public function schedule(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'RosterDays' => 'required|array|min:1',
            'RosterDays.*.day' => 'required|string',
            'RosterDays.*.Shift' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->all()], 422);
        }
        $doctor = Doctor::where('user_id', $user->id)->first();
        if (!$doctor) return response()->json(['message' => 'Doctor Not Found'], 404);
        Schedule::where('doctor_id', $doctor->id)->delete();
        foreach ($request->RosterDays as $RosterDay) {
            $day = $RosterDay['day'];
            $Shift = $RosterDay['Shift'];
            Schedule::create([
                'clinic_id' => $doctor->clinic_id,
                'doctor_id' => $doctor->id,
                'day' => $day,
                'Shift' => $Shift,
            ]);
        }
        $doctor->status = 'available';
        $doctor->save();
        return response()->json(['message' => 'Shifts processed successfully'], 201);
    }
    /////
    public function availableWorkDays()
    {
        if ($auth = $this->auth()) {
            return $auth;
        }

        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();

        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found.'], 404);
        }

        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Saturday'];
        $shifts = [
            'morning shift:from 9 AM to 3 PM',
            'evening shift:from 3 PM to 9 PM',
        ];

        $availableSchedule = [];

        foreach ($days as $day) {
            $availableShifts = [];

            foreach ($shifts as $shift) {
                $isTaken = Schedule::where('clinic_id', $doctor->clinic_id)
                    ->where('day', $day)
                    ->where('Shift', $shift)
                    ->where('doctor_id', '!=', $doctor->id)
                    ->exists();

                if (!$isTaken) {
                    $availableShifts[] = $shift;
                }
            }

            if (!empty($availableShifts)) {
                $availableSchedule[] = [
                    'day' => $day,
                    'available_shifts' => $availableShifts
                ];
            }
        }

        return response()->json($availableSchedule, 200);
    }

    public function showDoctorReviews()
    {

        if ($auth = $this->auth()) {
            return $auth;
        }
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();

        if (!$doctor) return response()->json(['message' => 'Doctor Not Found'], 404);

        $reviews = PatientReview::with(['review', 'patient'])->where('doctor_id', $doctor->id)->get();
        $response = [];

        foreach ($reviews as $patientReview) {
            if ($patientReview->review) {
                $response[] = [
                    'patient_id' => $patientReview->patient_id,
                    'patient first name' => $patientReview->patient->first_name,
                    'patient last name' => $patientReview->patient->last_name,
                    'rate' => $patientReview->review->rate,
                    'comment' => $patientReview->review->comment,
                ];
            }
        }


        return response()->json($response, 200);
    }
    /////
    public function auth()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => 'unauthorized'
            ], 401);
        }
        if ($user->role != 'doctor') {
            return response()->json([
                'message' => 'you dont have permission'
            ], 401);
        }
    }
}

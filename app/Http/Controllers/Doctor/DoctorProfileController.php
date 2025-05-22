<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Doctor;
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
        $doctor = Doctor::where('user_id', $user->id)->first();
        $clinic = Clinic::where('id', $doctor->clinic_id)->first();
        $workDays = Schedule::where('doctor_id', $doctor->id)->where('clinic_id', $clinic->id)->get()->all();
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
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => 'unauthorized'
            ], 401);
        }

        if ($user->role !== 'doctor') {
            return response()->json([
                'message' => 'you dont have permission'
            ], 401);
        }

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
        $doctor = Doctor::where('user_id', $user->id)->first();
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

        $doctor = Doctor::where('user_id', $user->id)->first();
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Saturday'];
        $schedule = [];
        foreach ($days as $day) {
            $availableShifts = [];
            $morningTakenByOther = Schedule::where('clinic_id', $doctor->clinic_id)
                ->where('day', $day)
                ->where('Shift', 'morning shift:from 9 AM to 3 PM')
                ->where('doctor_id', '!=', $doctor->id)
                ->exists();

            if (!$morningTakenByOther) {
                $availableShifts[] = 'morning shift:from 9 AM to 3 PM';
            }
            $eveningTakenByOther = Schedule::where('clinic_id', $doctor->clinic_id)
                ->where('day', $day)
                ->where('Shift', 'evening shift:from 3 PM to 9 PM')
                ->where('doctor_id', '!=', $doctor->id)
                ->exists();

            if (!$eveningTakenByOther) {
                $availableShifts[] = 'evening shift:from 3 PM to 9 PM';
            }

            if (!empty($availableShifts)) {
                $schedule[$day] = $availableShifts;
            }
        }

        return response()->json($schedule, 200);
    }
}

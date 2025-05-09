<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'checkup_duration' => $doctor->checkup_duration,
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

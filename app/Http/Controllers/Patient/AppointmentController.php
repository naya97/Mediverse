<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function showAppointment(Request $request)
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
        $appointments = Appointment::where('patient_id', $patient->id)->where('status', $request->status)->get();
        $doctorsIds = $appointments->pluck('doctor_id')->all();
        $doctors  = Doctor::whereIn('id', $doctorsIds)->get();
        $size = count($doctors);
        $response = [];
        for ($i = 0; $i < $size; $i++) {
            $response[] = [
                'doctor_photo' => $doctors[$i]->photo,
                'doctor_name' => $doctors[$i]->name,
                'doctor_speciality' => $doctors[$i]->speciality,
                'reservation_date' => $appointments[$i]->reservation_date,
                'reservation_hour' => $appointments[$i]->reservation_hour,
                'status' => $appointments[$i]->status,
            ];
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
        if ($user->role != 'patient') {
            return response()->json([
                'message' => 'you dont have permission'
            ], 401);
        }
        return $user;
    }
}

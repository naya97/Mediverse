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

        $appointments = Appointment::with('schedule.doctor')
            ->where('patient_id', $patient->id)
            ->where('status', $request->status)
            ->get();
    
        foreach ($appointments as $appointment) {
            $doctor = $appointment->schedule->doctor ?? null;

            if ($doctor) {
                $response[] = [
                    'doctor_photo' => $doctor->photo,
                    'doctor_name' => $doctor->first_name . ' ' . $doctor->last_name,
                    'doctor_speciality' => $doctor->speciality,
                    'reservation_date' => $appointment->reservation_date,
                    'reservation_hour' => $appointment->timeSelected,
                    'status' => $appointment->status,
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
        if ($user->role != 'patient') {
            return response()->json([
                'message' => 'you dont have permission'
            ], 401);
        }
        return $user;
    }
}

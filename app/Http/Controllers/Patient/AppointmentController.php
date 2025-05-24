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
                    'id' => $appointment->id,
                    'doctor_photo' => $doctor->photo,
                    'doctor_name' => $doctor->first_name . ' ' . $doctor->last_name,
                    'doctor_speciality' => $doctor->speciality,
                    'reservation_date' => $appointment->reservation_date,
                    'reservation_hour' => $appointment->timeSelected,
                ];
            }   
        }
        return response()->json($response, 200);
    }

    public function showAppointmentDetails(Request $request) {
       // don't forget the appointment details will be different if the patient has checkout or not
       // don't forget show the parent_id (first_time_visit, checout_visit)
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

        $appointment = Appointment::with('schedule.doctor')
            ->where('id', $request->appointment_id)
            ->where('patient_id', $patient->id)
            ->first();
        $doctor = $appointment->schedule->doctor;

        $response = [
            'id' => $doctor->id,
            'doctor_photo' => $doctor->photo,
            'doctor_name' => $doctor->first_name . ' ' . $doctor->last_name,
            'doctor_speciality' => $doctor->speciality,
            'visit_fee' => $doctor->visit_fee,
            'finalRate' => $doctor->finalRate,
            'status' => $appointment->status,
            'reservation_date' => $appointment->timeSelected,
            'reservation_hour' => $appointment->reservation_date,
        ];

        return response()->json($response, 200);

    }

}

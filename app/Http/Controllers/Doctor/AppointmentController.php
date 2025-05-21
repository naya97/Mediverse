<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function showAllAppointments()
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();
        $scheduleIds = Schedule::where('doctor_id', $doctor->id)->pluck('id')->toArray();
        $appointments = Appointment::whereIn('schedule_id', $scheduleIds)->get();
        $response = [];
        foreach ($appointments as $appointment) {
            $patient = Patient::find($appointment->patient_id);
            if ($appointment->parent_id == null) {
                $type = 'first time';
            } else {
                $type = 'check up';
            }
            $response[] = [
                'patient first name' => $patient->first_name,
                'patient last name' => $patient->last_name,
                'reservation date' => $appointment->reservation_date,
                'reservation hour' => $appointment->timeSelected,
                'status' => $appointment->status,
                'appointment type' => $type
            ];
        }
        return response()->json($response, 200);
    }
    /////
    public function showAppointmentsByStatus(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();
        $scheduleIds = Schedule::where('doctor_id', $doctor->id)->pluck('id')->toArray();
        if ($request->status != 'today') {
            $appointments = Appointment::whereIn('schedule_id', $scheduleIds)->where('status', $request->status)->get();
        } else {
            $today = now()->format('Y-m-d');
            $appointments = Appointment::whereIn('schedule_id', $scheduleIds)->where('reservation_date', $today)->get();
        }
        $response = [];
        foreach ($appointments as $appointment) {
            $patient = Patient::find($appointment->patient_id);
            if ($appointment->parent_id == null) {
                $type = 'first time';
            } else {
                $type = 'check up';
            }
            $response[] = [
                'patient first name' => $patient->first_name,
                'patient last name' => $patient->last_name,
                'reservation date' => $appointment->reservation_date,
                'reservation hour' => $appointment->timeSelected,
                'status' => $appointment->status,
                'appointment type' => $type
            ];
        }
        return response()->json($response, 200);
    }
    /////
    public function showAppointmentsByType(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();
        $scheduleIds = Schedule::where('doctor_id', $doctor->id)->pluck('id')->toArray();
        if ($request->type == 'first time') {
            if ($request->status != 'today') {
                $appointments = Appointment::whereIn('schedule_id', $scheduleIds)->where('status', $request->status)->where('parent_id', null)->get();
            } else {
                $today = now()->format('Y-m-d');
                $appointments = Appointment::whereIn('schedule_id', $scheduleIds)->where('reservation_date', $today)->where('parent_id', null)->get();
            }
        } else {
            if ($request->status != 'today') {
                $appointments = Appointment::whereIn('schedule_id', $scheduleIds)->where('status', $request->status)->whereNotNull('parent_id')->get();
            } else {
                $today = now()->format('Y-m-d');
                $appointments = Appointment::whereIn('schedule_id', $scheduleIds)->where('reservation_date', $today)->whereNotNull('parent_id')->get();
            }
        }
        $response = [];
        foreach ($appointments as $appointment) {
            $patient = Patient::find($appointment->patient_id);
            $response[] = [
                'patient first name' => $patient->first_name,
                'patient last name' => $patient->last_name,
                'reservation date' => $appointment->reservation_date,
                'reservation hour' => $appointment->timeSelected,
                'status' => $appointment->status,
            ];
        }
        return response()->json($response, 200);
    }
    /////
    public function showAppointmentDetails(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $appointment = Appointment::find($request->id);
        $patient = Patient::find($appointment->patient_id);
        if ($appointment->parent_id == null) {
            $type = 'first time';
        } else {
            $type = 'check up';
        }
        $response = [
            'patient first name' => $patient->first_name,
            'patient last name' => $patient->last_name,
            'reservation date' => $appointment->reservation_date,
            'reservation hour' => $appointment->timeSelected,
            'status' => $appointment->status,
            'appointment type' => $type
        ];

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

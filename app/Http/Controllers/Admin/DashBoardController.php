<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashBoardController extends Controller
{
    public function showAllAppointments() {
        
        $auth = $this->auth();
        if($auth) return $auth;

        $all_appointments = Appointment::all()->count();

        $appointments = Appointment::with('patient','schedule')->get();

        $response = [];
        foreach($appointments as $appointment) {
            $response [] = [
                'id' => $appointment->id,
                'patient' => $appointment->patient->first_name. ' '. $appointment->patient->last_name,
                'doctor' => $appointment->schedule->doctor->first_name. ' '.$appointment->schedule->doctor->last_name,
                'doctor_id' => $appointment->schedule->doctor->id,
                'doctor_photo' => $appointment->schedule->doctor->photo ,
                'visit_fee' => $appointment->schedule->doctor->visit_fee ,
                'reservation_date' => $appointment->reservation_date,
                'timeSelected' => $appointment->timeSelected,
                'status' => $appointment->status,
            ];
        };

        return response()->json([
            'appointments' => $response,
            'numOfAppointments' => $all_appointments
        ],200);
    }

    public function filteringAppointmentByDoctor(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $all_appointments = Appointment::whereHas('schedule', function($query) use ($request) {
            $query->where('doctor_id', $request->doctor_id);
        })->count();

        $appointments = Appointment::with('patient', 'schedule.doctor')
        ->whereHas('schedule', function($query) use ($request) {
            $query->where('doctor_id', $request->doctor_id);
        })->get();

        $response = [];
        foreach($appointments as $appointment) {
            $response [] = [
                'id' => $appointment->id,
                'patient' => $appointment->patient->first_name. ' '. $appointment->patient->last_name,
                'doctor_id' => $appointment->schedule->doctor->id,
                'doctor' => $appointment->schedule->doctor->first_name. ' '.$appointment->schedule->doctor->last_name,
                'doctor_photo' => $appointment->schedule->doctor->photo ,
                'visit_fee' => $appointment->schedule->doctor->visit_fee ,
                'reservation_date' => $appointment->reservation_date,
                'timeSelected' => $appointment->timeSelected,
                'status' => $appointment->status,
            ];
        };

        return response()->json([
            'appointments' => $response,
            'numOfAppointments' => $all_appointments
        ],200);
    }

    public function filteringAppointmentByStatus(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $all_appointments = Appointment::whereHas('schedule', function($query) use ($request) {
            $query->where('status', $request->status);
        })->count();

        $appointments = Appointment::with('patient', 'schedule.doctor')
        ->whereHas('schedule', function($query) use ($request) {
            $query->where('status', $request->status);
        })->get();

        $response = [];
        foreach($appointments as $appointment) {
            $response [] = [
                'id' => $appointment->id,
                'patient' => $appointment->patient->first_name. ' '. $appointment->patient->last_name,
                'doctor_id' => $appointment->schedule->doctor->id,
                'doctor' => $appointment->schedule->doctor->first_name. ' '.$appointment->schedule->doctor->last_name,
                'doctor_photo' => $appointment->schedule->doctor->photo ,
                'visit_fee' => $appointment->schedule->doctor->visit_fee ,
                'reservation_date' => $appointment->reservation_date,
                'timeSelected' => $appointment->timeSelected,
                'status' => $appointment->status,
            ];
        };

        return response()->json([
            'appointments' => $response,
            'numOfAppointments' => $all_appointments
        ],200);
    }

    public function showPaymentDetails() {
        $auth = $this->auth();
        if($auth) return $auth;

        $appointments = Appointment::where('status', 'visited')->get();

        $totalRevenue = $appointments->sum('price');
        $totalAppointments = $appointments->count();
        $averagePayment = $appointments->avg('price');

        return response()->json([
            'totalRevenue' => $totalRevenue,
            'totalAppointments' => $totalAppointments,
            'averagePayment' => $averagePayment,
        ], 200);
    }

    public function showPaymentDetailsByDoctor(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $appointments = Appointment::with(['schedule.doctor'])
        ->whereHas('schedule', function ($query) use ($request) {
           $query->where('doctor_id', $request->doctor_id);
        })
        ->get();

        $totalRevenue = $appointments->sum('price');
        $totalAppointments = $appointments->count();
        $averagePayment = $appointments->avg('price');

        return response()->json([
            'totalRevenue' => $totalRevenue,
            'totalAppointments' => $totalAppointments,
            'averagePayment' => $averagePayment,
        ], 200);
    }

    public function showPaymentDetailsByDate(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;


        $date = Carbon::createFromFormat('m-Y', $request->date); 
        $startOfMonth = $date->startOfMonth()->toDateString();
        $endOfMonth = $date->endOfMonth()->toDateString();

        $appointments = Appointment::where('status', 'visited')
            ->whereBetween('reservation_date',[$startOfMonth, $endOfMonth])
        ->get();

        $totalRevenue = $appointments->sum('price');
        $totalAppointments = $appointments->count();
        $averagePayment = $appointments->avg('price');

        return response()->json([
            'totalRevenue' => $totalRevenue,
            'totalAppointments' => $totalAppointments,
            'averagePayment' => $averagePayment,
        ], 200);

    }

    public function showPatients() {
        $auth = $this->auth();
        if($auth) return $auth;
        
        $patients = Patient::select('id', 'first_name', 'last_name', 'user_id', 'gender', 'age', 'address')
        ->get();

        return response()->json($patients, 200);

    }

    public function showPatientDetails(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $patient = Patient::where('id', $request->patient_id)
            ->select('id', 'first_name', 'last_name', 'user_id', 'gender', 'age', 'address')
        ->first();
        if(!$patient) return response()->json(['message' => 'patient not found'], 404);

        return response()->json($patient, 200);
    }

    public function deletePatient(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $patient = Patient::with('user')->find($request->patient_id);
        if(!$patient) return response()->json(['message' => 'patient not found'], 404);

        $patient->user->delete();
        $patient->delete();

        return response()->json(['message' => 'patient removed successfully'], 200);
        
    }

    public function auth() {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => 'unauthorized'
            ], 401);
        }
        if ($user->role != 'admin') {
            return response()->json('You do not have permission in this page', 400);
        }
    }
}

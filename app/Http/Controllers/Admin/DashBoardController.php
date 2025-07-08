<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\PaginationTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashBoardController extends Controller
{
    use PaginationTrait;

    public function showAllAppointments(Request $request) {
        
        $auth = $this->auth();
        if($auth) return $auth;

        $appointments = Appointment::with('schedule.doctor', 'patient');

        $response = $this->paginateResponse($request, $appointments, 'Appointments', function ($appointment) {
            return [
                'id' => $appointment->id,
                'patient' => $appointment->patient->first_name . ' ' . $appointment->patient->last_name,
                'doctor' => $appointment->schedule->doctor->first_name . ' ' . $appointment->schedule->doctor->last_name,
                'doctor_id' => $appointment->schedule->doctor->id,
                'doctor_photo' => $appointment->schedule->doctor->photo,
                'visit_fee' => $appointment->schedule->doctor->visit_fee,
                'reservation_date' => $appointment->reservation_date,
                'timeSelected' => $appointment->timeSelected,
                'status' => $appointment->status,
            ];
        });

         return response()->json($response, 200);
    }

    public function filteringAppointmentByDoctor(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $appointments = Appointment::with('patient', 'schedule.doctor')
        ->whereHas('schedule', function($query) use ($request) {
            $query->where('doctor_id', $request->doctor_id);
        });

        $response = $this->paginateResponse($request, $appointments, 'Appointments', function ($appointment) {
            return [
                'id' => $appointment->id,
                'patient' => $appointment->patient->first_name . ' ' . $appointment->patient->last_name,
                'doctor_id' => $appointment->schedule->doctor->id,
                'doctor' => $appointment->schedule->doctor->first_name . ' ' . $appointment->schedule->doctor->last_name,
                'doctor_photo' => $appointment->schedule->doctor->photo,
                'visit_fee' => $appointment->schedule->doctor->visit_fee,
                'reservation_date' => $appointment->reservation_date,
                'timeSelected' => $appointment->timeSelected,
                'status' => $appointment->status,
            ];
        });

         return response()->json($response, 200);
    }

    public function filteringAppointmentByStatus(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

         $appointments = Appointment::with('patient', 'schedule.doctor')
        ->where('status', $request->status);

        

        $response = $this->paginateResponse($request, $appointments, 'Appointments', function ($appointment) {
            return [
                'id' => $appointment->id,
                'patient' => $appointment->patient->first_name . ' ' . $appointment->patient->last_name,
                'doctor_id' => $appointment->schedule->doctor->id,
                'doctor' => $appointment->schedule->doctor->first_name . ' ' . $appointment->schedule->doctor->last_name,
                'doctor_photo' => $appointment->schedule->doctor->photo,
                'visit_fee' => $appointment->schedule->doctor->visit_fee,
                'reservation_date' => $appointment->reservation_date,
                'timeSelected' => $appointment->timeSelected,
                'status' => $appointment->status,
            ];
        });
    

         return response()->json($response, 200);
    }

    public function showPaymentDetails() {
        $auth = $this->auth();
        if($auth) return $auth;

        $appointments = Appointment::where('payment_status', 'paid')->get();

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

        $appointments = Appointment::where('payment_status', 'paid')
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

    public function showPatients(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;
        
        $patients = Patient::select('id', 'first_name', 'last_name', 'user_id', 'gender', 'age', 'address');

        $query = $this->paginateResponse($request, $patients, 'Patients');

        return response()->json($query, 200);

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

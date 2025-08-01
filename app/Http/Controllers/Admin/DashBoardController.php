<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\Schedule;
use App\PaginationTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
                'appointment_type' => $appointment->appointment_type,
            ];
        });

         return response()->json($response, 200);
    }

    public function filteringAppointmentByDoctor(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $validator = Validator::make($request->all(), [
            'date' => ['required', 'date_format:m-Y'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }

        $date = Carbon::createFromFormat('m-Y', $request->date); 
        $startOfMonth = $date->startOfMonth()->toDateString();
        $endOfMonth = $date->endOfMonth()->toDateString();

        $appointments = Appointment::with('patient', 'schedule.doctor')
        ->whereBetween('reservation_date', [$startOfMonth, $endOfMonth])
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
                'appointment_type' => $appointment->appointment_type,
            ];
        });

         return response()->json($response, 200);
    }

    public function filteringAppointmentByStatus(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $validator = Validator::make($request->all(), [
            'date' => ['required', 'date_format:m-Y'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }

        $date = Carbon::createFromFormat('m-Y', $request->date); 
        $startOfMonth = $date->startOfMonth()->toDateString();
        $endOfMonth = $date->endOfMonth()->toDateString();

        $appointments = Appointment::with('patient', 'schedule.doctor')
        ->where('status', $request->status)
        ->whereBetween('reservation_date', [$startOfMonth, $endOfMonth]);

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
                'appointment_type' => $appointment->appointment_type,
            ];
        });
    

        return response()->json($response, 200);
    }

    public function filteringAppointmentByDoctorStatus(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $validator = Validator::make($request->all(), [
            'date' => ['required', 'date_format:m-Y'],
            'doctor_id' => 'required|exists:doctors',
            'status' => ['required', Rule::in('pending', 'visited', 'cancelled')]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }

        $date = Carbon::createFromFormat('m-Y', $request->date); 
        $startOfMonth = $date->startOfMonth()->toDateString();
        $endOfMonth = $date->endOfMonth()->toDateString();

        $appointments = Appointment::with('patient', 'schedule.doctor')
        ->where('status', $request->status)
        ->whereBetween('reservation_date', [$startOfMonth, $endOfMonth])
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
                'appointment_type' => $appointment->appointment_type,
            ];
        });
    

        return response()->json($response, 200);
    }

    public function filteringAppointmentsByDate(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $validator = Validator::make($request->all(), [
            'date' => ['required', 'date_format:m-Y'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }

        $date = Carbon::createFromFormat('m-Y', $request->date); 
        $startOfMonth = $date->startOfMonth()->toDateString();
        $endOfMonth = $date->endOfMonth()->toDateString();

        $appointments = Appointment::whereBetween('reservation_date',[$startOfMonth, $endOfMonth])
        ->orderBy('reservation_date', 'asc')
        ->get();
        $response = [];

        foreach($appointments as $appointment) {
            $response [] = [
                'id' => $appointment->id,
                'patient' => $appointment->patient->first_name . ' ' . $appointment->patient->last_name,
                'doctor_id' => $appointment->schedule->doctor->id,
                'doctor' => $appointment->schedule->doctor->first_name . ' ' . $appointment->schedule->doctor->last_name,
                'doctor_photo' => $appointment->schedule->doctor->photo,
                'visit_fee' => $appointment->schedule->doctor->visit_fee,
                'reservation_date' => $appointment->reservation_date,
                'timeSelected' => $appointment->timeSelected,
                'status' => $appointment->status,
                'appointment_type' => $appointment->appointment_type,
            ];
        }

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

        $validator = Validator::make($request->all(), [
            'date' => ['required', 'date_format:m-Y'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }

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

    public function showAllPayments() {
        $auth = $this->auth();
        if($auth) return $auth;

        $monthlyPaymentInfo = Appointment::select(
            DB::raw('YEAR(reservation_date) as year'),
            DB::raw('MONTH(reservation_date) as month'),
            DB::raw('SUM(price) as totalRevenue'),
            DB::raw('COUNT(*) as totalAppointments'),
            DB::raw('AVG(price) as averagePayment'),
        )
        ->where('payment_status', 'paid')
        ->groupBy(DB::raw('YEAR(reservation_date)'), DB::raw('MONTH(reservation_date)'))
        ->orderBy('year')
        ->orderBy('month')
        ->get();

        return response()->json([
        'monthlyPaymentInfo' => $monthlyPaymentInfo
        ], 200);
    }

    public function showPatients(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;
        
        $patients = Patient::select('id', 'first_name', 'last_name', 'user_id', 'gender', 'birth_date', 'address');

        $query = $this->paginateResponse($request, $patients, 'Patients');

        return response()->json($query, 200);

    }

    public function showDoctorPatients(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $scheduleIds = Schedule::where('doctor_id', $request->doctor_id)->pluck('id');
        $appointments = Appointment::with('patient')->whereIn('schedule_id', $scheduleIds)->get();
        $patientIds = $appointments->pluck('patient_id');
        $patients = Patient::with('user')->whereIn('id', $patientIds);

        $response = $this->paginateResponse($request, $patients, 'Patients', function ($patient) {
            return [
                'id' => $patient->id,
                'patient' => $patient->first_name . ' ' . $patient->last_name,
                'user_id' => $patient->user_id,
                'email' => $patient->user ?->email ? : null,
                'phone' => $patient->user ?->phone ? : null,
                'birth_date' => $patient->birth_date,
                'gender' => $patient->gender,
                'blood_type' => $patient->blood_type,
                'address' => $patient->address,
            ];
        });

        return response()->json($response, 200);
        
    }

    public function showPatientDetails(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $patient = Patient::where('id', $request->patient_id)
            ->select('id', 'first_name', 'last_name', 'user_id', 'gender', 'birth_date', 'address')
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

<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Refund;
use Stripe\Stripe;
use App\CancelAppointmentsTrait;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\PaginationTrait;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    use CancelAppointmentsTrait;
    use PaginationTrait;

    public function filteringAppointmentByDoctor(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required|exists:doctors,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }

        if($request->has('date')) {
            $date = Carbon::createFromFormat('m-Y', $request->date); 
            $startOfMonth = $date->startOfMonth()->toDateString();
            $endOfMonth = $date->endOfMonth()->toDateString();

            $appointments = Appointment::with('patient.user', 'schedule.doctor')
            ->whereBetween('reservation_date', [$startOfMonth, $endOfMonth])
            ->whereHas('schedule', function($query) use ($request) {
                $query->where('doctor_id', $request->doctor_id);
            })->get();
        }
        else {
            $appointments = Appointment::with('patient.user', 'schedule.doctor')
            ->whereHas('schedule', function($query) use ($request) {
                $query->where('doctor_id', $request->doctor_id);
            })->get();
        }

        $response = [];

        foreach($appointments as $appointment){
            $patient = $appointment->patient;
            $doctor = $appointment->schedule->doctor;
            $patientUser = $appointment->patient->user;
            $doctorUser = $appointment->schedule->doctor->user;

            $response [] = [
                'id' => $appointment->id,
                'patient' => $patient->first_name . ' ' . $patient->last_name,
                'patient_phone' => $patientUser ? $patientUser->phone : null,
                'doctor' => $doctor->first_name . ' ' . $doctor->last_name,
                'doctor_id' => $doctor->id,
                'doctor_phone' => $doctorUser ? $doctorUser->phone : null,
                'doctor_photo' => $doctor->photo,
                'visit_fee' => $doctor->visit_fee,
                'expected_price' => $appointment->expected_price,
                'paid_price' => $appointment->paid_price,
                'reservation_date' => $appointment->reservation_date,
                'payment_status' => $appointment->payment_status,
                'timeSelected' => $appointment->timeSelected,
                'status' => $appointment->status,
                'queue_number' => $appointment->queue_number,
            ];
        }


        return response()->json($response, 200);
    }

    public function filteringAppointmentByStatus(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $validator = Validator::make($request->all(), [
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }

        if($request->status == 'today') {
            $today = now()->format('Y-m-d');
            $appointments = Appointment::with('patient.user', 'schedule.doctor.user')
            ->where('status', $request->status)
            ->where('reservation_date', $today)
            ->get();
        }
        else {
            if($request->has('date')) {
                $date = Carbon::createFromFormat('m-Y', $request->date); 
                $startOfMonth = $date->startOfMonth()->toDateString();
                $endOfMonth = $date->endOfMonth()->toDateString();

                $appointments = Appointment::with('patient.user', 'schedule.doctor.user')
                ->where('status', $request->status)
                ->whereBetween('reservation_date', [$startOfMonth, $endOfMonth])
                ->get();
            }
            else {
                $appointments = Appointment::with('patient.user', 'schedule.doctor.user')
                ->where('status', $request->status)
                ->get();
            }
        }

        $response = [];
        foreach($appointments as $appointment){
            $patient = $appointment->patient;
            $doctor = $appointment->schedule->doctor;
            $patientUser = $appointment->patient->user;
            $doctorUser = $appointment->schedule->doctor->user;

            $response [] = [
                'id' => $appointment->id,
                'patient' => $patient->first_name . ' ' . $patient->last_name,
                'patient_phone' => $patientUser ? $patientUser->phone : null,
                'doctor' => $doctor->first_name . ' ' . $doctor->last_name,
                'doctor_id' => $doctor->id,
                'doctor_phone' => $doctorUser ? $doctorUser->phone : null,
                'doctor_photo' => $doctor->photo,
                'visit_fee' => $doctor->visit_fee,
                'expected_price' => $appointment->expected_price,
                'paid_price' => $appointment->paid_price,
                'reservation_date' => $appointment->reservation_date,
                'payment_status' => $appointment->payment_status,
                'timeSelected' => $appointment->timeSelected,
                'status' => $appointment->status,
                'queue_number' => $appointment->queue_number,
            ];
        }

        

        return response()->json($response, 200);
    }

    public function filteringAppointmentByDoctorStatus(Request $request) {
        $auth = $this->auth();
        if ($auth) return $auth;

        $validator = Validator::make($request->all(), [
            'status' => 'required',
            'doctor_id' => 'required|exists:doctors,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }

        if($request->status == 'today') {
            $today = now()->format('Y-m-d');
            $appointments = Appointment::with('patient.user', 'schedule.doctor.user')
            ->where('status', $request->status)
            ->whereHas('schedule', function($query) use ($request) {
            $query->where('doctor_id', $request->doctor_id);
            })
            ->where('reservation_date', $today)
            ->get();
        }
        else {
            if($request->has('date')) {
                $date = Carbon::createFromFormat('m-Y', $request->date); 
                $startOfMonth = $date->startOfMonth()->toDateString();
                $endOfMonth = $date->endOfMonth()->toDateString();

                $appointments = Appointment::with('patient.user', 'schedule.doctor.user')
                ->where('status', $request->status)
                ->whereBetween('reservation_date', [$startOfMonth, $endOfMonth])
                ->whereHas('schedule', function($query) use ($request) {
                    $query->where('doctor_id', $request->doctor_id);
                })
                ->get();
            }
            else {
                $appointments = Appointment::with('patient.user', 'schedule.doctor.user')
                ->where('status', $request->status)
                ->whereHas('schedule', function($query) use ($request) {
                    $query->where('doctor_id', $request->doctor_id);
                })
                ->get();
            }
        }

        $response = [];
        foreach($appointments as $appointment){
            $patient = $appointment->patient;
            $doctor = $appointment->schedule->doctor;
            $patientUser = $appointment->patient->user;
            $doctorUser = $appointment->schedule->doctor->user;

            $response [] = [
                'id' => $appointment->id,
                'patient' => $patient->first_name . ' ' . $patient->last_name,
                'patient_phone' => $patientUser ? $patientUser->phone : null,
                'doctor' => $doctor->first_name . ' ' . $doctor->last_name,
                'doctor_id' => $doctor->id,
                'doctor_phone' => $doctorUser ? $doctorUser->phone : null,
                'doctor_photo' => $doctor->photo,
                'visit_fee' => $doctor->visit_fee,
                'expected_price' => $appointment->expected_price,
                'paid_price' => $appointment->paid_price,
                'reservation_date' => $appointment->reservation_date,
                'payment_status' => $appointment->payment_status,
                'timeSelected' => $appointment->timeSelected,
                'status' => $appointment->status,
                'queue_number' => $appointment->queue_number,
            ];
        }

        

        return response()->json($response, 200);
    }


    public function filteringAppointmentByDate(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

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

        foreach($appointments as $appointment){
            $patient = $appointment->patient;
            $doctor = $appointment->schedule->doctor;
            $patientUser = $appointment->patient->user;
            $doctorUser = $appointment->schedule->doctor->user;

            $response [] = [
                'id' => $appointment->id,
                'patient' => $patient->first_name . ' ' . $patient->last_name,
                'patient_phone' => $patientUser ? $patientUser->phone : null,
                'doctor' => $doctor->first_name . ' ' . $doctor->last_name,
                'doctor_id' => $doctor->id,
                'doctor_phone' => $doctorUser ? $doctorUser->phone : null,
                'doctor_photo' => $doctor->photo,
                'visit_fee' => $doctor->visit_fee,
                'expected_price' => $appointment->expected_price,
                'paid_price' => $appointment->paid_price,
                'reservation_date' => $appointment->reservation_date,
                'payment_status' => $appointment->payment_status,
                'timeSelected' => $appointment->timeSelected,
                'status' => $appointment->status,
                'queue_number' => $appointment->queue_number,
            ];
        }

        return response()->json($response, 200);
    }

    public function filteringAppointmentByClinic(Request $request) {
        $auth = $this->auth();
        if ($auth) return $auth;

        $validator = Validator::make($request->all(), [
            'clinic_id' => ['required', 'exists:clinics,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }

        if($request->has('status')) {
            if($request->status == 'today') {
                $today = now()->format('Y-m-d');
                $appointments = Appointment::with('patient.user', 'schedule.doctor.user', 'schedule.clinic')
                    ->where('status', $request->status)
                    ->where('reservation_date', $today)
                    ->whereHas('schedule', function ($query) use ($request) {
                        $query->where('clinic_id', $request->clinic_id);
                    })
                ->get();
            }
            else {
                $appointments = Appointment::with('patient.user', 'schedule.doctor.user', 'schedule.doctor.clinic')
                    ->where('status', $request->status)
                    ->whereHas('schedule.doctor', function ($query) use ($request) {
                        $query->where('clinic_id', $request->clinic_id);
                    })
                ->get();
            }
        }
        else {
            if($request->has('date')) {
                $date = Carbon::createFromFormat('m-Y', $request->date);
                $startOfMonth = $date->startOfMonth()->toDateString();
                $endOfMonth = $date->endOfMonth()->toDateString();

                $appointments = Appointment::with('patient.user', 'schedule.doctor.user', 'schedule.doctor.clinic')
                    ->whereBetween('reservation_date', [$startOfMonth, $endOfMonth])
                    ->whereHas('schedule.doctor', function ($query) use ($request) {
                        $query->where('clinic_id', $request->clinic_id);
                    })
                ->get();
            }
            else {
                $appointments = Appointment::with('patient.user', 'schedule.doctor.user', 'schedule.doctor.clinic')
                ->whereHas('schedule.doctor', function ($query) use ($request) {
                    $query->where('clinic_id', $request->clinic_id);
                })
                ->get();
            }
        }

        $response = [];
        foreach ($appointments as $appointment) {
            $patient = $appointment->patient;
            $doctor = $appointment->schedule->doctor;
            $patientUser = $patient->user;
            $doctorUser = $doctor->user;

            $response[] = [
                'id' => $appointment->id,
                'patient' => $patient->first_name . ' ' . $patient->last_name,
                'patient_phone' => $patientUser ? $patientUser->phone : null,
                'doctor' => $doctor->first_name . ' ' . $doctor->last_name,
                'doctor_id' => $doctor->id,
                'doctor_phone' => $doctorUser ? $doctorUser->phone : null,
                'doctor_photo' => $doctor->photo,
                'visit_fee' => $doctor->visit_fee,
                'expected_price' => $appointment->expected_price,
                'paid_price' => $appointment->paid_price,
                'reservation_date' => $appointment->reservation_date,
                'payment_status' => $appointment->payment_status,
                'timeSelected' => $appointment->timeSelected,
                'status' => $appointment->status,
                'queue_number' => $appointment->queue_number,
            ];
        }

        return response()->json($response, 200);

    }

    public function showAppointmentDetails(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $validator = Validator::make($request->all(), [
            'appointment_id' => 'required|exists:appointments,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->all()
            ], 400);
        }

        $appointment = Appointment::with('patient')->find($request->appointment_id);
        if ($appointment->parent_id == null) {
            $type = 'first time';
        } else {
            $type = 'check up';
        }
        $response = [
            'patient_id ' => $appointment->patient->id, //it is for showing patient analysis and appointments and add checkup
            'patient_first_name' => $appointment->patient->first_name,
            'patient_last_name' => $appointment->patient->last_name,
            'reservation_date' => $appointment->reservation_date,
            'reservation_hour' => $appointment->timeSelected,
            'status' => $appointment->status,
            'appointment_type' => $type,
            'payment_status' => $appointment->payment_status,
            'discount_points' => $appointment->patient->discount_points,
            'queue_number' => $appointment->queue_number,

        ];

        return response()->json($response, 200);
    }

    public function editSchedule(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        return $this->editDoctorSchedule($request, $request->doctor_id);
    }

    public function cancelAppointment(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        return $this->cancelAnAppointment($request);
    }


    public function auth()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => 'unauthorized'
            ], 401);
        }
        if ($user->role != 'secretary') {
            return response()->json('You do not have permission in this page', 400);
        }
    }
}

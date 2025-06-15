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

class AppointmentController extends Controller
{
    use CancelAppointmentsTrait;
    public function showAllAppointments()
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $all_appointments = Appointment::all()->count();

        $appointments = Appointment::with('patient', 'schedule')->get();

        $response = [];
        foreach ($appointments as $appointment) {
            $response[] = [
                'id' => $appointment->id,
                'patient' => $appointment->patient->first_name . ' ' . $appointment->patient->last_name,
                'patient_phone' => $appointment->patient->user->phone,
                'doctor' => $appointment->schedule->doctor->first_name . ' ' . $appointment->schedule->doctor->last_name,
                'doctor_id' => $appointment->schedule->doctor->id,
                'doctor_phone' => $appointment->schedule->doctor->user->phone,
                'doctor_photo' => $appointment->schedule->doctor->photo,
                'visit_fee' => $appointment->schedule->doctor->visit_fee,
                'price' => $appointment->price,
                'reservation_date' => $appointment->reservation_date,
                'payment_status' => $appointment->payment_status,
                'timeSelected' => $appointment->timeSelected,
                'status' => $appointment->status,
            ];
        };

        return response()->json([
            'appointments' => $response,
            'numOfAppointments' => $all_appointments
        ], 200);
    }

    public function filteringAppointmentByDoctor(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $all_appointments = Appointment::whereHas('schedule', function ($query) use ($request) {
            $query->where('doctor_id', $request->doctor_id);
        })->count();

        $appointments = Appointment::with('patient', 'schedule.doctor')
            ->whereHas('schedule', function ($query) use ($request) {
                $query->where('doctor_id', $request->doctor_id);
            })->get();

        $response = [];
        foreach ($appointments as $appointment) {
            $response[] = [
                'id' => $appointment->id,
                'patient' => $appointment->patient->first_name . ' ' . $appointment->patient->last_name,
                'patient_phone' => $appointment->patient->user->phone,
                'doctor' => $appointment->schedule->doctor->first_name . ' ' . $appointment->schedule->doctor->last_name,
                'doctor_id' => $appointment->schedule->doctor->id,
                'doctor_phone' => $appointment->schedule->doctor->user->phone,
                'doctor_photo' => $appointment->schedule->doctor->photo,
                'visit_fee' => $appointment->schedule->doctor->visit_fee,
                'price' => $appointment->price,
                'reservation_date' => $appointment->reservation_date,
                'payment_status' => $appointment->payment_status,
                'timeSelected' => $appointment->timeSelected,
                'status' => $appointment->status,
            ];
        };

        return response()->json([
            'appointments' => $response,
            'numOfAppointments' => $all_appointments
        ], 200);
    }

    public function filteringAppointmentByStatus(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $appointments = Appointment::whereHas('schedule', function ($query) use ($request) {
            $query->where('doctor_id', $request->doctor_id);
        })->where('status', $request->status)
            ->get();


        $response = [];
        foreach ($appointments as $appointment) {
            $response[] = [
                'id' => $appointment->id,
                'patient' => $appointment->patient->first_name . ' ' . $appointment->patient->last_name,
                'patient_phone' => $appointment->patient->user->phone,
                'doctor' => $appointment->schedule->doctor->first_name . ' ' . $appointment->schedule->doctor->last_name,
                'doctor_id' => $appointment->schedule->doctor->id,
                'doctor_phone' => $appointment->schedule->doctor->user->phone,
                'doctor_photo' => $appointment->schedule->doctor->photo,
                'visit_fee' => $appointment->schedule->doctor->visit_fee,
                'price' => $appointment->price,
                'reservation_date' => $appointment->reservation_date,
                'payment_status' => $appointment->payment_status,
                'timeSelected' => $appointment->timeSelected,
                'status' => $appointment->status,
            ];
        };

        $all_appointments = $appointments->count();

        return response()->json([
            'appointments' => $response,
            'numOfAppointments' => $all_appointments
        ], 200);
    }

    public function filteringAppointmentByDate(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $date = Carbon::createFromFormat('d-m-Y', $request->date)->toDateString();
        $appointments = Appointment::where('reservation_date', $date)->get();

        $response = [];
        foreach ($appointments as $appointment) {
            $response[] = [
                'id' => $appointment->id,
                'patient' => $appointment->patient->first_name . ' ' . $appointment->patient->last_name,
                'patient_phone' => $appointment->patient->user->phone,
                'doctor' => $appointment->schedule->doctor->first_name . ' ' . $appointment->schedule->doctor->last_name,
                'doctor_id' => $appointment->schedule->doctor->id,
                'doctor_phone' => $appointment->schedule->doctor->user->phone,
                'doctor_photo' => $appointment->schedule->doctor->photo,
                'visit_fee' => $appointment->schedule->doctor->visit_fee,
                'price' => $appointment->price,
                'reservation_date' => $appointment->reservation_date,
                'payment_status' => $appointment->payment_status,
                'timeSelected' => $appointment->timeSelected,
                'status' => $appointment->status,
            ];
        };

        $all_appointments = $appointments->count();

        return response()->json([
            'appointments' => $response,
            'numOfAppointments' => $all_appointments
        ], 200);
    }

    public function filteringAppointmentByMonth(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $date = Carbon::createFromFormat('m-Y', $request->date);
        $startOfMonth = $date->startOfMonth()->toDateString();
        $endOfMonth = $date->endOfMonth()->toDateString();

        $appointments = Appointment::whereBetween('reservation_date', [$startOfMonth, $endOfMonth])
            ->get();

        $response = [];
        foreach ($appointments as $appointment) {
            $response[] = [
                'id' => $appointment->id,
                'patient' => $appointment->patient->first_name . ' ' . $appointment->patient->last_name,
                'patient_phone' => $appointment->patient->user->phone,
                'doctor' => $appointment->schedule->doctor->first_name . ' ' . $appointment->schedule->doctor->last_name,
                'doctor_id' => $appointment->schedule->doctor->id,
                'doctor_phone' => $appointment->schedule->doctor->user->phone,
                'doctor_photo' => $appointment->schedule->doctor->photo,
                'visit_fee' => $appointment->schedule->doctor->visit_fee,
                'price' => $appointment->price,
                'reservation_date' => $appointment->reservation_date,
                'payment_status' => $appointment->payment_status,
                'timeSelected' => $appointment->timeSelected,
                'status' => $appointment->status,
            ];
        };

        $all_appointments = $appointments->count();

        return response()->json([
            'appointments' => $response,
            'numOfAppointments' => $all_appointments
        ], 200);
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

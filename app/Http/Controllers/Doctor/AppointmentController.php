<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalInfo;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Support\Facades\Validator;

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
                'id' => $appointment->id,
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
                'id' => $appointment->id,
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
                'id' => $appointment->id,
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
    public function showpatientAppointments(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $appointments = Appointment::where('patient_id', $request->patient_id)->get()->all();
        $response = [];
        foreach ($appointments as $appointment) {
            $appointment = Appointment::find($appointment->id);
            if ($appointment->parent_id == null) {
                $type = 'first time';
            } else {
                $type = 'check up';
            }
            $response[] = [
                'id' => $appointment->id,
                'reservation date' => $appointment->reservation_date,
                'reservation hour' => $appointment->timeSelected,
                'status' => $appointment->status,
                'appointment type' => $type
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
            'patient id ' => $patient->id, //it is for showing patient analysis and appointments and add checkup
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
    public function showAppointmantResults(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $appointment = Appointment::find($request->appointment_id);
        $medicalInfo = MedicalInfo::where('appointment_id', $appointment->id)->first();
        $prescription = Prescription::find($medicalInfo->prescription_id);
        if ($prescription) {
            $medicines = Medicine::where('prescription_id', $prescription->id)->get()->all();
            $prescription = [
                'medicines' => $medicines,
                'note' => $prescription->note,
            ];
        } else {
            $prescription = null;
        }
        $medicalInfo = [
            'symptoms' => $medicalInfo->symptoms,
            'diagnosis' => $medicalInfo->diagnosis,
            'note for the doctor' => $medicalInfo->doctorNote,
            'note for the patient' => $medicalInfo->patientNote
        ];
        return response()->json([
            'medicalInfo' => $medicalInfo,
            'prescription' => $prescription
        ], 200);
    }
    /////
    public function showDoctorWorkDays()
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();
        $schedule[] = Schedule::where('doctor_id', $doctor->id)->get();
        return response()->json($schedule, 200);
    }
    /////
    public function showTimes(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $validator = Validator::make($request->all(), [
            'date' => 'required|date_format:d/m/y',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }

        $date = Carbon::createFromFormat('d/m/y', $request->date);
        $day = $date->format('l');

        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();
        $schedule = Schedule::where('doctor_id', $doctor->id)->where('day', $day)->first();

        $mysqlDate = Carbon::createFromFormat('d/m/y', $request->date)->format('Y-m-d');

        $appointments = Appointment::where('schedule_id', $schedule->id)
            ->where('reservation_date', $mysqlDate)
            ->get();

        $visitTime = Doctor::where('id', $doctor->id)->select('average_visit_duration')->first()->average_visit_duration;
        $visitTime = (float) $visitTime;
        $numOfPeopleInHour = floor(60 / $visitTime);

        // filter the times 
        $available_times = [];

        if ($schedule->Shift == 'morning shift:from 9 AM to 3 PM') {
            $start = new DateTime('09:00 AM');
            $end = new DateTime('03:00 PM');
        } else {
            $start = new DateTime('03:00 PM');
            $end = new DateTime('09:00 PM');
        }

        $interval = new DateInterval('PT1H');
        $period = new DatePeriod($start, $interval, $end);

        foreach ($period as $time) {

            $timeFormatted = $time->format('h:i:s');
            $count = $appointments->where('timeSelected', $timeFormatted)->count();
            if ($count < $numOfPeopleInHour) {
                $available_times[] = $time->format('h:i A');
            }
        }

        return response()->json($available_times, 200);
    }
    /////
    public function addCheckup(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();
        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'date' => 'required|date_format:d/m/y',
            'time' => 'required|date_format:h:i A',
            'this_appointment_id' => 'required|exists:appointments,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }
        $patient = Patient::find($request->patient_id);

        $dateFormatted = Carbon::createFromFormat('d/m/y', $request->date)->format('Y-m-d');
        $timeFormatted = Carbon::parse($request->time)->format('h:i:s');

        $date = Carbon::createFromFormat('d/m/y', $request->date);
        $day = $date->format('l');

        $schedule = Schedule::where('doctor_id', $doctor->id)
            ->where('day', $day)
            ->first();

        $appointmentsNum = Appointment::where('schedule_id', $schedule->id)
            ->where('reservation_date', $dateFormatted)
            ->where('status', 'pending')
            ->where('timeSelected', $timeFormatted)
            ->count();

        $visitTime = Doctor::where('id', $doctor->id)->select('average_visit_duration')->first()->average_visit_duration;
        $visitTime = (float) $visitTime;

        if ($visitTime == 0 || $doctor->status == 'notAvailable') {
            return response()->json('this doctor not available', 503);
        }

        $numOfPeopleInHour = floor(60 / $visitTime);

        $newTimeFormatted = Carbon::parse($request->time);
        if ($appointmentsNum == $numOfPeopleInHour) $timeSelected = $newTimeFormatted->addHours(1)->toTimeString();
        else $timeSelected = $timeFormatted;

        if ($appointmentsNum < $numOfPeopleInHour) {
            $appointment = Appointment::create([
                'patient_id' => $patient->id,
                'schedule_id' => $schedule->id,
                'timeSelected' => $timeSelected,
                'reservation_date' => $dateFormatted,
                'parent_id' => $request->this_appointment_id,
            ]);

            return response()->json($appointment, 200);
        }

        return response()->json('this time is full', 400);
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

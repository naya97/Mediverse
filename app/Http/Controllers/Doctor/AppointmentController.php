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
use Carbon\CarbonPeriod;
use DateInterval;
use DatePeriod;
use DateTime;
use App\CancelAppointmentsTrait;
use App\PaginationTrait;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    use CancelAppointmentsTrait;
    use PaginationTrait;

    public function showAllAppointments(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $user = Auth::user();

        $doctor = Doctor::where('user_id', $user->id)->first();
        if (!$doctor) return response()->json(['message' => 'Doctor Not Found'], 404);

        $scheduleIds = Schedule::where('doctor_id', $doctor->id)->pluck('id')->toArray();

        $appointments = Appointment::with('patient')->whereIn('schedule_id', $scheduleIds);

        $response = $this->paginateResponse($request, $appointments, 'Appointments', function ($appointment) {
        $type = $appointment->parent_id === null ? 'first time' : 'check up';

            return [
                'id' => $appointment->id,
                'patient_first_name' => $appointment->patient->first_name,
                'patient_last_name' => $appointment->patient->last_name,
                'reservation_date' => $appointment->reservation_date,
                'reservation_hour' => $appointment->timeSelected,
                'status' => $appointment->status,
                'appointment_type' => $type,
                'payment_status' => $appointment->payment_status,
            ];
        });

        return response()->json($response, 200);
    }
    /////
    public function showAppointmentsByStatus(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $validator = Validator::make($request->all(), [
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }

        $user = Auth::user();

        $doctor = Doctor::where('user_id', $user->id)->first();
        if (!$doctor) return response()->json(['message' => 'Doctor Not Found'], 404);

        $scheduleIds = Schedule::where('doctor_id', $doctor->id)->pluck('id')->toArray();

        if ($request->status != 'today') {
            $appointments = Appointment::with('patient')->whereIn('schedule_id', $scheduleIds)->where('status', $request->status);
        } else {
            $today = now()->format('Y-m-d');
            $appointments = Appointment::with('patient')->whereIn('schedule_id', $scheduleIds)->where('reservation_date', $today);
        }

        $response = $this->paginateResponse($request, $appointments, 'Appointments', function ($appointment) {
        $type = $appointment->parent_id === null ? 'first time' : 'check up';

            return [
                'id' => $appointment->id,
                'patient_first_name' => $appointment->patient->first_name,
                'patient_last_name' => $appointment->patient->last_name,
                'reservation_date' => $appointment->reservation_date,
                'reservation_hour' => $appointment->timeSelected,
                'status' => $appointment->status,
                'appointment_type' => $type,
                'payment_status' => $appointment->payment_status,
            ];
        });

        return response()->json($response, 200);
    }
    /////
    public function showAppointmentsByType(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $validator = Validator::make($request->all(), [
            'status' => 'required',
            'type' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }

        $user = Auth::user();

        $doctor = Doctor::where('user_id', $user->id)->first();
        if (!$doctor) return response()->json(['message' => 'Doctor Not Found'], 404);

        $scheduleIds = Schedule::where('doctor_id', $doctor->id)->pluck('id')->toArray();

        if ($request->type == 'first time') {
            if ($request->status != 'today') {
                $appointments = Appointment::with('patient')->whereIn('schedule_id', $scheduleIds)->where('status', $request->status)->where('parent_id', null);
            } else {
                $today = now()->format('Y-m-d');
                $appointments = Appointment::with('patient')->whereIn('schedule_id', $scheduleIds)->where('reservation_date', $today)->where('parent_id', null);
            }
            $type = 'first time';
        } else {
            if ($request->status != 'today') {
                $appointments = Appointment::with('patient')->whereIn('schedule_id', $scheduleIds)->where('status', $request->status)->whereNotNull('parent_id');
            } else {
                $today = now()->format('Y-m-d');
                $appointments = Appointment::with('patient')->whereIn('schedule_id', $scheduleIds)->where('reservation_date', $today)->whereNotNull('parent_id');
            }
            $type = 'check up';
        }

        $response = $this->paginateResponse($request, $appointments, 'Appointments', function ($appointment) {
            $type = $appointment->parent_id === null ? 'first time' : 'check up';

                return [
                    'id' => $appointment->id,
                    'patient_first_name' => $appointment->patient->first_name,
                    'patient_last_name' => $appointment->patient->last_name,
                    'reservation_date' => $appointment->reservation_date,
                    'reservation_hour' => $appointment->timeSelected,
                    'status' => $appointment->status,
                    'appointment_type' => $type,
                    'payment_status' => $appointment->payment_status,
                ];
        });

        return response()->json($response, 200);
    }
    /////
    public function showpatientAppointments(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->all()
            ], 400);
        }

        $user = Auth::user();

        $doctor = Doctor::where('user_id', $user->id)->first();
        if (!$doctor) return response()->json(['message' => 'Doctor Not Found'], 404);

        $scheduleIds = Schedule::where('doctor_id', $doctor->id)->pluck('id')->toArray();

        $appointments = Appointment::where('patient_id', $request->patient_id)->whereIn('schedule_id', $scheduleIds);

        $response = $this->paginateResponse($request, $appointments, 'Appointments', function ($appointment) {
            $type = $appointment->parent_id === null ? 'first time' : 'check up';

            return [
                'id' => $appointment->id,
                'patient_first_name' => $appointment->patient->first_name,
                'patient_last_name' => $appointment->patient->last_name,
                'reservation_date' => $appointment->reservation_date,
                'reservation_hour' => $appointment->timeSelected,
                'status' => $appointment->status,
                'appointment_type' => $type,
                'payment_status' => $appointment->payment_status,
            ];
        });

        return response()->json($response, 200);
    }
    /////
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
        ];

        return response()->json($response, 200);
    }
    /////
    public function showAppointmantResults(Request $request)
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
        $appointment = Appointment::find($request->appointment_id);
        $medicalInfo = MedicalInfo::where('appointment_id', $appointment->id)->first();
        if (!$medicalInfo) return response()->json(['message' => 'MedicalInfo Not Found'], 404);

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
        if (!$doctor) return response()->json(['message' => 'Doctor Not Found'], 404);

        $schedules = Schedule::where('doctor_id', $doctor->id)->get();
        $workingDays = $schedules->pluck('day');

        $startDate = Carbon::today();
        $endDate = Carbon::today()->addYear();
        $period = CarbonPeriod::create($startDate, $endDate);

        $availableDates = collect();

        foreach ($period as $date) {
            if ($workingDays->contains($date->format('l'))) {
                $availableDates->push($date->toDateString());
            }
        }

        foreach ($availableDates as $key => $availableDate) {
            foreach ($schedules as $schedule) {
                $date = $availableDate;
                $startLeaveDate = $schedule->start_leave_date;
                $endLeaveDate = $schedule->end_leave_date;
                $startLeaveTime =  $schedule->start_leave_time;
                $endLeaveTime =  $schedule->end_leave_time;

                if ($date >= $startLeaveDate && $date <= $endLeaveDate) {
                    if ($schedule->Shift == 'morning shift:from 9 AM to 3 PM') {
                        $start = Carbon::createFromTime(9, 0, 0)->format('H:i:s');
                        $end = Carbon::createFromTime(15, 0, 0)->format('H:i:s');
                    } else {
                        $start = Carbon::createFromTime(15, 0, 0)->format('H:i:s');
                        $end = Carbon::createFromTime(21, 0, 0)->format('H:i:s');
                    }
                    if ($startLeaveTime == null && $endLeaveTime == null) {
                        $availableDates->forget($key);
                        continue;
                    }
                    if ($startLeaveTime == $start && $endLeaveTime == $end) {
                        $availableDates->forget($key);
                    }
                }
            }
        }

        return response()->json([
            'available_dates' => $availableDates->values()
        ], 200);
    }
    /////
    public function showTimes(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();
        if (!$doctor) return response()->json(['message' => 'Doctor Not Found'], 404);

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

        $schedule = Schedule::where('doctor_id', $doctor->id)->where('day', $day)->first();

        $mysqlDate = Carbon::createFromFormat('d/m/y', $request->date)->format('Y-m-d');

        $appointments = Appointment::where('schedule_id', $schedule->id)
            ->where('reservation_date', $mysqlDate)
            ->get();

        $visitTime = Doctor::where('id', $doctor->id)->select('average_visit_duration')->first()->average_visit_duration;
        if (!$visitTime) return response()->json(['message' => 'Visit Time Not Availabe'], 404);
        $visitTime = (float) $visitTime;
        $numOfPeopleInHour = floor(60 / $visitTime);

        // filter the times 
        $available_times = [];

        if ($schedule->Shift == 'morning shift:from 9 AM to 3 PM') {
            $start = new DateTime('09:00');
            $end = new DateTime('15:00');
        } else {
            $start = new DateTime('15:00');
            $end = new DateTime('21:00');
        }

        $interval = new DateInterval('PT1H');
        $period = new DatePeriod($start, $interval, $end);

        foreach ($period as $time) {

            $timeFormatted = $time->format('H:i:s');
            $count = $appointments->where('timeSelected', $timeFormatted)->where('status', 'pending')->count();
            if ($date->toDateString() >= $schedule->start_leave_date && $date->toDateString() <= $schedule->end_leave_date) {
                if ($time->format('H:i') >= $schedule->start_leave_time && $time->format('H:i') <= $schedule->end_leave_time) {
                    continue;
                }
            }
            if ($count < $numOfPeopleInHour) {
                $available_times[] = $time->format('H:i');
            }
        }

        if ($available_times == []) {
            return response()->json([
                'message' => 'this doctor is not available in this date'
            ], 400);
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
        if (!$doctor) return response()->json(['message' => 'Doctor Not Found'], 404);

        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'date' => 'required|date_format:d/m/y',
            'time' => 'required|date_format:H:i',
            'this_appointment_id' => 'required|exists:appointments,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }
        $patient = Patient::where('id', $request->patient_id)->first();
        if (!$patient) {
            return response()->json(['message' => 'Patient not found.'], 404);
        }

        $dateFormatted = Carbon::createFromFormat('d/m/y', $request->date)->format('Y-m-d');
        $timeFormatted = Carbon::parse($request->time)->format('H:i:s');

        $date = Carbon::createFromFormat('d/m/y', $request->date);
        $time = Carbon::createFromFormat('H:i', $request->time);
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

        $userTime = new DateTime($request->input('time'));
        if ($schedule->Shift == 'morning shift:from 9 AM to 3 PM') {
            $start = new DateTime('09:00');
            $end = new DateTime('15:00');
        } else {
            $start = new DateTime('15:00');
            $end = new DateTime('21:00');
        }

        if ($userTime < $start || $userTime >= $end) {
            return response()->json([
                'message' => 'this time not available in this schedule',
            ], 400);
        }

        if ($date->toDateString() >= $schedule->start_leave_date && $date->toDateString() <= $schedule->end_leave_date) {
            if ($time->format('H:i') >= $schedule->start_leave_time && $time->format('H:i') <= $schedule->end_leave_time) {
                return response()->json([
                    'message' => 'this doctor is not available in this date '
                ], 400);
            }
        }

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
    public function editSchedule(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();
        if (!$doctor) return response()->json(['message' => 'Doctor Not Found'], 404);
        return $this->editDoctorSchedule($request, $doctor->id);
    }
    /////  
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
        if ($user->role != 'doctor') {
            return response()->json([
                'message' => 'you dont have permission'
            ], 401);
        }
    }
}

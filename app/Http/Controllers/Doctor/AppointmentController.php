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
use App\Models\User;
use App\Models\VaccinationRecord;
use App\PaginationTrait;
use FontLib\Table\Type\fpgm;
use Illuminate\Support\Facades\Validator;
use App\Services\FirebaseService;
use App\Notifications\Referral;

class AppointmentController extends Controller
{
    use CancelAppointmentsTrait;
    use PaginationTrait;
    protected $firebaseService;

    public function __construct(FirebaseService $firebase_service)
    {
        $this->firebaseService = $firebase_service;
    }

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
            $referring_doctor_name = null;
            if ($appointment->referring_doctor != null) {
                $referring_doctor = Doctor::find($appointment->referring_doctor);
                if ($referring_doctor) {
                    $referring_doctor_name = 'Dr. ' . $referring_doctor->first_name . ' ' . $referring_doctor->last_name;
                }
            }
            $is_child = false;
            if ($appointment->patient->parent_id != null) {
                $is_child = true;
            }
            return [
                'id' => $appointment->id,
                'patient_first_name' => $appointment->patient->first_name,
                'patient_last_name' => $appointment->patient->last_name,
                'patient_gender' => $appointment->patient->gender,
                'reservation_date' => $appointment->reservation_date,
                'reservation_hour' => $appointment->timeSelected,
                'status' => $appointment->status,
                'appointment_type' => $type,
                'appointment_info' => $appointment->appointment_type,
                'payment_status' => $appointment->payment_status,
                'referred by' => $referring_doctor_name,
                'is_child' => $is_child,
            ];
        });

        return response()->json($response, 200);
    }
    /////

    public function showChildsAppointments(Request $request) {
        $auth = $this->auth();
        if ($auth) return $auth;

        $user = Auth::user();

        $doctor = Doctor::where('user_id', $user->id)->first();
        if (!$doctor) return response()->json(['message' => 'Doctor Not Found'], 404);

        $scheduleIds = Schedule::where('doctor_id', $doctor->id)->pluck('id')->toArray();

        if ($request->status != 'today') {
            if ($request->has('date')) {
                $date = Carbon::createFromFormat('m-Y', $request->date);
                $startOfMonth = $date->startOfMonth()->toDateString();
                $endOfMonth = $date->endOfMonth()->toDateString();

                $appointments = Appointment::with('patient')
                    ->whereIn('schedule_id', $scheduleIds)
                    ->where('status', $request->status)
                    ->whereBetween('reservation_date', [$startOfMonth, $endOfMonth])
                    ->whereHas('patient', function ($query) {
                        $query->whereNotNull('parent_id');
                    })
                ->get();
            } else {
                $appointments = Appointment::with('patient')
                    ->whereIn('schedule_id', $scheduleIds)
                    ->where('status', $request->status)
                    ->whereHas('patient', function ($query) {
                        $query->whereNotNull('parent_id');
                    })
                ->get();
            }
        } else {
            $today = now()->format('Y-m-d');
            $appointments = Appointment::with('patient')
                ->whereIn('schedule_id', $scheduleIds)
                ->where('reservation_date', $today)
                ->whereHas('patient', function ($query) {
                    $query->whereNotNull('parent_id');
                })
                ->get();
        }

        $response = [];

        foreach ($appointments as $appointment) {
            $type = $appointment->parent_id === null ? 'first time' : 'check up';
            $referring_doctor_name = null;

            if ($appointment->referring_doctor != null) {
                $referring_doctor = Doctor::find($appointment->referring_doctor);
                if ($referring_doctor) {
                    $referring_doctor_name = 'Dr. ' . $referring_doctor->first_name . ' ' . $referring_doctor->last_name;
                }
            }

            $is_child = $appointment->patient->parent_id != null;

            $response[] = [
                'id' => $appointment->id,
                'patient_id' => $appointment->patient->id,
                'patient_first_name' => $appointment->patient->first_name,
                'patient_last_name' => $appointment->patient->last_name,
                'patient_gender' => $appointment->patient->gender,
                'reservation_date' => $appointment->reservation_date,
                'reservation_hour' => $appointment->timeSelected,
                'status' => $appointment->status,
                'appointment_type' => $type,
                'appointment_info' => $appointment->appointment_type,
                'payment_status' => $appointment->payment_status,
                'referred by' => $referring_doctor_name,
                'is_child' => $is_child,
            ];
        }

        return response()->json($response, 200);
    }

    public function showAppointmentsByStatus(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $validator = Validator::make($request->all(), [
            'status' => 'required',
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

        if ($request->status != 'today') {
            if ($request->has('date')) {
                $date = Carbon::createFromFormat('m-Y', $request->date);
                $startOfMonth = $date->startOfMonth()->toDateString();
                $endOfMonth = $date->endOfMonth()->toDateString();

                $appointments = Appointment::with('patient')
                    ->whereIn('schedule_id', $scheduleIds)
                    ->where('status', $request->status)
                    ->whereBetween('reservation_date', [$startOfMonth, $endOfMonth])
                    ->get();
            } else {
                $appointments = Appointment::with('patient')
                    ->whereIn('schedule_id', $scheduleIds)
                    ->where('status', $request->status)
                    ->get();
            }
        } else {
            $today = now()->format('Y-m-d');
            $appointments = Appointment::with('patient')
                ->whereIn('schedule_id', $scheduleIds)
                ->where('reservation_date', $today)
                ->get();
        }

        $response = [];

        foreach ($appointments as $appointment) {
            $type = $appointment->parent_id === null ? 'first time' : 'check up';
            $referring_doctor_name = null;

            if ($appointment->referring_doctor != null) {
                $referring_doctor = Doctor::find($appointment->referring_doctor);
                if ($referring_doctor) {
                    $referring_doctor_name = 'Dr. ' . $referring_doctor->first_name . ' ' . $referring_doctor->last_name;
                }
            }

            $is_child = $appointment->patient->parent_id != null;

            $response[] = [
                'id' => $appointment->id,
                'patient_id' => $appointment->patient->id,
                'patient_first_name' => $appointment->patient->first_name,
                'patient_last_name' => $appointment->patient->last_name,
                'patient_gender' => $appointment->patient->gender,
                'reservation_date' => $appointment->reservation_date,
                'reservation_hour' => $appointment->timeSelected,
                'status' => $appointment->status,
                'appointment_type' => $type,
                'appointment_info' => $appointment->appointment_type,
                'payment_status' => $appointment->payment_status,
                'referred by' => $referring_doctor_name,
                'is_child' => $is_child,
            ];
        }

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

        $isFirstTime = $request->type == 'first time';

        $appointments = Appointment::with('patient')->whereIn('schedule_id', $scheduleIds);

        if ($request->status != 'today') {
            $appointments->where('status', $request->status);

            if ($request->has('date')) {
                $date = Carbon::createFromFormat('m-Y', $request->date);
                $startOfMonth = $date->startOfMonth()->toDateString();
                $endOfMonth = $date->endOfMonth()->toDateString();

                $appointments->whereBetween('reservation_date', [$startOfMonth, $endOfMonth]);
            }

            $appointments->where($isFirstTime ? 'parent_id' : 'parent_id', $isFirstTime ? null : '!=', null);
        } else {
            $today = now()->format('Y-m-d');
            $appointments->where('reservation_date', $today);
            $appointments->where($isFirstTime ? 'parent_id' : 'parent_id', $isFirstTime ? null : '!=', null);
        }

        $usePagination = $request->has('page') || $request->has('size');

        if ($usePagination) {
            $response = $this->paginateResponse($request, $appointments, 'Appointments', function ($appointment) use ($isFirstTime) {
                $referring_doctor_name = null;
                if ($appointment->referring_doctor != null) {
                    $referring_doctor = Doctor::find($appointment->referring_doctor);
                    if ($referring_doctor) {
                        $referring_doctor_name = 'Dr. ' . $referring_doctor->first_name . ' ' . $referring_doctor->last_name;
                    }
                }

                $is_child = $appointment->patient->parent_id != null;

                return [
                    'id' => $appointment->id,
                    'patient_first_name' => $appointment->patient->first_name,
                    'patient_last_name' => $appointment->patient->last_name,
                    'patient_gender' => $appointment->patient->gender,
                    'reservation_date' => $appointment->reservation_date,
                    'reservation_hour' => $appointment->timeSelected,
                    'status' => $appointment->status,
                    'appointment_type' => $isFirstTime ? 'first time' : 'check up',
                    'appointment_info' => $appointment->appointment_type,
                    'payment_status' => $appointment->payment_status,
                    'referred_by' => $referring_doctor_name,
                    'is_child' => $is_child,
                ];
            });
        } else {
            $items = $appointments->get();
            $data = [];

            foreach ($items as $appointment) {
                $referring_doctor_name = null;
                if ($appointment->referring_doctor != null) {
                    $referring_doctor = Doctor::find($appointment->referring_doctor);
                    if ($referring_doctor) {
                        $referring_doctor_name = 'Dr. ' . $referring_doctor->first_name . ' ' . $referring_doctor->last_name;
                    }
                }

                $is_child = $appointment->patient->parent_id != null;

                $data[] = [
                    'id' => $appointment->id,
                    'patient_first_name' => $appointment->patient->first_name,
                    'patient_last_name' => $appointment->patient->last_name,
                    'patient_gender' => $appointment->patient->gender,
                    'reservation_date' => $appointment->reservation_date,
                    'reservation_hour' => $appointment->timeSelected,
                    'status' => $appointment->status,
                    'appointment_type' => $isFirstTime ? 'first time' : 'check up',
                    'appointment_info' => $appointment->appointment_type,
                    'payment_status' => $appointment->payment_status,
                    'referred_by' => $referring_doctor_name,
                    'is_child' => $is_child,
                ];
            }
            $response = $data;
        }

        return response()->json($response, 200);
    }

    /////
    public function filteringAppointmentsByDate(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'date' => ['required', 'date_format:m-Y'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }

        $doctor = Doctor::where('user_id', $user->id)->first();
        if (!$doctor) return response()->json(['message' => 'Doctor Not Found'], 404);

        $scheduleIds = Schedule::where('doctor_id', $doctor->id)->pluck('id')->toArray();
        $date = Carbon::createFromFormat('m-Y', $request->date);
        $startOfMonth = $date->startOfMonth()->toDateString();
        $endOfMonth = $date->endOfMonth()->toDateString();

        $appointments = Appointment::with('patient')
            ->whereIn('schedule_id', $scheduleIds)
            ->whereBetween('reservation_date', [$startOfMonth, $endOfMonth])
            ->get();

        $response = [];

        foreach ($appointments as $appointment) {
            $type = $appointment->parent_id === null ? 'first time' : 'check up';
            $referring_doctor_name = null;

            if ($appointment->referring_doctor != null) {
                $referring_doctor = Doctor::find($appointment->referring_doctor);
                if ($referring_doctor) {
                    $referring_doctor_name = 'Dr. ' . $referring_doctor->first_name . ' ' . $referring_doctor->last_name;
                }
            }

            $is_child = $appointment->patient->parent_id != null;

            $response[] = [
                'id' => $appointment->id,
                'patient_first_name' => $appointment->patient->first_name,
                'patient_last_name' => $appointment->patient->last_name,
                'patient_gender' => $appointment->patient->gender,
                'reservation_date' => $appointment->reservation_date,
                'reservation_hour' => $appointment->timeSelected,
                'status' => $appointment->status,
                'appointment_type' => $type,
                'appointment_info' => $appointment->appointment_type,
                'payment_status' => $appointment->payment_status,
                'referred by' => $referring_doctor_name,
                'is_child' => $is_child,
            ];
        }

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
        if ($request->has('date')) {
            $date = Carbon::createFromFormat('m-Y', $request->date);
            $startOfMonth = $date->startOfMonth()->toDateString();
            $endOfMonth = $date->endOfMonth()->toDateString();
            $appointments = Appointment::where('patient_id', $request->patient_id)->whereIn('schedule_id', $scheduleIds)->whereBetween('reservation_date', [$startOfMonth, $endOfMonth]);
        } else {
            $appointments = Appointment::where('patient_id', $request->patient_id)->whereIn('schedule_id', $scheduleIds);
        }

        $response = $this->paginateResponse($request, $appointments, 'Appointments', function ($appointment) {
            $type = $appointment->parent_id === null ? 'first time' : 'check up';
            $referring_doctor_name = null;
            if ($appointment->referring_doctor != null) {
                $referring_doctor = Doctor::find($appointment->referring_doctor);
                if ($referring_doctor) {
                    $referring_doctor_name = 'Dr. ' . $referring_doctor->first_name . ' ' . $referring_doctor->last_name;
                }
            }
            $is_child = false;
            if ($appointment->patient->parent_id != null) {
                $is_child = true;
            }
            return [
                'id' => $appointment->id,
                'patient_first_name' => $appointment->patient->first_name,
                'patient_last_name' => $appointment->patient->last_name,
                'patient_gender' => $appointment->patient->gender,
                'reservation_date' => $appointment->reservation_date,
                'reservation_hour' => $appointment->timeSelected,
                'status' => $appointment->status,
                'appointment_type' => $type,
                'appointment_info' => $appointment->appointment_type,
                'payment_status' => $appointment->payment_status,
                'referred by' => $referring_doctor_name,
                'is_child' => $is_child,
            ];
        });

        return response()->json($response, 200);
    }
    /////

    public function showAppointmentVaccinatioinRecord(Request $request) {
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

        $vaccinationRecord = VaccinationRecord::with('appointment.patient', 'vaccine')
        ->where('appointment_id', $request->appointment_id)
        ->first();

        if(!$vaccinationRecord) return response()->json(['message' => 'record not found'], 404);

        $response = [
            'vaccination_record_id' => $vaccinationRecord->id,
            'vaccine_id' => $vaccinationRecord->vaccine_id,
            'vaccine_name' => $vaccinationRecord->vaccine->name,
            'dose_number' => $vaccinationRecord->dose_number,
            'notes' => $vaccinationRecord->notes,
            'isTaken' => $vaccinationRecord->isTaken,
            'when_to_take' => $vaccinationRecord->when_to_take,
            'recommended' => $vaccinationRecord->recommended,
            'next_vaccine_date' => $vaccinationRecord->next_vaccine_date,
            'patient_id' => $vaccinationRecord->child_id,
            'patient_first_name' => $vaccinationRecord->patient->first_name,
            'patient_last_name' => $vaccinationRecord->patient->last_name,
            'reservation_date' => $vaccinationRecord->appointment->reservation_date,
            'reservation_hour' => $vaccinationRecord->appointment->timeSelected,
        ];

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
            $medicines = Medicine::where('prescription_id', $prescription->id)->select(['id', 'name', 'dose', 'frequency', 'strength', 'until', 'whenToTake', 'note']);
            $medicines = $this->paginateResponse($request, $medicines, 'medicines');
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
    public function showVaccinationAppointments(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $user = Auth::user();
        $doctor = Doctor::with('schedule')->where('user_id', $user->id)->first();
        if (!$doctor) return response()->json(['message' => 'Doctor Not Found'], 404);

        $scheduleIds = Schedule::where('doctor_id', $doctor->id)->pluck('id')->toArray();

        $appointments = Appointment::with('patient')
            ->where('appointment_type', 'vaccination')
            ->whereIn('schedule_id', $scheduleIds);

        $response = $this->paginateResponse($request, $appointments, 'Appointments', function ($appointment) {
            return [
                'id' => $appointment->id,
                'patient_first_name' => $appointment->patient->first_name,
                'patient_last_name' => $appointment->patient->last_name,
                'reservation_date' => $appointment->reservation_date,
                'reservation_hour' => $appointment->timeSelected,
                'status' => $appointment->status,
                'appointment_type' => $appointment->appointment_type,
                'payment_status' => $appointment->payment_status,
            ];
        });
        return response()->json($response, 200);
    }
    /////
    public function showVaccinationAppointmentDetails(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $vaccinationRecord = VaccinationRecord::where('appointment_id', $request->appointment_id)->first();
        if (!$vaccinationRecord) return response()->json(['message' => 'vaccination record not found'], 404);

        return response()->json($vaccinationRecord, 200);
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

        $schedule = Schedule::where('doctor_id', $doctor->id)->where('status', 'notAvailable')->where('day', $day)->first();
        if (!$schedule) return response()->json(['message' => 'you are not available on this day'], 400);

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

        if ($schedule->doctor->booking_type == 'manual') {

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
                    'message' => 'you are not available in this date'
                ], 400);
            }
        }

        return response()->json($available_times, 200);
    }
    /////
    public function addManualReservation(Request $request)
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
            'appointment_type' => 'required|in:visit,vaccination'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }

        $patient = Patient::where('id', $request->patient_id)->first();
        if (!$patient) return response()->json(['message' => 'Patient Not Found'], 404);

        $dateFormatted = Carbon::createFromFormat('d/m/y', $request->date)->format('Y-m-d');
        $timeFormatted = Carbon::parse($request->time)->format('H:i:s');

        $date = Carbon::createFromFormat('d/m/y', $request->date);
        $time = Carbon::createFromFormat('H:i', $request->time);
        $day = $date->format('l');

        $schedule = Schedule::where('doctor_id', $doctor->id)
            ->where('status', 'notAvailable')
            ->where('day', $day)
            ->first();
        if (!$schedule) return response()->json(['message' => 'Schedule Not Found'], 404);

        $appointmentsNum = Appointment::where('schedule_id', $schedule->id)
            ->where('reservation_date', $dateFormatted)
            ->where('status', 'pending')
            ->where('timeSelected', $timeFormatted)
            ->count();

        $visitTime = Doctor::where('id', $doctor->id)->select('average_visit_duration')->first()->average_visit_duration;
        if (!$visitTime) return response()->json(['message' => 'Visit Time Not Availabe'], 404);
        $visitTime = (float) $visitTime;

        if ($visitTime == 0 || $doctor->status == 'notAvailable') {
            return response()->json(['message' => 'doctor not available'], 503);
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
                    'message' => 'you are not available in this date '
                ], 400);
            }
        }

        $newTimeFormatted = Carbon::parse($request->time);
        if ($appointmentsNum == $numOfPeopleInHour) $timeSelected = $newTimeFormatted->addHours(1)->toTimeString();
        else $timeSelected = $timeFormatted;

        $numOfPatientReservation = Appointment::where('patient_id', $patient->id)
            ->where('schedule_id', $schedule->id)
            ->where('reservation_date', $dateFormatted)
            ->where('status', 'pending')
            ->count();

        if ($numOfPatientReservation > 0) {
            return response()->json([
                'message' => 'You already appointment at this date'
            ], 400);
        }

        if ($appointmentsNum < $numOfPeopleInHour) {

            $sameDayAppointment = Appointment::where('patient_id', $patient->id)
                ->where('reservation_date', $dateFormatted)
                ->where('timeSelected', $timeFormatted)
                ->where('status', '!=', 'cancelled')
                ->first();
            if ($sameDayAppointment) {
                return response()->json(['message' => 'Sorry, the patient already has an appointment on the same day'], 400);
            }

            $lastQueueNumber = Appointment::where('schedule_id',  $schedule->id)
                ->whereDate('reservation_date', $dateFormatted)
                ->whereTime('timeSelected', $timeFormatted)
                ->max('queue_number');
            $newQueueNumber = $lastQueueNumber ? $lastQueueNumber + 1 : 1;

            $expectedPrice = $doctor->visit_fee;

            $appointment = Appointment::create([
                'patient_id' => $patient->id,
                'schedule_id' => $schedule->id,
                'timeSelected' => $timeSelected,
                'reservation_date' => $dateFormatted,
                'appointment_type' => $request->appointment_type ?? 'visit',
                'expected_price' => $expectedPrice,
                'queue_number'    => $newQueueNumber,
                'parent_id' => $request->this_appointment_id,
            ]);

            if ($appointment->appointment_type == 'vaccination') {
                // لازم يعطيني كمان السجل يلي بدي اعمله ال appointment
                $vaccinationRecord = VaccinationRecord::with('vaccine')->where('id', $request->record_id)->first();
                if (!$vaccinationRecord) return response()->json(['message' => 'record not found'], 404);

                $vaccinationRecord->appointment_id = $appointment->id;
                $vaccinationRecord->save();

                $appointment->expected_price += $vaccinationRecord->vaccine->price;
                $appointment->save();
            }

            return response()->json($appointment, 200);
        }

        return response()->json(['message' => 'this time is full'], 400);
    }

    public function addAutoReservation(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();
        if (!$doctor) return response()->json(['message' => 'Doctor Not Found'], 404);

        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'date' => 'required|date_format:d/m/y',
            'this_appointment_id' => 'required|exists:appointments,id',
            'appointment_type' => 'in:visit,vaccination'

        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }

        $patient = Patient::where('id', $request->patient_id)->first();
        if (!$patient) return response()->json(['message' => 'Patient Not Found'], 404);

        $dateFormatted = Carbon::createFromFormat('d/m/y', $request->date)->format('Y-m-d');

        $date = Carbon::createFromFormat('d/m/y', $request->date);
        $day = $date->format('l');

        $schedule = Schedule::where('doctor_id', $doctor->id)
            ->where('status', 'notAvailable')
            ->where('day', $day)
            ->first();

        if (!$schedule) return response()->json(['message' => 'Schedule Not Found'], 404);


        $lastReservationTime = Appointment::where('schedule_id', $schedule->id)
            ->whereDate('reservation_date', $dateFormatted)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lastReservationTime) {
            $shift = $schedule->Shift;

            if ($shift == 'morning shift:from 9 AM to 3 PM') {
                $reservationTime = new DateTime('09:00');
            } else {
                $reservationTime = new DateTime('15:00');
            }
        } else {
            $reservationTime = new DateTime($lastReservationTime->timeSelected);
        }

        $appointmentsNum = Appointment::where('schedule_id', $schedule->id)
            ->where('reservation_date', $dateFormatted)
            ->where('status', 'pending')
            ->where('timeSelected', $reservationTime)
            ->count();

        $visitTime = Doctor::where('id', $doctor->id)->select('average_visit_duration')->first()->average_visit_duration;
        if (!$visitTime) return response()->json(['message' => 'Visit Time Not Availabe'], 404);
        $visitTime = (float) $visitTime;

        if ($visitTime == 0 || $doctor->status == 'notAvailable') {
            return response()->json(['message' => 'doctor not available'], 503);
        }
        $numOfPeopleInHour = floor(60 / $visitTime);

        $reservationCarbonTime = Carbon::createFromFormat('H:i', $reservationTime->format('H:i'));
        if ($date->toDateString() >= $schedule->start_leave_date && $date->toDateString() <= $schedule->end_leave_date) {
            if ($reservationCarbonTime->format('H:i') >= $schedule->start_leave_time && $reservationCarbonTime->format('H:i') <= $schedule->end_leave_time) {
                return response()->json([
                    'message' => 'you are not available in this date '
                ], 400);
            }
        }

        $newTimeFormatted = Carbon::parse($reservationTime);
        if ($appointmentsNum == $numOfPeopleInHour) $timeSelected = $newTimeFormatted->addHours(1)->toTimeString();
        else $timeSelected = $newTimeFormatted->toTimeString();

        $numOfPatientReservation = Appointment::where('patient_id', $patient->id)
            ->where('schedule_id', $schedule->id)
            ->where('reservation_date', $dateFormatted)
            ->where('status', 'pending')
            ->count();

        if ($numOfPatientReservation > 0) {
            return response()->json([
                'message' => 'You already appointment at this date'
            ], 400);
        }

        $appointmentsTimeNum = Appointment::where('schedule_id', $schedule->id)
            ->where('reservation_date', $dateFormatted)
            ->where('status', 'pending')
            ->where('timeSelected', $timeSelected)
            ->count();

        if ($appointmentsTimeNum < $numOfPeopleInHour) {

            $sameDayAppointment = Appointment::where('patient_id', $patient->id)
                ->where('reservation_date', $dateFormatted)
                ->where('timeSelected', $timeSelected)
                ->where('status', '!=', 'cancelled')
                ->first();
            if ($sameDayAppointment) {
                return response()->json(['message' => 'Sorry, the patient already has an appointment on the same day'], 400);
            }

            $lastQueueNumber = Appointment::where('schedule_id', $schedule->id)
                ->whereDate('reservation_date', $dateFormatted)
                ->max('queue_number');
            $newQueueNumber = $lastQueueNumber ? $lastQueueNumber + 1 : 1;

            $expectedPrice = $doctor->visit_fee;

            $appointment = Appointment::create([
                'patient_id' => $patient->id,
                'schedule_id' => $schedule->id,
                'timeSelected' => $timeSelected,
                'reservation_date' => $dateFormatted,
                'appointment_type' => $request->appointment_type ?? 'visit',
                'expected_price' => $expectedPrice,
                'queue_number'    => $newQueueNumber,
                'parent_id' => $request->this_appointment_id,
            ]);

            if ($appointment->appointment_type == 'vaccination') {
                // لازم يعطيني كمان السجل يلي بدي اعمله ال appointment
                $vaccinationRecord = VaccinationRecord::with('vaccine')->where('id', $request->record_id)->first();
                if (!$vaccinationRecord) return response()->json(['message' => 'record not found'], 404);

                $vaccinationRecord->appointment_id = $appointment->id;
                $vaccinationRecord->save();

                $appointment->expected_price += $vaccinationRecord->vaccine->price;
                $appointment->save();
            }

            return response()->json($appointment, 200);
        }

        return response()->json(['message' => 'this time is full'], 400);
    }

    public function addCheckup(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();
        if (!$doctor) return response()->json(['message' => 'Doctor Not Found'], 404);

        if ($doctor->booking_type == 'manual') {

            $validator = Validator::make($request->all(), [
                'time' => 'required|date_format:H:i',
                'appointment_type' => 'in:visit,vaccination',
                'record_id' => 'sometimes|exists:vaccination_records,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' =>  $validator->errors()->all()
                ], 400);
            }

            return $this->addManualReservation($request);
        } else {
            $validator = Validator::make($request->all(), [
                'appointment_type' => 'in:visit,vaccination',
                'record_id' => 'sometimes|exists:vaccination_records,id',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' =>  $validator->errors()->all()
                ], 400);
            }
            return $this->addAutoReservation($request);
        }
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
    ////////Referral////////
    public function showClinicDoctors(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $doctors = Doctor::select('id', 'first_name', 'last_name', 'clinic_id')
            ->get();

        $clinic_doctors = [];
        foreach ($doctors as $doctor) {
            if ($doctor->clinic_id == $request->clinic_id) {
                $clinic_doctors[] = $doctor;
            }
        }

        return response()->json($clinic_doctors, 200);
    }
    /////
    public function showReferralDoctorWorkDays(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required|exists:doctors,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }

        $schedules = Schedule::where('doctor_id', $request->doctor_id)->where('status', 'notAvailable')->get();
        $workingDays = $schedules->pluck('day');

        $startDate = Carbon::today();
        $endDate = Carbon::today()->addMonth();
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

    public function showReferralTimes(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required|exists:doctors,id',
            'date' => 'required|date_format:d/m/y',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }

        $date = Carbon::createFromFormat('d/m/y', $request->date);
        $day = $date->format('l');

        $schedule = Schedule::where('doctor_id', $request->doctor_id)->where('status', 'notAvailable')->where('day', $day)->first();
        if (!$schedule) return response()->json(['message' => 'the doctor is not available on this day'], 400);

        $mysqlDate = Carbon::createFromFormat('d/m/y', $request->date)->format('Y-m-d');

        $appointments = Appointment::where('schedule_id', $schedule->id)
            ->where('reservation_date', $mysqlDate)
            ->get();

        $visitTime = Doctor::where('id', $request->doctor_id)->select('average_visit_duration')->first()->average_visit_duration;
        if (!$visitTime) return response()->json(['message' => 'Visit Time Not Availabe'], 404);
        $visitTime = (float) $visitTime;
        $numOfPeopleInHour = floor(60 / $visitTime);

        // filter the times 
        $available_times = [];

        if ($schedule->doctor->booking_type == 'manual') {

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
        }

        return response()->json($available_times, 200);
    }
    ////
    public function addManualReferralReservation(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $user = Auth::user();
        $re_doctor = Doctor::where('user_id', $user->id)->first();
        if (!$re_doctor) return response()->json(['message' => 'Doctor Not Found'], 404);

        $patient = Patient::where('id', $request->patient_id)->first();

        if (!$patient) return response()->json(['message' => 'Patient Not Found'], 404);

        $dateFormatted = Carbon::createFromFormat('d/m/y', $request->date)->format('Y-m-d');
        $timeFormatted = Carbon::parse($request->time)->format('H:i:s');

        $date = Carbon::createFromFormat('d/m/y', $request->date);
        $time = Carbon::createFromFormat('H:i', $request->time);
        $day = $date->format('l');

        $schedule = Schedule::where('doctor_id', $request->doctor_id)
            ->where('status', 'notAvailable')
            ->where('day', $day)
            ->first();
        if (!$schedule) return response()->json(['message' => 'Schedule Not Found'], 404);
        $doctor = Doctor::where('id', $request->doctor_id)->first();
        if (!$doctor) return response()->json(['message' => 'Doctor Not Found'], 404);


        $appointmentsNum = Appointment::where('schedule_id', $schedule->id)
            ->where('reservation_date', $dateFormatted)
            ->where('status', 'pending')
            ->where('timeSelected', $timeFormatted)
            ->count();

        $visitTime = Doctor::where('id', $request->doctor_id)->select('average_visit_duration')->first()->average_visit_duration;
        if (!$visitTime) return response()->json(['message' => 'Visit Time Not Availabe'], 404);
        $visitTime = (float) $visitTime;

        if ($visitTime == 0 || $doctor->status == 'notAvailable') {
            return response()->json(['message' => 'this doctor not available'], 503);
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

        $numOfPatientReservation = Appointment::where('patient_id', $patient->id)
            ->where('schedule_id', $schedule->id)
            ->where('reservation_date', $dateFormatted)
            ->where('status', 'pending')
            ->count();

        if ($numOfPatientReservation > 0) {
            return response()->json([
                'message' => 'You already appointment at this date'
            ], 400);
        }

        if ($appointmentsNum < $numOfPeopleInHour) {

            $sameDayAppointment = Appointment::where('patient_id', $patient->id)
                ->where('reservation_date', $dateFormatted)
                ->where('timeSelected', $timeFormatted)
                ->where('status', '!=', 'cancelled')
                ->first();
            if ($sameDayAppointment) {
                return response()->json(['message' => 'you can not reservation two appointments at the same time'], 400);
            }

            $lastQueueNumber = Appointment::where('schedule_id',  $schedule->id)
                ->whereDate('reservation_date', $dateFormatted)
                ->whereTime('timeSelected', $timeFormatted)
                ->max('queue_number');
            $newQueueNumber = $lastQueueNumber ? $lastQueueNumber + 1 : 1;

            $expectedPrice = $doctor->visit_fee;

            $appointment = Appointment::create([
                'patient_id' => $patient->id,
                'schedule_id' => $schedule->id,
                'timeSelected' => $timeSelected,
                'reservation_date' => $dateFormatted,
                'appointment_type' => $request->appointment_type ?? 'visit',
                'expected_price' => $expectedPrice,
                'queue_number'    => $newQueueNumber,
                'is_referral' => true,
                'referring_doctor' => $re_doctor->id,
            ]);

            if ($appointment->appointment_type == 'vaccination') {
                // لازم يعطيني كمان السجل يلي بدي اعمله ال appointment
                $vaccinationRecord = VaccinationRecord::with('vaccine')->where('id', $request->record_id)->first();
                if (!$vaccinationRecord) return response()->json(['message' => 'record not found'], 404);

                $vaccinationRecord->appointment_id = $appointment->id;
                $vaccinationRecord->save();

                $appointment->expected_price += $vaccinationRecord->vaccine->price;
                $appointment->save();
            }

            return response()->json($appointment, 200);
        }

        return response()->json(['message' => 'this time is full'], 400);
    }

    public function addAutoReferralReservation(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $user = Auth::user();
        $re_doctor = Doctor::where('user_id', $user->id)->first();
        if (!$re_doctor) return response()->json(['message' => 'Doctor Not Found'], 404);

        $patient = Patient::where('id', $request->patient_id)->first();

        if (!$patient) return response()->json(['message' => 'Patient Not Found'], 404);

        $dateFormatted = Carbon::createFromFormat('d/m/y', $request->date)->format('Y-m-d');

        $date = Carbon::createFromFormat('d/m/y', $request->date);
        $day = $date->format('l');

        $schedule = Schedule::where('doctor_id', $request->doctor_id)
            ->where('status', 'notAvailable')
            ->where('day', $day)
            ->first();

        if (!$schedule) return response()->json(['message' => 'Schedule Not Found'], 404);
        $doctor = Doctor::where('id', $request->doctor_id)->first();
        if (!$doctor) return response()->json(['message' => 'Doctor Not Found'], 404);

        $lastReservationTime = Appointment::where('schedule_id', $schedule->id)
            ->whereDate('reservation_date', $dateFormatted)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lastReservationTime) {
            $shift = $schedule->Shift;

            if ($shift == 'morning shift:from 9 AM to 3 PM') {
                $reservationTime = new DateTime('09:00');
            } else {
                $reservationTime = new DateTime('15:00');
            }
        } else {
            $reservationTime = new DateTime($lastReservationTime->timeSelected);
        }

        $appointmentsNum = Appointment::where('schedule_id', $schedule->id)
            ->where('reservation_date', $dateFormatted)
            ->where('status', 'pending')
            ->where('timeSelected', $reservationTime)
            ->count();

        $visitTime = Doctor::where('id', $request->doctor_id)->select('average_visit_duration')->first()->average_visit_duration;
        if (!$visitTime) return response()->json(['message' => 'Visit Time Not Availabe'], 404);
        $visitTime = (float) $visitTime;

        if ($visitTime == 0 || $doctor->status == 'notAvailable') {
            return response()->json(['message' => 'this doctor not available'], 503);
        }
        $numOfPeopleInHour = floor(60 / $visitTime);

        $reservationCarbonTime = Carbon::createFromFormat('H:i', $reservationTime->format('H:i'));
        if ($date->toDateString() >= $schedule->start_leave_date && $date->toDateString() <= $schedule->end_leave_date) {
            if ($reservationCarbonTime->format('H:i') >= $schedule->start_leave_time && $reservationCarbonTime->format('H:i') <= $schedule->end_leave_time) {
                return response()->json([
                    'message' => 'this doctor is not available in this date '
                ], 400);
            }
        }

        $newTimeFormatted = Carbon::parse($reservationTime);
        if ($appointmentsNum == $numOfPeopleInHour) $timeSelected = $newTimeFormatted->addHours(1)->toTimeString();
        else $timeSelected = $newTimeFormatted->toTimeString();

        $numOfPatientReservation = Appointment::where('patient_id', $patient->id)
            ->where('schedule_id', $schedule->id)
            ->where('reservation_date', $dateFormatted)
            ->where('status', 'pending')
            ->count();

        if ($numOfPatientReservation > 0) {
            return response()->json([
                'message' => 'You already appointment at this date'
            ], 400);
        }

        $appointmentsTimeNum = Appointment::where('schedule_id', $schedule->id)
            ->where('reservation_date', $dateFormatted)
            ->where('status', 'pending')
            ->where('timeSelected', $timeSelected)
            ->count();

        if ($appointmentsTimeNum < $numOfPeopleInHour) {

            $sameDayAppointment = Appointment::where('patient_id', $patient->id)
                ->where('reservation_date', $dateFormatted)
                ->where('timeSelected', $timeSelected)
                ->where('status', '!=', 'cancelled')
                ->first();
            if ($sameDayAppointment) {
                return response()->json(['message' => 'Sorry, you have an appointment at the same time'], 400);
            }

            $lastQueueNumber = Appointment::where('schedule_id', $schedule->id)
                ->whereDate('reservation_date', $dateFormatted)
                ->max('queue_number');
            $newQueueNumber = $lastQueueNumber ? $lastQueueNumber + 1 : 1;

            $expectedPrice = $doctor->visit_fee;

            $appointment = Appointment::create([
                'patient_id' => $patient->id,
                'schedule_id' => $schedule->id,
                'timeSelected' => $timeSelected,
                'reservation_date' => $dateFormatted,
                'appointment_type' => $request->appointment_type ?? 'visit',
                'expected_price' => $expectedPrice,
                'queue_number'    => $newQueueNumber,
                'is_referral' => true,
                'referring_doctor' => $re_doctor->id,
            ]);

            if ($appointment->appointment_type == 'vaccination') {
                // لازم يعطيني كمان السجل يلي بدي اعمله ال appointment
                $vaccinationRecord = VaccinationRecord::with('vaccine')->where('id', $request->record_id)->first();
                if (!$vaccinationRecord) return response()->json(['message' => 'record not found'], 404);

                $vaccinationRecord->appointment_id = $appointment->id;
                $vaccinationRecord->save();

                $appointment->expected_price += $vaccinationRecord->vaccine->price;
                $appointment->save();
            }

            return response()->json($appointment, 200);
        }

        return response()->json(['message' => 'this time is full'], 400);
    }

    public function addReferralReservation(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $doctor = Doctor::findOrFail($request->doctor_id);

        if ($doctor->booking_type == 'manual') {
            $validator = Validator::make($request->all(), [
                'time' => 'required|date_format:H:i',
                'appointment_type' => 'in:visit,vaccination',
                'record_id' => 'sometimes|exists:vaccination_records,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' =>  $validator->errors()->all()
                ], 400);
            }

            return $this->addManualReferralReservation($request);
        } else {
            $validator = Validator::make($request->all(), [
                'appointment_type' => 'in:visit,vaccination',
                'record_id' => 'sometimes|exists:vaccination_records,id',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' =>  $validator->errors()->all()
                ], 400);
            }
            return $this->addAutoReferralReservation($request);
        }
        $user = User::where('id', $doctor->user_id)->first();
        if ($user->fcm_token) {
            $doctor2 = Doctor::where('user_id', $user->id)->first();
            $patient = Patient::where('id', $request->patient_id)->first();
            $doctorFullName = $doctor2->first_name . ' ' . $doctor2->last_name;
            $patientFullName = $patient->first_name . ' ' . $patient->last_name;
            $this->firebaseService->sendNotification(
                $user->fcm_token,
                'New referral',
                'The patient' . $patientFullName . ' has been referred to you by ' . $doctorFullName,
            );
            $user->notify(new Referral([
                'patient_id' => $patient->id,
                'referring_doctor_id' => $doctor->id,
                'patient_name' => $patientFullName,
                'referring_doctor_name' => $doctorFullName,
            ]));
        }
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

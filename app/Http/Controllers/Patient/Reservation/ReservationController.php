<?php

namespace App\Http\Controllers\Patient\Reservation;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Schedule;
use App\Models\VaccinationRecord;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Stripe\Refund;
use Stripe\Stripe;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;

use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\isNull;

class ReservationController extends Controller
{
    public function showDoctorWorkDays(Request $request)
    {
        //$request = department(clininc_id), doctor,
        $user = Auth::user();

        //check the auth
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

        $validator = Validator::make($request->all(), [
            // 'clinic_id' => 'required|exists:clinics,id',
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

    public function showTimes(Request $request)
    {
        $user = Auth::user();

        //check the auth
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

        $validator = Validator::make($request->all(), [

            // 'clinic_id' => 'required|exists:clinics,id',
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

    // public function addReservation(Request $request)
    // {
    //     $user = Auth::user();

    //     //check the auth
    //     if (!$user) {
    //         return response()->json([
    //             'message' => 'unauthorized'
    //         ], 401);
    //     }

    //     if ($user->role != 'patient') {
    //         return response()->json([
    //             'message' => 'you dont have permission'
    //         ], 401);
    //     }

    //     $patient = Patient::where('user_id', $user->id)->first();

    //     $validator = Validator::make($request->all(), [
    //         // 'clinic_id' => 'required|exists:clinics,id',
    //         'doctor_id' => 'required|exists:doctors,id',
    //         'date' => 'required|date_format:d/m/y',
    //         'time' => 'required|date_format:H:i'
    //     ]);


    //     if ($validator->fails()) {
    //         return response()->json([
    //             'message' =>  $validator->errors()->all()
    //         ], 400);
    //     }

    //     $dateFormatted = Carbon::createFromFormat('d/m/y', $request->date)->format('Y-m-d');
    //     $timeFormatted = Carbon::parse($request->time)->format('H:i:s');

    //     $date = Carbon::createFromFormat('d/m/y', $request->date);
    //     $time = Carbon::createFromFormat('H:i', $request->time);
    //     $day = $date->format('l');

    //     $schedule = Schedule::where('doctor_id', $request->doctor_id)
    //         ->where('status', 'notAvailable')
    //         ->where('day', $day)
    //         ->first();
    //     if (!$schedule) return response()->json(['message' => 'Schedule Not Found'], 404);
    //     $doctor = Doctor::where('id', $request->doctor_id)->first();
    //     if (!$doctor) return response()->json(['message' => 'Doctor Not Found'], 404);


    //     $appointmentsNum = Appointment::where('schedule_id', $schedule->id)
    //         ->where('reservation_date', $dateFormatted)
    //         ->where('status', 'pending')
    //         ->where('timeSelected', $timeFormatted)
    //         ->count();

    //     $visitTime = Doctor::where('id', $request->doctor_id)->select('average_visit_duration')->first()->average_visit_duration;
    //     if (!$visitTime) return response()->json(['message' => 'Visit Time Not Availabe'], 404);
    //     $visitTime = (float) $visitTime;

    //     if ($visitTime == 0 || $doctor->status == 'notAvailable') {
    //         return response()->json(['message' => 'this doctor not available'], 503);
    //     }

    //     $numOfPeopleInHour = floor(60 / $visitTime);

    //     $userTime = new DateTime($request->input('time'));
    //     if ($schedule->Shift == 'morning shift:from 9 AM to 3 PM') {
    //         $start = new DateTime('09:00');
    //         $end = new DateTime('15:00');
    //     } else {
    //         $start = new DateTime('15:00');
    //         $end = new DateTime('21:00');
    //     }

    //     if ($userTime < $start || $userTime >= $end) {
    //         return response()->json([
    //             'message' => 'this time not available in this schedule',
    //         ], 400);
    //     }

    //     if ($date->toDateString() >= $schedule->start_leave_date && $date->toDateString() <= $schedule->end_leave_date) {
    //         if ($time->format('H:i') >= $schedule->start_leave_time && $time->format('H:i') <= $schedule->end_leave_time) {
    //             return response()->json([
    //                 'message' => 'this doctor is not available in this date '
    //             ], 400);
    //         }
    //     }

    //     $newTimeFormatted = Carbon::parse($request->time);
    //     if ($appointmentsNum == $numOfPeopleInHour) $timeSelected = $newTimeFormatted->addHours(1)->toTimeString();
    //     else $timeSelected = $timeFormatted;

    //     $numOfPatientReservation = Appointment::where('patient_id', $patient->id)
    //         ->where('schedule_id', $schedule->id)
    //         ->where('reservation_date', $dateFormatted)
    //         ->where('status', 'pending')
    //         ->count();

    //     if ($numOfPatientReservation > 0) {
    //         return response()->json([
    //             'message' => 'You already appointment at this date'
    //         ], 400);
    //     }

    //     if ($appointmentsNum < $numOfPeopleInHour) {
    //         $appointment = Appointment::create([
    //             'patient_id' => $patient->id,
    //             'schedule_id' => $schedule->id,
    //             'timeSelected' => $timeSelected,
    //             'reservation_date' => $dateFormatted,
    //         ]);

    //         return response()->json($appointment, 200);
    //     }

    //     return response()->json(['message' => 'this time is full'], 400);
    // }

    public function addManualReservation(Request $request)
    {
        $user = Auth::user();

        //check the auth
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

        if ($request->has('child_id')) {
            $parent = Patient::where('user_id', $user->id)->first();
            if(!$parent) {
                return response()->json(['message' => 'Parent not found'], 404);
            }

            $child = Patient::where('id', $request->child_id)->where('parent_id', $parent->id)->first();
            if(!$child) return response()->json(['message' => 'child not found'], 404);

            $patient = $child;
        } else {
            $patient = Patient::where('user_id', $user->id)->first();
        }
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

            $lastQueueNumber = Appointment::where('schedule_id',  $schedule->id)
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
            ]);

            if($appointment->appointment_type == 'vaccination') {
                // لازم يعطيني كمان السجل يلي بدي اعمله ال appointment
                $vaccinationRecord = VaccinationRecord::with('vaccine')->where('id', $request->record_id)->first();
                if(!$vaccinationRecord) return response()->json(['message' => 'record not found'], 404);

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
        $user = Auth::user();

        //check the auth
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

        if ($request->has('child_id')) {
            $parent = Patient::where('user_id', $user->id)->first();
            if(!$parent) {
                return response()->json(['message' => 'Parent not found'], 404);
            }

            $child = Patient::where('id', $request->child_id)->where('parent_id', $parent->id)->first();
            if(!$child) return response()->json(['message' => 'child not found'], 404);

            $patient = $child;
        } else {
            $patient = Patient::where('user_id', $user->id)->first();
        }
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
            ]);

            if($appointment->appointment_type == 'vaccination') {
                // لازم يعطيني كمان السجل يلي بدي اعمله ال appointment
                $vaccinationRecord = VaccinationRecord::with('vaccine')->where('id', $request->record_id)->first();
                if(!$vaccinationRecord) return response()->json(['message' => 'record not found'], 404);

                $vaccinationRecord->appointment_id = $appointment->id;
                $vaccinationRecord->save();

                $appointment->expected_price += $vaccinationRecord->vaccine->price;
                $appointment->save();
            }

            return response()->json($appointment, 200);
        }

        return response()->json(['message' => 'this time is full'], 400);
    }

    public function addReservation(Request $request)
    {
        $user = Auth::user();

        //check the auth
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

        $doctor = Doctor::findOrFail($request->doctor_id);

        if ($doctor->booking_type == 'manual') {

            $validator = Validator::make($request->all(), [
                'time' => 'required|date_format:H:i',
                'child_id' => 'sometimes|exists:patients,id',
                'appointment_type' => ['required_with:child_id', Rule::in(['visit', 'vaccination'])],
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
                'child_id' => 'sometimes|exists:patients,id',
                'appointment_type' => ['required_with:child_id', Rule::in(['visit', 'vaccination'])],
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


    public function editReservation(Request $request)
    {
        $user = Auth::user(); // 

        //check the auth
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

        if ($request->has('child_id')) {
            $patient = Patient::where('id', $request->child_id)->first();
        } else {
            $patient = Patient::where('user_id', $user->id)->first();
        }
        if (!$patient) return response()->json(['message' => 'Patient Not Found'], 404);

        // front should give me the old time and date

        $validator = Validator::make($request->all(), [
            'new_date' => 'required|date_format:d/m/y',
            'new_time' => 'required|date_format:H:i'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }


        $dateFormatted = Carbon::createFromFormat('d/m/y', $request->new_date)->format('Y-m-d');
        $timeFormatted = Carbon::createFromFormat('H:i', $request->new_time)->format('H:i:s');

        // $oldDateFormatted = Carbon::createFromFormat('d/m/y', $request->old_date)->format('Y-m-d');
        // $oldTimeFormatted = Carbon::createFromFormat('H:i', $request->old_time)->format('H:i:s');

        $new_date = Carbon::createFromFormat('d/m/y', $request->new_date);
        $new_time = Carbon::createFromFormat('H:i', $request->new_time);
        $new_day = $new_date->format('l');

        $schedule = Schedule::where('doctor_id', $request->doctor_id)
            ->where('status', 'notAvailable')
            ->where('day', $new_day)
            ->first();

        if (!$schedule) return response()->json(['message' => 'schedule not found'], 404);

        $userTime = new DateTime($request->input('new_time'));
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

        if ($new_date->toDateString() >= $schedule->start_leave_date && $new_date->toDateString() <= $schedule->end_leave_date) {
            if ($new_time->format('H:i') >= $schedule->start_leave_time && $new_time->format('H:i') <= $schedule->end_leave_time) {
                return response()->json([
                    'message' => 'this doctor is not available in this date '
                ], 400);
            }
        }

        // delete old reservation 
        $oldReservation = Appointment::where('id', $request->appointment_id)
            ->where('status', 'pending')
            ->first();
        // return $oldReservation;
        if (!$oldReservation) return response()->json(['message' => 'reservation not found'], 404);

        $oldReservation->delete();

        $appointmentsNum = Appointment::where('schedule_id', $schedule->id)
            ->where('reservation_date', $dateFormatted)
            ->where('status', 'pending')
            ->where('timeSelected', $timeFormatted)
            ->count();

        $visitTime = Doctor::where('id', $request->doctor_id)->select('average_visit_duration')->first()->average_visit_duration;
        if (!$visitTime) return response()->json(['message' => 'Visit Time Not Availabe'], 404);

        $visitTime = (float) $visitTime;
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
            ]);

            return response()->json($appointment, 200);
        }

        return response()->json(['message' => 'this time is full'], 400);
    }

    public function cancelReservation(Request $request) {
        $user = Auth::user(); // 

        //check the auth
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

        $reservation = Appointment::with('patient', 'schedule.doctor')->where('id', $request->reservation_id)->first();
        if(!$reservation) return response()->json(['message' => 'reservation not found'], 404);

        $patient = $reservation->patient;

        if ($reservation->payment_status == 'paid') {
            try {
            
                $patient->wallet += $reservation->paid_price;
                $patient->save();

                $reservation->paid_price = 0;
                $reservation->save();

                $clinic = Clinic::where('id', $reservation->doctor->clinic_id)->first();
                if (!$clinic) return response()->json(['messsage' => 'clinic not found'], 404);

                $clinic->money -= $reservation->paid_price;
                $clinic->save();
            } catch (\Exception $e) {
                Log::error("Stripe refund failed for reservation ID {$reservation->id}: " . $e->getMessage());
            }
        }

        $reservation->update([
            'status' => 'cancelled',
        ]);
        $reservation->save();

        $doctor = $reservation->schedule->doctor;

        if($doctor->booking_type == 'auto') {
            $reservationTime = Carbon::createFromFormat('H:i:s', $reservation->timeSelected);
            $reservationDate = $reservation->reservation_date;

            $visitTime = $reservation->schedule->doctor->average_visit_duration;
            $visitTime = (float) $visitTime;
            $numOfPeopleInHour = floor(60 / $visitTime);

            $startHour = $reservationTime->copy()->startOfHour();
            $cancelledTime = $reservationTime->format('H:i:s');

            $currentCountInHour = Appointment::where('schedule_id', $reservation->schedule_id)
            ->where('reservation_date', $reservationDate)
            ->where('status', 'pending')
            ->whereBetween('timeSelected', [
                $startHour->format('H:i:s'),
                $startHour->copy()->addHour()->subSecond()->format('H:i:s'),
            ])
            ->count();

            $availableSlots = $numOfPeopleInHour - $currentCountInHour;

            $upcomingAppointments = Appointment::where('schedule_id', $reservation->schedule_id)
            ->where('reservation_date', $reservationDate)
            ->where('status', 'pending')
            ->where('timeSelected', '>', $cancelledTime)
            ->orderBy('created_at', 'asc')
            ->get();

            $currentHour = $startHour->copy();

            foreach ($upcomingAppointments as $appointment) {

                $currentCountInHour = Appointment::where('schedule_id', $reservation->schedule_id)
                ->where('reservation_date', $reservationDate)
                ->where('status', 'pending')
                ->where('timeSelected', $currentHour->format('H:i:s'))
                ->count();

                $availableSlots = $numOfPeopleInHour - $currentCountInHour;


                while ($availableSlots <= 0) {

                    $currentHour->addHour();

                    $currentCountInNextHour = Appointment::where('schedule_id', $reservation->schedule_id)
                        ->where('reservation_date', $reservationDate)
                        ->where('status', 'pending')
                        ->where('timeSelected', $currentHour->format('H:i:s'))
                        ->count();

                    $availableSlots = $numOfPeopleInHour - $currentCountInNextHour;
                }   

                $appointment->timeSelected = $currentHour->format('H:i:s');
                $appointment->save();

                $availableSlots--;
            }

        }

        return response()->json(['message' => 'reservation cancelled successfully'], 200);
    }
}

<?php

namespace App\Http\Controllers\Patient\Reservation;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Schedule;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;

use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\isNull;

class ReservationController extends Controller
{
    public function showDoctorWorkDays(Request $request) {
        //$request = department(clininc_id), doctor,
        $user = Auth::user();

         //check the auth
         if(!$user) {
            return response()->json([
                'message' => 'unauthorized'
            ],401);
        }

        if(!$user->role == 'patient') {
            return response()->json([
                'message' => 'you dont have permission'
            ],401);
        }

        $validator = Validator::make($request->all(), [
            'clinic_id' => 'required|exists:clinics,id',
            'doctor_id' => 'required|exists:doctors,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
               'message' =>  $validator->errors()->all()
            ], 400);
        }

        $schedules = Schedule::where('doctor_id',$request->doctor_id)->get();
        $workingDays = $schedules->pluck('day');

        $startDate = Carbon::today();
        $endDate = Carbon::today()->addMonth();
        $period = CarbonPeriod::create($startDate, $endDate);
        
        $availableDates = collect();

        foreach($period as $date) {
            if ($workingDays->contains($date->format('l'))) {
                $availableDates->push($date->toDateString());
            }
        }

        foreach($availableDates as $key => $availableDate) {
            foreach($schedules as $schedule) {
                $date = $availableDate;
                $startLeaveDate = $schedule->start_leave_date;
                $endLeaveDate = $schedule->end_leave_date;
                $startLeaveTime =  $schedule->start_leave_time;
                $endLeaveTime =  $schedule->end_leave_time;

                if($date >= $startLeaveDate && $date <= $endLeaveDate) {
                    if($schedule->Shift == 'morning shift:from 9 AM to 3 PM') {
                        $start = Carbon::createFromTime(9,0,0)->format('H:i:s');
                        $end = Carbon::createFromTime(15,0,0)->format('H:i:s');
                    }else {
                       $start = Carbon::createFromTime(15,0,0)->format('H:i:s');
                        $end = Carbon::createFromTime(21,0,0)->format('H:i:s');

                    }
                    if($startLeaveTime == null && $endLeaveTime == null) {
                        $availableDates->forget($key);
                        continue;
                    }
                    if($startLeaveTime == $start && $endLeaveTime == $end) {
                        $availableDates->forget($key);
                    }
                }
            }
        }

        return response()->json([
            'available_dates' => $availableDates->values()
        ], 200);

    }

    public function showTimes(Request $request) {
        $user = Auth::user();

        //check the auth
        if(!$user) {
           return response()->json([
               'message' => 'unauthorized'
           ],401);
       }

       if(!$user->role == 'patient') {
           return response()->json([
               'message' => 'you dont have permission'
           ],401);
       }

       $validator = Validator::make($request->all(), [
        
        'clinic_id' => 'required|exists:clinics,id',
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
        
        $schedule = Schedule::where('doctor_id',$request->doctor_id)->where('day',$day)->first();

        $mysqlDate = Carbon::createFromFormat('d/m/y', $request->date)->format('Y-m-d');

        $appointments = Appointment::where('schedule_id',$schedule->id)
            -> where('reservation_date',$mysqlDate)
            ->get();
        
        $visitTime = Doctor::where('id',$request->doctor_id)->select('average_visit_duration')->first()->average_visit_duration;
        $visitTime = (float) $visitTime; 
        $numOfPeopleInHour = floor(60 / $visitTime); 
        
        // filter the times 
        $available_times = [];

        if($schedule->Shift == 'morning shift:from 9 AM to 3 PM') {
            $start = new DateTime('09:00');
            $end = new DateTime('15:00');
        }
        else {
            $start = new DateTime('15:00');
            $end = new DateTime('21:00');
        }
        
        $interval = new DateInterval('PT1H');
        $period = new DatePeriod($start, $interval, $end);

        foreach($period as $time) {
        
            $timeFormatted = $time->format('H:i:s');
            $count = $appointments->where('timeSelected', $timeFormatted)->count();
            if($date->toDateString() >= $schedule->start_leave_date && $date->toDateString() <= $schedule->end_leave_date) {
                if($time->format('H:i') >= $schedule->start_leave_time && $time->format('H:i') <= $schedule->end_leave_time){
                    continue;
                }
            }
            if ($count < $numOfPeopleInHour) {
                $available_times[] = $time->format('H:i');
            }
        }

        if($available_times == []) {
            return response()->json([
                'message' => 'this doctor is not available in this date'
            ], 400);
        }

        return response()->json($available_times,200);

    }

    public function addReservation(Request $request) {
        $user = Auth::user();

         //check the auth
         if(!$user) {
            return response()->json([
                'message' => 'unauthorized'
            ],401);
        }

        if(!$user->role == 'patient') {
            return response()->json([
                'message' => 'you dont have permission'
            ],401);
        }

        $patient = Patient::where('user_id',$user->id)->first();

        $validator = Validator::make($request->all(), [
            'clinic_id' => 'required|exists:clinics,id',
            'doctor_id' => 'required|exists:doctors,id',
            'date' => 'required|date_format:d/m/y',
            'time' => 'required|date_format:H:i'
        ]);


        if ($validator->fails()) {
            return response()->json([
               'message' =>  $validator->errors()->all()
            ], 400);
        }

        $dateFormatted = Carbon::createFromFormat('d/m/y', $request->date)->format('Y-m-d');
        $timeFormatted = Carbon::parse($request->time)->format('H:i:s'); 

        $date = Carbon::createFromFormat('d/m/y', $request->date);
        $time = Carbon::createFromFormat('H:i', $request->time);
        $day = $date->format('l');
        
        $schedule = Schedule::where('doctor_id',$request->doctor_id)
            ->where('day',$day)
            ->first();
        $doctor = Doctor::where('id',$request->doctor_id)->first();

        $appointmentsNum = Appointment::where('schedule_id',$schedule->id)
            -> where('reservation_date',$dateFormatted)
            ->where('status', 'pending')
            ->where('timeSelected',$timeFormatted)
        ->count();

        $visitTime = Doctor::where('id',$request->doctor_id)->select('average_visit_duration')->first()->average_visit_duration;
        $visitTime = (float) $visitTime; 

        if($visitTime == 0 || $doctor->status == 'notAvailable') {
            return response()->json('this doctor not available', 503);
        }

        $numOfPeopleInHour = floor(60 / $visitTime); 

        $userTime = new DateTime($request->input('time'));
        if($schedule->Shift == 'morning shift:from 9 AM to 3 PM') {
            $start = new DateTime('09:00');
            $end = new DateTime('15:00');
        }
        else {
            $start = new DateTime('15:00');
            $end = new DateTime('21:00');
        }

        if ($userTime < $start || $userTime >= $end) {
            return response()->json([
                'message' => 'this time not available in this schedule',
            ],400);
        }

        if($date->toDateString() >= $schedule->start_leave_date && $date->toDateString() <= $schedule->end_leave_date) {
            if($time->format('H:i') >= $schedule->start_leave_time && $time->format('H:i') <= $schedule->end_leave_time){
                return response()->json([
                'message' => 'this doctor is not available in this date '
                ], 400);
            }
        }

        $newTimeFormatted = Carbon::parse($request->time);
        if($appointmentsNum == $numOfPeopleInHour) $timeSelected = $newTimeFormatted->addHours(1)->toTimeString();
        else $timeSelected = $timeFormatted;

        if($appointmentsNum < $numOfPeopleInHour) {
            $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'schedule_id' => $schedule->id,
            'timeSelected' => $timeSelected,
            'reservation_date' => $dateFormatted,
            ]);

            return response()->json($appointment,200);
        }

        return response()->json('this time is full', 400);

    }

    public function editReservation(Request $request) {
        $user = Auth::user(); // 

         //check the auth
         if(!$user) {
            return response()->json([
                'message' => 'unauthorized'
            ],401);
        }

        if(!$user->role == 'patient') {
            return response()->json([
                'message' => 'you dont have permission'
            ],401);
        }

        $patient = Patient::where('user_id',$user->id)->first();

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

        $schedule = Schedule::where('doctor_id',$request->doctor_id)
            ->where('day',$new_day)
        ->first();


        $userTime = new DateTime($request->input('new_time'));
        if($schedule->Shift == 'morning shift:from 9 AM to 3 PM') {
            $start = new DateTime('09:00');
            $end = new DateTime('15:00');
        }
        else {
            $start = new DateTime('15:00');
            $end = new DateTime('21:00');
        }

        if ($userTime < $start || $userTime >= $end) {
            return response()->json([
                'message' => 'this time not available in this schedule',
            ],400);
        }

        if($new_date->toDateString() >= $schedule->start_leave_date && $new_date->toDateString() <= $schedule->end_leave_date) {
            if($new_time->format('H:i') >= $schedule->start_leave_time && $new_time->format('H:i') <= $schedule->end_leave_time){
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

        $oldReservation->delete();

        $appointmentsNum = Appointment::where('schedule_id',$schedule->id)
            -> where('reservation_date',$dateFormatted)
            ->where('status', 'pending')
            ->where('timeSelected',$timeFormatted)
        ->count();

        $visitTime = Doctor::where('id',$request->doctor_id)->select('average_visit_duration')->first()->average_visit_duration;
        $visitTime = (float) $visitTime; 
        $numOfPeopleInHour = floor(60 / $visitTime); 

        $newTimeFormatted = Carbon::parse($request->time);
        if($appointmentsNum == $numOfPeopleInHour) $timeSelected = $newTimeFormatted->addHours(1)->toTimeString();
        else $timeSelected = $timeFormatted;

        if($appointmentsNum < $numOfPeopleInHour) {
            $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'schedule_id' => $schedule->id,
            'timeSelected' => $timeSelected,
            'reservation_date' => $dateFormatted,
            ]);

            return response()->json($appointment,200);
        }

        return response()->json('this time is full', 400);
    }

    public function cancelReservation(Request $request) {
        $user = Auth::user(); // 

         //check the auth
         if(!$user) {
            return response()->json([
                'message' => 'unauthorized'
            ],401);
        }

        if(!$user->role == 'patient') {
            return response()->json([
                'message' => 'you dont have permission'
            ],401);
        }

        $patient = Patient::where('user_id',$user->id)->first();

        $reservation = Appointment::where('id',$request->reservation_id)->first();

        $reservation->update([
            'status' => 'canceled',
        ]);
        $reservation->save();

        return response()->json('reservation canceled successfully', 200);
    }
}

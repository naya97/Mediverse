<?php

namespace App\Http\Controllers\Patient\Reservation;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Schedule;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;

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
            return response()->json(['error' => $validator->errors()], 400);
        }

        $schedule [] = Schedule::where('doctor_id',$request->doctor_id)->get();

        return response()->json($schedule,200);

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
            return response()->json(['error' => $validator->errors()], 400);
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
            $start = new DateTime('09:00 AM');
            $end = new DateTime('03:00 PM');
        }
        else {
            $start = new DateTime('03:00 PM');
            $end = new DateTime('09:00 PM');
        }
        
        $interval = new DateInterval('PT1H');
        $period = new DatePeriod($start, $interval, $end);

        foreach($period as $time) {
        
            $timeFormatted = $time->format('h:i:s');
            $count = $appointments->where('timeSelected', $timeFormatted)->count();
            if ($count < $numOfPeopleInHour) {
                $available_times[] = $time->format('h:i A');
            }
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
            'time' => 'required|date_format:h:i A'
        ]);


        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $dateFormatted = Carbon::createFromFormat('d/m/y', $request->date)->format('Y-m-d');
        $timeFormatted = Carbon::parse($request->time)->format('h:i:s'); 

        $date = Carbon::createFromFormat('d/m/y', $request->date);
        $day = $date->format('l');
        
        $schedule = Schedule::where('doctor_id',$request->doctor_id)
            ->where('day',$day)
            ->first();
        //$mysqlDate = Carbon::createFromFormat('d/m/y', $request->date)->format('Y-m-d');

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
            'clinic_id' => 'required|exists:clinics,id',
            'doctor_id' => 'required|exists:doctors,id',
            'new_date' => 'required|date_format:d/m/y',
            'new_time' => 'required|date_format:h:i A'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }


    $dateFormatted = Carbon::createFromFormat('d/m/y', $request->new_date)->format('Y-m-d');
    $timeFormatted = Carbon::createFromFormat('h:i A', $request->new_time)->format('h:i:s'); 

    $oldDateFormatted = Carbon::createFromFormat('d/m/y', $request->old_date)->format('Y-m-d');
    $oldTimeFormatted = Carbon::createFromFormat('h:i A', $request->old_time)->format('h:i:s');

        $new_date = Carbon::createFromFormat('d/m/y', $request->new_date);
        $new_day = $new_date->format('l');

        // delete old reservation 
        $oldReservation = Appointment::where('reservation_date', $oldDateFormatted)
            ->where('timeSelected', $oldTimeFormatted)
            ->where('status', 'pending')
            ->first();
        // return $oldReservation;

        $oldReservation->delete();

        $schedule = Schedule::where('doctor_id',$request->doctor_id)
            ->where('day',$new_day)
            ->first();

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

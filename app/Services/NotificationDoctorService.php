<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Doctor;
use App\Models\Schedule;
use App\Models\Appointment;
use App\Notifications\ShiftCompleted;
use App\Services\FirebaseService;

class NotificationDoctorService
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    public function notifyDoctorsAfterShift()
    {
        $now = Carbon::now();
        $todayName = $now->format('l');
        $todayDate = $now->toDateString();

        if (!in_array($now->format('H:i'), ['15:00', '21:00'])) {
            return;
        }

        $shiftType = $now->hour === 15
            ? 'morning shift:from 9 AM to 3 PM'
            : 'evening shift:from 3 PM to 9 PM';

        $schedules = Schedule::with('doctor.user')
            ->where('day', $todayName)
            ->where('Shift', $shiftType)
            ->where('status', 'notAvailable')
            ->get();

        foreach ($schedules as $schedule) {
            $doctor = $schedule->doctor;
            $user = $doctor->user;

            if (!$user || !$user->fcm_token) continue;

            $visitsCount = Appointment::where('doctor_id', $doctor->id)
                ->whereDate('created_at', $todayDate)
                ->count();

            $title = 'Shift Completed';
            $body = "Dr. {$user->first_name}, your $shiftType on $todayName has ended. You treated $visitsCount patient(s) today.";

            $this->firebase->sendNotification($user->fcm_token, $title, $body,);
            $user->notify(new ShiftCompleted([
                'shift' => $shiftType,
                'day' => $todayName,
                'visits' => $visitsCount,
            ]));
        }
    }
}

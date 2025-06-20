<?php

// use Carbon\Carbon;
// use App\Models\Doctor;
// use App\Models\Shift;
// use App\Models\Visit; // أو اسم جدول زيارات الطبيب
// use App\Services\FirebaseService;

// class NotificationAfterShiftService
// {
//     protected $firebase;

//     public function __construct(FirebaseService $firebase)
//     {
//         $this->firebase = $firebase;
//     }

//     public function notifyDoctorsAfterShift()
//     {
//         $now = Carbon::now();
//         $today = $now->toDateString();

//         if (!in_array($now->format('H:i'), ['15:00', '21:00'])) {
//             return;
//         }

//         $shiftType = $now->hour === 15 ? 'morning' : 'evening';

//         // جبلي كل الشيفتات بهاليوم وهالنوع
//         $shifts = Shift::with('doctor.user')
//             ->where('date', $today)
//             ->where('type', $shiftType)
//             ->get();

//         foreach ($shifts as $shift) {
//             $doctor = $shift->doctor;
//             $user = $doctor->user;

//             if (!$user || !$user->fcm_token) continue;

//             // احسب عدد المرضى بهاليوم
//             $visitsCount = Visit::where('doctor_id', $doctor->id)
//                 ->whereDate('created_at', $today)
//                 ->count();

//             $title = 'انتهاء الشيفت';
//             $body = "تم إنهاء شيفتك الـ$shiftType اليوم. عدد المرضى الذين تم علاجهم: $visitsCount.";

//             $this->firebase->sendNotification($user->fcm_token, $title, $body);
//         }
//     }
// }

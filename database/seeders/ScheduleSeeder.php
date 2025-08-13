<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Schedule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $shifts = [
            'morning shift:from 9 AM to 3 PM',
            'evening shift:from 3 PM to 9 PM',
        ];

        $doctors = Doctor::with('clinic')->get();

         foreach ($doctors as $doctor) {
            // نعطي لكل دكتور عدد عشوائي من الشفتات (مثلاً 2 شفتات بالأسبوع)
            $numShifts = rand(1, 3);

            $assigned = [];

            for ($i = 0; $i < $numShifts; $i++) {
                $day = $days[array_rand($days)];
                $shift = $shifts[array_rand($shifts)];

                // نتأكد ما في دكتور تاني بنفس العيادة على نفس اليوم والشفت
                $exists = Schedule::where('clinic_id', $doctor->clinic_id)
                    ->where('day', $day)
                    ->where('Shift', $shift)
                    ->exists();

                // إذا وجدنا جدول بنفس اليوم والشفت نعيد اختيار أو نتخطى هذا الجدول
                $attempts = 0;
                while ($exists && $attempts < 5) {
                    $day = $days[array_rand($days)];
                    $shift = $shifts[array_rand($shifts)];
                    $exists = Schedule::where('clinic_id', $doctor->clinic_id)
                        ->where('day', $day)
                        ->where('Shift', $shift)
                        ->exists();
                    $attempts++;
                }

                if ($exists) {
                    // بعد 5 محاولات، نتخطى هذا الشفت
                    continue;
                }

                // نضيف جدول جديد
                Schedule::create([
                    'clinic_id'       => $doctor->clinic_id,
                    'doctor_id'       => $doctor->id,
                    'day'             => $day,
                    'Shift'           => $shift,
                    'start_leave_date' => null,
                    'end_leave_date'   => null,
                    'start_leave_time' => null,
                    'end_leave_time'   => null,
                    'status'          => 'available',
                ]);
            }
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patients = Patient::all();
        $schedules = Schedule::all();

        if ($patients->isEmpty() || $schedules->isEmpty()) {
            $this->command->warn('Make sure patients and schedules exist before seeding appointments.');
            return;
        }

        foreach (range(1, 12) as $month) {
            $year = now()->year;

            foreach (range(1, 10) as $i) {
                $appointment = new Appointment();
                $appointment->patient_id = $patients->random()->id;
                $appointment->schedule_id = $schedules->random()->id;
                $appointment->timeSelected = Carbon::create($year, $month, rand(1, 28), rand(8, 17))->format('H:i');
                $appointment->reservation_date = Carbon::create($year, $month, rand(1, 28));
                $appointment->status = 'visited';
                $appointment->price = rand(50, 300);
                $appointment->payment_status = 'paid';
                $appointment->reminder_sent = (bool) rand(0, 1);
                $appointment->save();
            }
        }
    }
}

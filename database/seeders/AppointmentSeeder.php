<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patients = Patient::all();
        $schedules = Schedule::where('status', 'notAvailable')->get();

        if ($patients->isEmpty() || $schedules->isEmpty()) {
            $this->command->warn('Make sure patients and available schedules exist before seeding appointments.');
            return;
        }

        $statusOptions = ['visited', 'cancelled', 'pending'];
        $appointmentTypeOptions = ['visit', 'vaccination'];
        $paymentStatusOptions = ['pending', 'paid', 'cancelled'];

        $totalAppointments = 1000;

        foreach (range(1, $totalAppointments) as $i) {
            $schedule = $schedules->random();

            $timeSelected = $this->randomTimeForShift($schedule->Shift);


            if (rand(1, 100) <= 35) {
                $reservationDate = Carbon::create(2025, 8, 30)->toDateString();
            } else {
                $year = now()->year;
                $reservationDate = Carbon::create($year, rand(1, 12), rand(1, 28))->toDateString();
            }

            $createdAt = Carbon::now()->subDays(rand(0, 60));
            $updatedAt = (clone $createdAt)->addMinutes(rand(0, 1440));

            $status = $statusOptions[array_rand($statusOptions)];
            $appointmentType = $appointmentTypeOptions[array_rand($appointmentTypeOptions)];

            if ($appointmentType === 'vaccination') {
                $eligiblePatients = $patients->whereNotNull('parent_id');
            } else {
                $eligiblePatients = $patients;
            }

            if ($eligiblePatients->isEmpty()) {
                continue;
            }

            $selectedPatient = $eligiblePatients->random();

            if ($appointmentType === 'visit') {
                $expectedPrice = rand(50, 150);
            } else {
                $expectedPrice = rand(100, 300);
            }

            if ($status === 'visited') {
                $payment_status = 'paid';
                $paidPrice = $expectedPrice;
            } elseif ($status === 'cancelled') {
                $payment_status = 'cancelled';
                $paidPrice = 0;
            } else {
                $payment_status = $paymentStatusOptions[array_rand($paymentStatusOptions)];
                $paidPrice = ($payment_status === 'paid') ? $expectedPrice : 0;
            }

            Appointment::create([
                'patient_id'       => $selectedPatient->id,
                'schedule_id'      => $schedule->id,
                'timeSelected'     => $timeSelected,
                'reservation_date' => $reservationDate,
                'status'           => $status,
                'expected_price'   => $expectedPrice,
                'paid_price'       => $paidPrice,
                'payment_status'   => $payment_status,
                'reminder_offset'  => 12,
                'reminder_sent'    => (bool) rand(0, 1),
                'appointment_type' => $appointmentType,
                'queue_number'     => null,
                'is_referral'      => false,
                'referring_doctor' => null,
                'parent_id'        => null,
                'created_at'       => $createdAt,
                'updated_at'       => $updatedAt,
            ]);
        }
    }

    private function randomTimeForShift(string $shift): string
    {
        if (str_contains($shift, 'morning')) {
            $start = 9;
            $end = 15;
        } else {
            $start = 15;
            $end = 21;
        }

        $hour = rand($start, $end - 1);
        return sprintf('%02d:%02d:00', $hour, '00');
    }
}

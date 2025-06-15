<?php

namespace App;

use App\Models\Appointment;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Refund;
use Stripe\Stripe;

trait CancelAppointmentsTrait
{

    public function editDoctorSchedule(Request $request, $doctor)
    {
        // $schedule = Schedule::where('doctor_id',$request->doctor_id)->where('day',$request->scheduleDay)->first();

        $start_leave_date = Carbon::createFromFormat('d-m-Y', $request->start_leave_date)->format('Y-m-d');
        $end_leave_date = Carbon::createFromFormat('d-m-Y', $request->end_leave_date)->format('Y-m-d');
        $start_leave_time = Carbon::createFromFormat('H:i', $request->start_leave_time)->format('H:i');
        $end_leave_time = Carbon::createFromFormat('H:i', $request->end_leave_time)->format('H:i');


        $date = Carbon::createFromFormat('d-m-Y', $request->start_leave_date);
        $day = $date->format('l');
        $schedule = Schedule::where('doctor_id', $doctor)->where('day', $day)->first();
        if (!$schedule) return response()->json(['message' => 'schedule not fount'], 404);

        $schedule->update([
            'start_leave_date' => $start_leave_date,
            'end_leave_date' => $end_leave_date,
            'start_leave_time' => $start_leave_time,
            'end_leave_time' => $end_leave_time,
        ]);
        $schedule->save();

        $appointments = Appointment::whereBetween('reservation_date', [$start_leave_date, $end_leave_date])
            ->whereBetween('timeSelected', [$start_leave_time, $end_leave_time])
            ->where('status', 'pending')
            ->get();

        // return $appointments;

        Stripe::setApiKey(env('STRIPE_SECRET'));

        foreach ($appointments as $appointment) {

            if ($appointment->payment_status == 'paid' && $appointment->payment_intent_id) {
                try {
                    Refund::create([
                        'payment_intent' => $appointment->payment_intent_id,
                    ]);
                } catch (\Exception $e) {
                    Log::error("Stripe refund failed for appointment ID {$appointment->id}: " . $e->getMessage());
                }
            }

            $appointment->status = 'cancelled';
            $appointment->save();
        }

        return response()->json([
            'message' => 'schedule successfully updated',
            'message' => 'Appointments canceled and refunds processed (if applicable).',
            'data' => $schedule,
        ], 200);
    }
    /////
    public function cancelAnAppointment(Request $request)
    {

        $reservation = Appointment::where('id', $request->reservation_id)->first();
        if (!$reservation) return response()->json(['message' => 'Reservaion Not Found'], 404);


        Stripe::setApiKey(env('STRIPE_SECRET'));

        if ($reservation->payment_status == 'paid' && $reservation->payment_intent_id) {
            try {
                Refund::create([
                    'payment_intent' => $reservation->payment_intent_id,
                ]);
            } catch (\Exception $e) {
                Log::error("Stripe refund failed for reservation ID {$reservation->id}: " . $e->getMessage());
            }
        }

        $reservation->update([
            'status' => 'cancelled',
        ]);
        $reservation->save();

        return response()->json(['message' => 'reservation canceled successfully'], 200);
    }
}

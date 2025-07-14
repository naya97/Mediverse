<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\AppointmentReminder;
use App\Services\FirebaseService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send appointment reminders via Firebase';

    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        parent::__construct();
        $this->firebase = $firebase;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $appointments = Appointment::with('patient.user')
            ->where('reminder_sent', false)
            ->where('status', 'pending')
            ->get();

        foreach ($appointments as $appointment) {
            $appointmentDateTime = Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $appointment->reservation_date . ' ' . $appointment->timeSelected
            );

            $reminderTime = $appointmentDateTime->copy()->subHours($appointment->reminder_offset);

            if (
                $now->greaterThanOrEqualTo($reminderTime) &&
                $now->lessThan($reminderTime->copy()->addMinutes(60))
            ) {
                $user = null;

                if ($appointment->patient->parent_id != null) {
                    $parentPatient = Patient::find($appointment->patient->parent_id);
                    $user = $parentPatient ? $parentPatient->user : null;
                } else {
                    $user = $appointment->patient->user;
                }

                $token = $user?->fcm_token ?? null;

                if ($token) {
                    $title = 'appointment reminder';
                    $body = 'You have an appointment ' . $appointmentDateTime->format('Y-m-d H:i');
                    $data = [
                        'appointment_id' => $appointment->id,
                        'type' => 'appointment_reminder',
                    ];

                    $this->firebase->sendNotification($token, $title, $body, $data);
                    $user->notify(new AppointmentReminder($appointment));

                    Log::info("Appointment reminder sent successfully", [
                        'user_id' => $user->id,
                        'appointment_id' => $appointment->id,
                        'token' => $token,
                        'send_time' => now()->toDateTimeString(),
                    ]);

                    $appointment->reminder_sent = true;
                    $appointment->save();

                    $this->info("reminder sent successfully for patient ID: {$appointment->patient->id}");
                } else {
                    Log::warning("FCM token not found", [
                        'user_id' => $user?->id,
                        'appointment_id' => $appointment->id,
                    ]);

                    $this->warn("there is no token for this patient ID: {$appointment->patient->id}");
                }
            }
        }
    }


}

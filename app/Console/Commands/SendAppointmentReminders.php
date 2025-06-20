<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Notifications\AppointmentReminder;
use App\Services\FirebaseService;
use Carbon\Carbon;
use Illuminate\Console\Command;

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

        foreach($appointments as $appointment) {
            $appointmentDateTime = Carbon::createFromFormat(
                'Y-m-d H:i:s', 
                $appointment->reservation_date. ' ' .$appointment->timeSelected
            );

            $reminderTime = $appointmentDateTime->copy()->subHours($appointment->reminder_offset);

            if (
                $now->greaterThanOrEqualTo($reminderTime) &&
                $now->lessThan($reminderTime->copy()->addMinutes(60))
            ) {
                $token = $appointment->patient->user->fcm_token ?? null;
                if($token) {
                    $title = 'appointment reminder';
                    $body = 'You have an appointment ' .$appointmentDateTime->format('Y-m-d H:i');
                    $data = [
                        'appointment_id' => $appointment->id,
                        'type' => 'appointment_reminder',
                    ];
                    $this->firebase->sendNotification($token, $title, $body, $data); 
                    $appointment->patient->user->notify(new AppointmentReminder($appointment));
                }

                $appointment->reminder_sent = true;
                $appointment->save();

                $this->info("reminder sent successfully for patient ID: {$appointment->patient->id}");
            }
            else {
                $this->warn("there is no token for this patient ID: {$appointment->patient->id}");
            }
        }
    }
}

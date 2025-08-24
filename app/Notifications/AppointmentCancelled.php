<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentCancelled extends Notification
{
    use Queueable;

    protected $appointment;

    /**
     * Create a new notification instance.
     */
    public function __construct($appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $doctor = $this->appointment->schedule?->doctor;
        return [
            'title' => 'sorry, your appointment canceled',
            'body' => 'date : ' . $this->appointment->reservation_date . ', doctor : ' . $doctor->first_name . ' ' . $doctor->last_name,
            'appointment_id' => $this->appointment->id,
            'reservation_date' => $this->appointment->reservation_date,
            'timeSelected' => $this->appointment->timeSelected,
            'doctor_name' => $doctor ? $doctor->first_name . ' ' . $doctor->last_name : 'Unknown',
            'type' => 'cancel_appointment',
        ];
    }
}

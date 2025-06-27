<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentVisited extends Notification
{
    use Queueable;
    protected $appointmentData;

    public function __construct($appointmentData)
    {
        $this->appointmentData = $appointmentData;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Appointment Visited',
            'body' => 'Your recent appointment has been marked as visited.',
            'type' => 'appointment_visited',
            'appointment_id' => $this->appointmentData['appointment_id'],
        ];
    }
}

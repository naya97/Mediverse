<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AnalyseRequest extends Notification
{
    use Queueable;

    protected $analyseData;

    /**
     * Create a new notification instance.
     */
    public function __construct($analyseData)
    {
        $this->analyseData = $analyseData;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Lab Test Request',
            'body' => 'A new lab test has been requested by Dr. ' . $this->analyseData['doctor_name'],
            'type' => 'lab_test_requested',
            'analyse_id' => $this->analyseData['analyse_id'],
            'doctor_id' => $this->analyseData['doctor_id'],
            'clinic_id' => $this->analyseData['clinic_id'],
        ];
    }
}

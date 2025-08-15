<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class Referral extends Notification
{
    use Queueable;

    protected $referralData;

    /**
     * Create a new notification instance.
     */
    public function __construct($referralData)
    {
        $this->referralData = $referralData;
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
            'title' => 'New Referral',
            'body' => 'The patient ' . $this->referralData['patient_name'] .
                ' has been referred to you by Dr. ' . $this->referralData['referring_doctor_name'],
            'type' => 'referral',
            'patient_id' => $this->referralData['patient_id'],
            'referring_doctor_id' => $this->referralData['referring_doctor_id'],
        ];
    }
}

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewClinicCreated extends Notification
{
    use Queueable;

    protected $clinic;

    /**
     * Create a new notification instance.
     */
    public function __construct($clinic)
    {
        $this->clinic = $clinic;
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
        return [
            'title' => 'new clinic',
            'body' => 'new clinic added ' . $this->clinic->name,
            'clinic_id' => $this->clinic->id,
            'type' => 'new_clinic_created',
        ];
    }
}

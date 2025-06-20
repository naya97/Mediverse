<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DoctorRated extends Notification
{
    use Queueable;

    protected $ratingData;

    /**
     * Create a new notification instance.
     */
    public function __construct($ratingData)
    {
        $this->ratingData = $ratingData;
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
            'title' => 'you have already been rated',
            'body' => 'A new rating has been submitted.',
            'type' => 'doctor_rated',
            'rater_id' => $this->ratingData['user_id'],
            'rater_name' => $this->ratingData['user_name'],
            'rating' => $this->ratingData['rating'],
        ];
    }
}

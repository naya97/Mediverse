<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShiftCompleted extends Notification
{
    use Queueable;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Shift Completed',
            'body' => "Your {$this->data['shift']} on {$this->data['day']} has ended. You treated {$this->data['visits']} patient(s) today.",
            'type' => 'shift_completed',
            'shift' => $this->data['shift'],
            'day' => $this->data['day'],
            'visits' => $this->data['visits'],
        ];
    }
}

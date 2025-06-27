<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DoctorAnalyseResult extends Notification
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
            'title' => 'Analysis Result Available',
            'body' => 'The test result for patient ' . $this->data['patient_name'] . ' is now available.',
            'type' => 'analysis_result_ready',
            'analyse_id' => $this->data['analyse_id'],
            'patient_id' => $this->data['patient_id'],
        ];
    }
}

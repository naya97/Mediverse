<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

// class MessageSent extends Notification implements ShouldQueue
// {
//     use Queueable;

//     /**
//      * MessageSent constructor.
//      * @param array $data
//      */
//     public function __construct(private array $data)
//     {
//         //
//     }

//     /**
//      * Get the notification's delivery channels.
//      *
//      * @param  mixed  $notifiable
//      * @return array
//      */
//     public function via($notifiable)
//     {
//         return [FcmChannel::class];
//     }
//     // public function via()
//     // {
//     //     return [OneSignalChannel::class];
//     // }

//     // public function toOneSignal(){
//     //     $messageData = $this->data['messageData'];

//     //     return OneSignalMessage::create()
//     //             ->setSubject($messageData['senderName']. " sent you a message.")
//     //             ->setBody($messageData['message'])
//     //             ->setData('data',$messageData);
//     // }
//     public function toFcm($notifiable)
//     {
//         $messageData = $this->data['messageData'];

//         return (new FcmMessage(notification: new FcmNotification(
//             title: 'Account Activated',
//             body: 'Your account has been activated.',
//         )))->data([
//             'data' => json_encode($messageData)
//         ]);
//     }
// }

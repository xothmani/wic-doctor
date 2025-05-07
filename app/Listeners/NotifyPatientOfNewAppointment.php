<?php

namespace App\Listeners;

use App\Events\CreateAppointmentEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\FCMServices;

use App\Models\Notification;

class NotifyPatientOfNewAppointment
{
    /**
     * Create the event listener. hamza
     */
    public function __construct(FCMServices $fcmServices)
    {
        $this->fcmService = $fcmServices;
    }

    /**
     * Handle the event.
     */
    public function handle(CreateAppointmentEvent $event): void
    {
        // Préparez la notification
        $title = 'New Appointment';
        $message = "Your appointment has been successfully scheduled. Please check the details in the app.";

        // Envoi de la notification FCM
        $this->fcmService->sendNotification(
            $event->deviceToken,
            $title,
            $message,
            [
                'appointment_id' => $event->appointment_id,
                'id' => 'App\Notifications\NewAppointmentAdded',
                ]
        );


        //Ajouter la notification dans la base de donnée
        Notification::create([
            'type' => 'App\Notifications\NewAppointmentAdded',
            'read_at' => null,
            'notifiable_id' => $event->user_id,
            'notifiable_type' => 'App\Models\User',
            'data' => [
                'appointment_id' => $event->appointment_id,
                'id' => 'App\Notifications\NewAppointmentAdded',
                ],
            'read' => false,
            'body' => $message
        ]);
    }
}

<?php

namespace App\Listeners;

use App\Events\AppointmentStatusChangedEvent;
use App\Notifications\FCMServices;
use Log;
use App\Models\Notification;

class SendNotificationOnAppointmentStatusChanged
{
    /**
     * Create the event listener.
     */
    public function __construct(FCMServices $fcmServices)
    {
        $this->fcmService = $fcmServices;
    }

    /**
     * Handle the event.
     */
    public function handle(AppointmentStatusChangedEvent $event)
    {
        Log::info("Handle Status Appointment Changer Listener");
        $appointment = $event->appointment;
        $deviceToken = $event->deviceToken;
        Log::info("Appointment -> Listener Status Changed Event: {$appointment} / {$deviceToken}");

        // Assurez-vous d'avoir un moyen d'obtenir le device_token du utilisateur
        $deviceToken = $appointment->user->device_token;
        Log::info("Device Token -> Listener Status Changed Event: {$deviceToken}");
        // Préparez la notification
        $title = 'Statut du rendez-vous modifié';
        $message = "Le statut de votre rendez-vous avec Dr.{$appointment->doctor->name} a été {$this->getStatusFromId($event->status_id)}.";

        // Envoi de la notification FCM
        $this->fcmService->sendNotification(
            $deviceToken,
            $title,
            $message,
            [
                'appointment_id' => $appointment->id, 
                'status' => $this->getStatusFromId($event->status_id),
                'id' => 'App\Notifications\StatusChangedAppointment',
                ]
        );


        //Ajouter la notification dans la base de donnée
        Log::info("Add notification in database Send notification on appointment status changed");
        Notification::create([
            'type' => 'App\Notifications\StatusChangedAppointment',
            'read_at' => null,
            'notifiable_id' => $appointment->user_id,
            'notifiable_type' => 'App\Models\User',
            'data' => [
                'appointment_id' => $appointment->id, 
                'status' => $this->getStatusFromId($event->status_id),
                'id' => 'App\Notifications\StatusChangedAppointment',
                ],
            'read' => false,
            'body' => $message
        ]);
    }


    public function getStatusFromId($status_id){
        switch ($status_id) {
            case 1:
                return "Received";
            case 2:
                return "In Progress";
            case 3:
                return "On the Way";
            case 4:
                return "Accepted";
            case 5:
                return "Ready";
            case 6:
                return "Done";
            case 7:
                return "Failed";
            case 9:
                return "Paid";                                   
            default:
                return "Received";
        }
    }

    
}

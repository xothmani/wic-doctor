<?php

namespace App\Listeners;

use App\Events\AppointmentStatusChangedEvent;
use App\Notifications\FCMServices;
use Log;
use App\Models\Notification;
use App\Models\Doctor;
use Illuminate\Support\Str;


class SendNotificationOnAppointmentStatusChanged
{
    /**
     * Create the event listener Hamzaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.
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
        $appointment = $event->appointment;
        $deviceToken = $event->deviceToken;

        // Assurez-vous d'avoir un moyen d'obtenir le device_token du utilisateur
        $deviceToken = $appointment->user->device_token;
        // Préparez la notification

        $doctor = Doctor::find($appointment->doctor_id);

        $title = 'Appointment Status Updated';
        
        $message = "Your appointment with Dr. %s is now %s.";

        // Envoi de la notification FCM
        $this->fcmService->sendNotification(
            $deviceToken,
            $title,
            $message,
            [
                'appointment_id' => $appointment->id, 
                'status' => $this->getStatusFromId($event->status_id),
                'id' => 'App\Notifications\StatusChangedAppointment',
                'doctor_name' => $doctor->name
                ]
        );


        //Ajouter la notification dans la base de donnée
        Notification::create([
            'type' => 'App\Notifications\StatusChangedAppointment',
            'read_at' => null,
            'notifiable_id' => $appointment->user_id,
            'notifiable_type' => 'App\Models\User',
            'data' => [
                'appointment_id' => $appointment->id, 
                'status' => $this->getStatusFromId($event->status_id),
                'id' => 'App\Notifications\StatusChangedAppointment',
                'doctor_name' => $doctor->name
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

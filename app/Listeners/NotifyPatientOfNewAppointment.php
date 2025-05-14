<?php

namespace App\Listeners;

use App\Events\CreateAppointmentEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\FCMServices;
use App\Models\Doctor;
use Illuminate\Support\Facades\Lang;

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
        $appointment = $event->appointment;
        $deviceToken = $event->deviceToken;
        // Préparez la notification
        //$title = 'New Appointment';
        //$message = "Your appointment has been successfully scheduled. Please check the details in the app.";

        $localUser = $appointment->user->locale_mobile;
        // Préparez la notification

        $doctor = Doctor::find($appointment->doctor_id);

        //$title = 'Appointment Status Updated';
        
        $bdBody = "You have a new appointment with Dr. %s on %s at %s.";

        Lang::setLocale($localUser ?? 'ar');

        $title = $this->getCustomTranslation('New Appointment', $localUser ?? 'en');

        $template = $this->getCustomTranslation('You have a new appointment with Dr. %s on %s at %s.', $localUser ?? 'en');
        // Récupération date et heure formatées
        $appointmentDate = \Carbon\Carbon::parse($appointment->appointment_at)->translatedFormat('d M Y'); // ex: 19 May 2025
        $appointmentTime = \Carbon\Carbon::parse($appointment->appointment_at)->translatedFormat('H:i'); // ex: 19:15

        $message = sprintf($template, $doctor->name, $appointmentDate, $appointmentTime);




        // Envoi de la notification FCM
        $this->fcmService->sendNotification(
            $event->deviceToken,
            $title,
            $message,
            [
                'appointment_id' => $appointment->id,
                'id' => 'App\Notifications\NewAppointmentAdded',
                'doctor_name' => $doctor->name,
                'appointment_date' => $appointmentDate,
                'appointment_time' => $appointmentTime
                ],
        );


        //Ajouter la notification dans la base de donnée
        Notification::create([
            'type' => 'App\Notifications\NewAppointmentAdded',
            'read_at' => null,
            'notifiable_id' => $event->user->id,
            'notifiable_type' => 'App\Models\User',
            'data' => [
                'appointment_id' => $appointment->id,
                'id' => 'App\Notifications\NewAppointmentAdded',
                'doctor_name' => $doctor->name,
                'appointment_date' => $appointmentDate,
                'appointment_time' => $appointmentTime
                ],
            'read' => false,
            'body' => $bdBody
        ]);
    }




    public function getCustomTranslation($key, $locale = 'en')
    {
        $path = base_path("resources/lang/{$locale}/customer_app.json");

        if (!file_exists($path)) return $key;

        $translations = json_decode(file_get_contents($path), true);

        return $translations[$key] ?? $key;
    }
}

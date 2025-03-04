<?php

namespace App\Mail;

use App\Models\Doctor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class DoctorRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $doctorPassword;
    public $patientPassword;
    public $doctor;

    /**
     * Create a new message instance.
     */
    public function __construct($doctorPassword, $patientPassword, Doctor $doctor)
    {
        $this->doctorPassword = $doctorPassword;
        $this->patientPassword = $patientPassword;
        $this->doctor = $doctor;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Details de vos Comptes',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.doctorRequest', // Assurez-vous que cette vue existe
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath(storage_path('app/public/pdf/Guide modification photo de profil.pdf'))
                      ->as('Guide_WIC_Doctor.pdf') // Nom du fichier dans l'email
                      ->withMime('application/pdf'),
        ];
    }
}

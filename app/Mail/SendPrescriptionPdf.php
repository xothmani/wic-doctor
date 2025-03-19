<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPrescriptionPdf extends Mailable
{
    use Queueable, SerializesModels;

    public $pdfPath; // Chemin du PDF
    public $patientName; // Nom du patient
    public $prescriptionDate; // Date de la prescription

    /**
     * Create a new message instance.
     *
     * @param string $pdfPath
     * @param string $patientName
     * @param string $prescriptionDate
     */
    public function __construct($pdfPath, $patientName, $prescriptionDate)
    {
        $this->pdfPath = $pdfPath;
        $this->patientName = $patientName;
        $this->prescriptionDate = $prescriptionDate;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Votre prescription du ' . $this->prescriptionDate) // Sujet de l'e-mail
                    ->view('emails.prescription') // Vue de l'e-mail
                    ->attach($this->pdfPath, [ // Attacher le PDF
                        'as' => 'Prescription_' . $this->patientName . '.pdf',
                        'mime' => 'application/pdf',
                    ]);
    }
}
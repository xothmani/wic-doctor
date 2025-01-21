<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMail extends Mailable
{
    use Queueable, SerializesModels;

    public $details; // To hold the data passed to the mailable

    /**
     * Create a new message instance.
     *
     * @param array $details
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;
	// Log the email details (for debugging purposes)
         Log::info('Email Details:', [
            'mail_username' => config('mail.mailers.smtp.username'),
            'mail_password' => config('mail.mailers.smtp.password'),
        ]);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->details['subject'] ?? 'Default Subject') // Fallback if 'subject' isn't provided
                    ->view('emails.send_mail') // Blade template for the email content
                    ->with([
                        'details' => $this->details, // Passing details to the view
                    ]);
    }
}


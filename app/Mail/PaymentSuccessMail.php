<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public $paymentDetails;

    /**
     * Create a new message instance.
     *
     * @param array $paymentDetails
     */
    public function __construct($paymentDetails)
    {
        $this->paymentDetails = $paymentDetails;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Payment Successful')
            ->view('emails.payment_success')
            ->with('paymentDetails', $this->paymentDetails);
    }
}



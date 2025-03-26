<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ParrainageMail extends Mailable
{
    use Queueable, SerializesModels;

    public $link;
    public $senderName;

    /**
     * Create a new message instance.
     *
     * @param string $link
     * @param string $senderName
     */
    public function __construct($link, $senderName)
    {
        $this->link = $link;
        $this->senderName = $senderName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Invitation à rejoindre WicDoctor')
                    ->view('parrainers.parrainers')
                    ->with([
                        'link' => $this->link,
                        'senderName' => $this->senderName,
                    ]);
    }
}
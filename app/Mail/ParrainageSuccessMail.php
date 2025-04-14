<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class ParrainageSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public $filleul;

    /**
     * Create a new message instance.
     *
     * @param User $filleul
     */
    public function __construct(User $filleul)
    {
        $this->filleul = $filleul;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Votre filleul s\'est inscrit!')
                    ->view('emails.parrainage_success')
                    ->with([
                        'filleul' => $this->filleul,
                    ]);
    }
}
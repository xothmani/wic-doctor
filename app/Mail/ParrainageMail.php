<?php
namespace App\Mail;
use App\Mail\ParrainageMail;  // Correct import for the mail class
use Illuminate\Support\Facades\Mail;  // Correct import for Mail facade

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ParrainageMail extends Mailable
{
    public $link;

    // Recevoir le lien dans le constructeur
    public function __construct($link)
    {
        $this->link = $link;
    }

    public function build()
    {
        return $this->view('parrainers.parrainers') // La vue pour l'email
                    ->with([
                        'link' => $this->link,  // Passer le lien à la vue
                    ])
                    ->subject('Invitation au Parrainage');
    }
}

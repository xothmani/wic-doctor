<?php
namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class AddPatientMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $generatedPassword;
    public $email;
    public $shortUrl; // Ajoutez la propriété pour le lien court

    public function __construct(User $user, $generatedPassword, $email, $shortUrl)
    {
        $this->user = $user;
        $this->generatedPassword = $generatedPassword;
        $this->email = $email;
        $this->shortUrl = $shortUrl; // Initialisez le lien court
    }

    public function build()
    {
        return $this->subject('Bienvenue chez Wic-Doctor')
                    ->view('emails.add_patient')
                    ->with([
                        'userName' => json_decode($this->user->name, true)['fr'] ?? $this->user->name,
                        'userLastname' => json_decode($this->user->lastname, true)['fr'] ?? $this->user->lastname,
                        'generatedPassword' => $this->generatedPassword,
                        'email' => $this->email,
                        'shortUrl' => $this->shortUrl, // Passez le lien court à la vue
                    ]);
    }
}

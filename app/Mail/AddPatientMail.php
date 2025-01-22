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
    public $email; // Ajoutez la propriété pour l'email

    /**
     * Crée une nouvelle instance de message.
     *
     * @param  User  $user
     * @param  string  $generatedPassword
     * @param  string  $email // Ajoutez l'email en paramètre
     */
    public function __construct(User $user, $generatedPassword, $email)
    {
        $this->user = $user;
        $this->generatedPassword = $generatedPassword;
        $this->email = $email; // Initialisez la propriété email
    }

    /**
     * Construire le message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Bienvenue chez Wic-Doctor')
                    ->view('emails.add_patient')
                    ->with([
                        'userName' => $this->user->name,
                        'userLastname' => $this->user->lastname,
                        'generatedPassword' => $this->generatedPassword,
                        'email' => $this->email, // Passez l'email à la vue
                    ]);
    }
}

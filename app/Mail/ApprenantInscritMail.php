<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApprenantInscritMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject("Bienvenue dans la promotion")
                    ->view('emails.apprenant_inscrit')
                    ->with([
                        'nom' => $this->user->nom,
                        'prenom' => $this->user->prenom,
                        'email' => $this->user->email,
                    ]);
    }
}

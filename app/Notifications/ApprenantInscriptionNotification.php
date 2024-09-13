<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ApprenantInscriptionNotification extends Notification
{
    use Queueable;

    public $user;
    public $password;

    public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    public function via($notifiable)
    {
        return ['mail', 'database']; // Inclure 'database' si vous stockez les notifications en base
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Vos informations de connexion à Kaay Point')
            ->greeting('Bonjour ' . $this->user->prenom . '!')
            ->line('Vous avez été inscrit avec succès sur Kaay Point.')
            ->line('Voici vos informations de connexion :')
            ->line('Email : ' . $this->user->email)
            ->line('Mot de passe : ' . $this->password)
            ->line('Matricule : ' . $this->user->matricule)
            ->action('Accéder à Kaay Point', url('/'))
            ->line('Merci d\'utiliser Kaay Point !');
    }

    public function toArray($notifiable)
    {
        return [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'matricule' => $this->user->matricule,
        ];
    }
}

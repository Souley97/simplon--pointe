<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprenantInscriptionNotification extends Notification
{
    use Queueable;

    protected $user;
    protected $password;

    /**
     * Create a new notification instance.
     *
     * @param $user
     * @param $password
     */
    public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail','database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Inscription sur la plateforme')
            ->greeting('Bonjour ' . $this->user->prenom . ' ' . $this->user->nom . ',')
            ->line('Vous avez été inscrit avec succès sur notre plateforme.')
            ->line('Voici vos informations de connexion :')
            ->line('**Email** : ' . $this->user->email)
            ->line('**Mot de passe** : ' . $this->password)
            ->line('Nous vous recommandons de changer ce mot de passe après votre première connexion.')
            ->action('Accéder à la plateforme', url('https://simplon-pointage.vercel.app'))
            ->line('Merci de faire partie de notre communauté.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
        ];
    }
}

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.set', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        // De geldigheid staat in minuten in de config, in de mail tonen we dagen
        $days = intdiv((int) config('auth.passwords.invites.expire'), 1440);

        return (new MailMessage)
            ->subject('Rond je Activadis-account af')
            ->greeting('Hallo '.$notifiable->name.',')
            ->line('Er is een account voor je aangemaakt op Activadis, de plek waar je je inschrijft voor activiteiten.')
            ->line('Klik op de knop om zelf een wachtwoord in te stellen. Daarna kun je meteen inloggen.')
            ->action('Wachtwoord instellen', $url)
            ->line("Deze link is {$days} dagen geldig. Is hij verlopen, vraag dan een beheerder om een nieuwe uitnodiging.");
    }
}

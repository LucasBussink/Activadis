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

        $minuten = config('auth.passwords.users.expire');

        return (new MailMessage)
            ->subject('Rond je Activadis-account af')
            ->greeting('Hallo ' . $notifiable->name . ',')
            ->line('Er is een account voor je aangemaakt op Activadis.')
            ->line('Klik op de knop om zelf een wachtwoord in te stellen.')
            ->action('Wachtwoord instellen', $url)
            ->line("Deze link is {$minuten} minuten geldig.");
    }
}
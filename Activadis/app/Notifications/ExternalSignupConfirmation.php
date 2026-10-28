<?php

namespace App\Notifications;

use App\Models\ExternalSignup;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExternalSignupConfirmation extends Notification
{
    use Queueable;

    public function __construct(public ExternalSignup $signup)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $activity = $this->signup->activity;

        $mail = (new MailMessage)
            ->subject('Je bent aangemeld voor '.$activity->title)
            ->greeting('Hallo '.$this->signup->name.',')
            ->line('Bedankt voor je aanmelding. Je staat op de lijst voor **'.$activity->title.'**.')
            ->line('Wanneer: '.$activity->period());

        if ($this->signup->brings_partner) {
            $mail->line('Je hebt een +1 aangemeld.');
        }

        return $mail
            ->line('Kun je toch niet komen? Meld je dan af, dan komt je plek vrij voor iemand anders.')
            ->action('Afmelden', route('external.cancel', $this->signup->token));
    }
}

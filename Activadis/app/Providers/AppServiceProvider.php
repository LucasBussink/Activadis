<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Eigen opmaak voor de paginering, zonder Tailwind
        Paginator::defaultView('pagination.activadis');

        // De "wachtwoord vergeten"-mail in het Nederlands
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            $minutes = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

            return (new MailMessage)
                ->subject('Nieuw wachtwoord instellen')
                ->greeting('Hallo '.$notifiable->name.',')
                ->line('Je hebt gevraagd om een nieuw wachtwoord voor je Activadis-account.')
                ->action('Nieuw wachtwoord instellen', $url)
                ->line("Deze link is {$minutes} minuten geldig.")
                ->line('Heb je dit niet aangevraagd? Dan hoef je niets te doen, je wachtwoord blijft hetzelfde.');
        });
    }
}

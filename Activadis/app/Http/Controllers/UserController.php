<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\SetPasswordNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Throwable;

class UserController extends Controller
{
    /**
     * Overzicht van alle gebruikers, met per gebruiker of de uitnodiging nog openstaat.
     */
    public function index(): View
    {
        $users = User::query()
            ->orderByDesc('is_admin')
            ->orderBy('name')
            ->paginate(15);

        // Een gebruiker met een uitnodigingstoken heeft nog geen eigen wachtwoord ingesteld
        $expireMinutes = (int) config('auth.passwords.invites.expire');
        $invites = DB::table('user_invite_tokens')
            ->whereIn('email', $users->pluck('email'))
            ->pluck('created_at', 'email')
            ->map(fn ($createdAt) => Carbon::parse($createdAt)->addMinutes($expireMinutes)->isPast() ? 'expired' : 'pending');

        return view('users.index', [
            'users' => $users,
            'invites' => $invites,
            'inviteDays' => intdiv($expireMinutes, 1440),
        ]);
    }

    /**
     * Formulier voor een nieuwe gebruiker.
     */
    public function create(): View
    {
        return view('users.create', [
            'inviteDays' => intdiv((int) config('auth.passwords.invites.expire'), 1440),
        ]);
    }

    /**
     * Maak de gebruiker aan en stuur een mail om zelf een wachtwoord in te stellen.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'is_admin' => ['nullable', 'boolean'],
        ]);

        // Het wachtwoord is willekeurig en onbekend: de gebruiker stelt zelf een wachtwoord in via de mail
        $user = new User();
        $user->name = $validated['name'];
        $user->email = Str::lower($validated['email']);
        $user->password = Hash::make(Str::random(32));
        $user->is_admin = $request->boolean('is_admin');
        $user->email_verified_at = now();
        $user->save();

        if (! $this->sendInvite($user)) {
            return redirect()
                ->route('users.index')
                ->with('error', 'Gebruiker aangemaakt, maar de mail kon niet worden verstuurd. Probeer "Uitnodiging opnieuw sturen".');
        }

        return redirect()
            ->route('users.index')
            ->with('status', 'Gebruiker aangemaakt. Er is een mail verstuurd naar '.$user->email.'.');
    }

    /**
     * Stuur de uitnodiging opnieuw, bijvoorbeeld als de mail kwijt is of de link is verlopen.
     */
    public function resendInvite(User $user): RedirectResponse
    {
        $hasOpenInvite = DB::table('user_invite_tokens')->where('email', $user->email)->exists();

        if (! $hasOpenInvite) {
            return redirect()
                ->route('users.index')
                ->with('error', $user->name.' heeft al een wachtwoord ingesteld. Via "Wachtwoord vergeten" kan een nieuw wachtwoord worden aangevraagd.');
        }

        if (! $this->sendInvite($user)) {
            return redirect()
                ->route('users.index')
                ->with('error', 'De mail kon niet worden verstuurd. Probeer het later opnieuw.');
        }

        return redirect()
            ->route('users.index')
            ->with('status', 'Uitnodiging opnieuw verstuurd naar '.$user->email.'.');
    }

    /**
     * Verwijder een gebruiker (en daarmee ook de inschrijvingen).
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('users.index')
                ->with('error', 'Je kunt je eigen account niet verwijderen.');
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('status', 'Gebruiker '.$user->name.' is verwijderd.');
    }

    /**
     * Maak een (nieuw) uitnodigingstoken en mail het. Een oud token van dezelfde gebruiker vervalt daarmee.
     */
    private function sendInvite(User $user): bool
    {
        try {
            $token = Password::broker('invites')->createToken($user);
            $user->notify(new SetPasswordNotification($token));
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }

        return true;
    }
}

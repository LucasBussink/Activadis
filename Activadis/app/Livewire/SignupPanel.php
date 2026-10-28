<?php

namespace App\Livewire;

use App\Models\Activity;
use App\Models\Signups;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Het inschrijfblok op de detailpagina: bezetting, inschrijven, uitschrijven, +1 en dieetwensen.
 * Alles gebeurt zonder de pagina te herladen. +1 en dieetwensen worden vanzelf opgeslagen.
 */
class SignupPanel extends Component
{
    public Activity $activity;

    public bool $bringsPartner = false;

    public string $dietaryNotes = '';

    // Korte melding bovenaan het blok, met een teller zodat dezelfde melding opnieuw verschijnt
    public ?string $notice = null;

    public bool $noticeIsError = false;

    public int $noticeId = 0;

    // Telt hoe vaak er is opgeslagen, voor het label "Opgeslagen"
    public int $saveCount = 0;

    protected function rules(): array
    {
        return [
            'bringsPartner' => ['boolean'],
            'dietaryNotes' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function messages(): array
    {
        return [
            'dietaryNotes.max' => 'Je dieetwensen mogen maximaal 500 tekens zijn.',
        ];
    }

    public function mount(Activity $activity): void
    {
        abort_if($activity->status === Activity::STATUS_DRAFT && ! auth()->user()->isAdmin(), 404);

        $this->activity = $activity;
        $this->loadFromSignup();
    }

    public function signUp(): void
    {
        $this->activity->refresh();
        abort_if($this->activity->status === Activity::STATUS_DRAFT, 404);

        $user = auth()->user();

        if ($this->activity->isUserSignedUp($user)) {
            $this->notify('Je bent al ingeschreven.');

            return;
        }

        if (! $this->activity->isSignupOpen()) {
            $this->notify('De inschrijving voor deze activiteit is gesloten.', true);

            return;
        }

        if ($this->activity->isFull()) {
            $this->notify('Deze activiteit is helaas vol.', true);

            return;
        }

        if ($this->bringsPartner && ! $this->activity->allow_partner) {
            $this->bringsPartner = false;
            $this->notify('Bij deze activiteit mag je geen +1 meenemen.', true);

            return;
        }

        $this->validate();

        $signup = $this->activity->addUser($user, $this->bringsPartner, $this->dietaryNotes);

        if (! $signup) {
            $this->notify('Inschrijven is niet gelukt. Probeer het opnieuw.', true);

            return;
        }

        $this->loadFromSignup();
        $this->notify($this->bringsPartner ? 'Je bent ingeschreven, met een +1!' : 'Je bent ingeschreven!');
        $this->announce();
    }

    public function signOff(): void
    {
        $this->activity->refresh();
        abort_if($this->activity->status === Activity::STATUS_DRAFT, 404);

        if (! $this->activity->removeUser(auth()->user())) {
            $this->notify('Uitschrijven kan niet meer.', true);

            return;
        }

        $this->bringsPartner = false;
        $this->dietaryNotes = '';
        $this->saveCount = 0;
        $this->resetErrorBag();
        $this->notify('Je bent uitgeschreven.');
        $this->announce();
    }

    // Wordt automatisch aangeroepen als de schuifknop of het tekstveld verandert (alleen als je al ingeschreven bent)
    public function updatedBringsPartner(): void
    {
        $this->saveDetails();
    }

    public function updatedDietaryNotes(): void
    {
        $this->saveDetails();
    }

    private function saveDetails(): void
    {
        $user = auth()->user();
        $this->activity->refresh();

        if (! $this->activity->isUserSignedUp($user)) {
            return;
        }

        $this->validate();

        if (! $this->activity->updateSignup($user, $this->bringsPartner, $this->dietaryNotes, true)) {
            $this->loadFromSignup();
            $this->notify('Je inschrijving kan niet meer worden aangepast.', true);

            return;
        }

        $this->saveCount++;
    }

    private function mySignup(): ?Signups
    {
        return $this->activity->signups()->where('user_id', auth()->id())->first();
    }

    private function loadFromSignup(): void
    {
        $signup = $this->mySignup();

        $this->bringsPartner = (bool) $signup?->brings_partner;
        $this->dietaryNotes = $signup?->dietary_notes ?? '';
    }

    private function notify(string $text, bool $isError = false): void
    {
        $this->notice = $text;
        $this->noticeIsError = $isError;
        $this->noticeId++;
    }

    // Laat de rest van de pagina (de labels bij de titel) weten dat er iets is veranderd
    private function announce(): void
    {
        $signedUp = $this->activity->isUserSignedUp(auth()->user());
        $registered = $this->activity->getSignedUpUserCount();

        $this->dispatch(
            'signup-changed',
            signedUp: $signedUp,
            full: $this->activity->max_participants !== null && $registered >= $this->activity->max_participants,
        );
    }

    public function render(): View
    {
        $activity = $this->activity;

        $mySignup = $this->mySignup();
        $registered = $activity->getSignedUpUserCount();
        $max = $activity->max_participants;
        $deadline = $activity->signup_deadline?->copy()->locale('nl');

        $isDraft = $activity->status === Activity::STATUS_DRAFT;
        $isFull = $max && $registered >= $max;

        // Eén toestand voor het inschrijfblok: ingeschreven, open, vol, gesloten, afgelopen of concept
        $signupState = match (true) {
            $mySignup !== null => 'signed-up',
            $isDraft => 'draft',
            $activity->hasEnded() => 'ended',
            $deadline?->isPast() => 'closed',
            $isFull => 'full',
            default => 'open',
        };

        return view('livewire.signup-panel', [
            'mySignup' => $mySignup,
            'registered' => $registered,
            'max' => $max,
            'isFull' => $isFull,
            'deadline' => $deadline,
            'signupState' => $signupState,
            'signupOpen' => $activity->isSignupOpen(),
            'spotsLeft' => $max ? max($max - $registered, 0) : null,
            'percentage' => $max ? min(100, (int) round($registered / $max * 100)) : 0,
        ]);
    }
}

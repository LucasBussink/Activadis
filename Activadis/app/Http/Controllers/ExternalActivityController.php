<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ExternalSignup;
use App\Notifications\ExternalSignupConfirmation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Publieke pagina's voor externen: de externe activiteiten bekijken en je zonder account aanmelden.
 */
class ExternalActivityController extends Controller
{
    public function index(): View
    {
        $activities = $this->visibleActivities()
            ->with('images')
            ->withCount(['signups', 'externalSignups'])
            // Verlopen activiteiten (einde of anders begin ligt in het verleden) tonen we niet.
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhereRaw('COALESCE(ends_at, starts_at) >= ?', [now()]))
            ->orderBy('starts_at')
            ->get();

        return view('external.index', ['activities' => $activities]);
    }

    public function show(Activity $activity): View
    {
        $this->abortUnlessVisible($activity);

        $activity->load('images')->loadCount(['signups', 'externalSignups']);

        return view('external.show', ['activity' => $activity]);
    }

    public function store(Request $request, Activity $activity): RedirectResponse
    {
        $this->abortUnlessVisible($activity);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'brings_partner' => ['nullable', 'boolean'],
            'dietary_notes' => ['nullable', 'string', 'max:500'],
            // Verborgen veld: echte mensen laten dit leeg, bots vullen het in
            'website' => ['prohibited'],
        ], [
            'name.required' => 'Vul je naam in.',
            'name.max' => 'Je naam mag maximaal 100 tekens zijn.',
            'email.required' => 'Vul je e-mailadres in.',
            'email.email' => 'Dit is geen geldig e-mailadres.',
            'dietary_notes.max' => 'Je dieetwensen mogen maximaal 500 tekens zijn.',
            'website.prohibited' => 'Aanmelden is niet gelukt.',
        ]);

        if (! $activity->isSignupOpen()) {
            return back()->withInput()->with('error', 'De aanmelding voor deze activiteit is gesloten.');
        }

        if ($activity->isFull()) {
            return back()->withInput()->with('error', 'Deze activiteit is helaas vol.');
        }

        if ($activity->externalSignups()->where('email', mb_strtolower(trim($validated['email'])))->exists()) {
            return back()->withInput()->withErrors(['email' => 'Met dit e-mailadres heb je je al aangemeld.']);
        }

        $signup = $activity->addExternal(
            $validated['name'],
            $validated['email'],
            (bool) ($validated['brings_partner'] ?? false),
            $validated['dietary_notes'] ?? null,
        );

        if (! $signup) {
            return back()->withInput()->with('error', 'Aanmelden is niet gelukt. Probeer het opnieuw.');
        }

        try {
            $signup->notify(new ExternalSignupConfirmation($signup));
        } catch (\Throwable $e) {
            // De aanmelding staat, alleen de mail is mislukt. Dat melden we aan de beheerders via het logboek.
            report($e);

            return redirect()->route('external.show', $activity)
                ->with('status', 'Je bent aangemeld, maar de bevestigingsmail kon niet worden verstuurd.');
        }

        return redirect()->route('external.show', $activity)
            ->with('status', 'Je bent aangemeld! We hebben een bevestiging gestuurd naar '.$signup->email.'.');
    }

    /**
     * Bevestigingspagina voor het afmelden. Pas na een klik op de knop wordt er echt afgemeld,
     * zodat een mailprogramma dat de link vooraf opent niemand per ongeluk afmeldt.
     */
    public function cancel(string $token): View
    {
        $signup = ExternalSignup::with('activity')->where('token', $token)->first();

        return view('external.cancel', [
            'signup' => $signup,
            'canCancel' => $signup?->activity->isSignupOpen() ?? false,
            'done' => false,
        ]);
    }

    public function destroy(string $token): View
    {
        $signup = ExternalSignup::with('activity')->where('token', $token)->first();

        $canCancel = $signup?->activity->isSignupOpen() ?? false;

        if ($signup && $canCancel) {
            $signup->delete();
        }

        return view('external.cancel', [
            'signup' => $signup,
            'canCancel' => $canCancel,
            'done' => $signup !== null && $canCancel,
        ]);
    }

    /**
     * Alleen gepubliceerde externe activiteiten zijn publiek.
     */
    private function visibleActivities()
    {
        return Activity::query()
            ->where('status', Activity::STATUS_PUBLISHED)
            ->where('is_external', true);
    }

    private function abortUnlessVisible(Activity $activity): void
    {
        abort_unless($activity->status === Activity::STATUS_PUBLISHED && $activity->is_external, 404);
    }
}

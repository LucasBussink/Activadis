<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SignupController extends Controller
{
    /**
     * Schrijf de ingelogde gebruiker in voor de activiteit.
     */
    public function store(Request $request, Activity $activity): RedirectResponse
    {
        abort_if($activity->status === Activity::STATUS_DRAFT, 404);

        $user = $request->user();

        $data = $request->validate([
            'brings_partner' => ['nullable', 'boolean'],
            'dietary_notes' => ['nullable', 'string', 'max:500'],
        ]);

        if ($activity->isUserSignedUp($user)) {
            return back()->with('status', 'Je bent al ingeschreven.');
        }

        if (! $activity->isSignupOpen()) {
            return back()->with('error', 'De inschrijving voor deze activiteit is gesloten.');
        }

        if ($activity->isFull()) {
            return back()->with('error', 'Deze activiteit is helaas vol.');
        }

        $withPartner = $request->boolean('brings_partner');

        if ($withPartner && ! $activity->allow_partner) {
            return back()->with('error', 'Bij deze activiteit mag je geen +1 meenemen.');
        }

        $activity->addUser($user, $withPartner, $data['dietary_notes'] ?? null);

        return back()->with('status', $withPartner ? 'Je bent ingeschreven, met een +1!' : 'Je bent ingeschreven!');
    }

    /**
     * Pas je +1 en dieetwensen aan.
     */
    public function update(Request $request, Activity $activity): RedirectResponse
    {
        abort_if($activity->status === Activity::STATUS_DRAFT, 404);

        $data = $request->validate([
            'brings_partner' => ['nullable', 'boolean'],
            'dietary_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $withPartner = $request->boolean('brings_partner');

        if (! $activity->isUserSignedUp($request->user())) {
            return back()->with('error', 'Je bent niet ingeschreven voor deze activiteit.');
        }

        // Dieetwensen alleen aanpassen als het veld is meegestuurd
        $changeNotes = $request->exists('dietary_notes');

        if (! $activity->updateSignup($request->user(), $withPartner, $data['dietary_notes'] ?? null, $changeNotes)) {
            return back()->with('error', 'Je inschrijving kan niet meer worden aangepast.');
        }

        return back()->with('status', 'Je gegevens zijn opgeslagen.');
    }

    /**
     * Schrijf de ingelogde gebruiker uit voor de activiteit.
     */
    public function destroy(Request $request, Activity $activity): RedirectResponse
    {
        abort_if($activity->status === Activity::STATUS_DRAFT, 404);

        if (! $activity->removeUser($request->user())) {
            return back()->with('error', 'Uitschrijven kan niet meer.');
        }

        return back()->with('status', 'Je bent uitgeschreven.');
    }
}

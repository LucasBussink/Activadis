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

        $activity->addUser($user, $withPartner);

        return back()->with('status', $withPartner ? 'Je bent ingeschreven, met een +1!' : 'Je bent ingeschreven!');
    }

    /**
     * Zet een +1 aan of uit voor je bestaande inschrijving.
     */
    public function update(Request $request, Activity $activity): RedirectResponse
    {
        abort_if($activity->status === Activity::STATUS_DRAFT, 404);

        $withPartner = $request->boolean('brings_partner');

        if (! $activity->isUserSignedUp($request->user())) {
            return back()->with('error', 'Je bent niet ingeschreven voor deze activiteit.');
        }

        if (! $activity->setPartner($request->user(), $withPartner)) {
            return back()->with('error', 'Je +1 kan niet meer worden aangepast.');
        }

        return back()->with('status', $withPartner ? 'Je +1 is toegevoegd.' : 'Je +1 is weggehaald.');
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

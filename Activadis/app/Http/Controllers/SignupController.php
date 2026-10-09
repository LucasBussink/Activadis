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

        $activity->addUser($user);

        return back()->with('status', 'Je bent ingeschreven!');
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

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class SetPasswordController extends Controller
{
    // Laat het formulier zien
    public function create(Request $request, string $token): View
    {
        return view('auth.set-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    // Verwerkt het formulier
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::broker('invites')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('status', 'Je account is klaar. Je kunt nu inloggen.');
        }

        return back()->withErrors([
            'password' => 'Deze link is ongeldig of verlopen. Vraag een nieuwe aan via "Wachtwoord vergeten".',
        ]);
    }
}
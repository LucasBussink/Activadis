<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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
    // Laat het formulier zien, of een melding als de link niet (meer) geldig is
    public function create(Request $request, string $token): View
    {
        $email = (string) $request->query('email');
        $user = User::where('email', $email)->first();

        return view('auth.set-password', [
            'token' => $token,
            'email' => $email,
            'valid' => $user !== null && Password::broker('invites')->tokenExists($user, $token),
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

        return back()->withInput($request->only('email'))->withErrors([
            'password' => 'Deze link is ongeldig of verlopen. Vraag een beheerder om een nieuwe uitnodiging.',
        ]);
    }
}

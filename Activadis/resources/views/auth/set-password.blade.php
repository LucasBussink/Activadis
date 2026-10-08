<x-guest-layout>
    <h1 class="text-lg font-semibold text-gray-900">Welkom bij Activadis!</h1>
    <p class="mt-1 mb-4 text-sm text-gray-600">
        Stel een wachtwoord in voor <strong>{{ $email }}</strong>.
        Daarna kun je inloggen.
    </p>

    <form method="POST" action="{{ route('password.set.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <div>
            <x-input-label for="password" value="Wachtwoord" />
            <x-text-input id="password" class="block mt-1 w-full" type="password"
                          name="password" required autofocus autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Herhaal wachtwoord" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                          name="password_confirmation" required autocomplete="new-password" />
        </div>

        <div class="flex justify-end mt-4">
            <x-primary-button>Account activeren</x-primary-button>
        </div>
    </form>
</x-guest-layout>
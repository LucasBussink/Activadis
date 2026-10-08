<x-app-layout>

    <form method="POST" action="{{ route('users.store') }}">
        @csrf

        <div>
            <label for="name">Naam</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required>
            @error('name')
                <p>{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email">E-mailadres</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required>
            @error('email')
                <p>{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="is_admin">
                <input type="checkbox" name="is_admin" id="is_admin" value="1" @checked(old('is_admin'))>
                Beheerder
            </label>
            @error('is_admin')
                <p>{{ $message }}</p>
            @enderror
        </div>

        <a href="{{ route('users.index') }}">Annuleren</a>
        <button type="submit">Gebruiker opslaan</button>
    </form>
</x-app-layout>

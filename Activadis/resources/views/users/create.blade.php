@push('styles')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  @vite(['resources/css/pages/users.css'])
@endpush

<x-app-layout>
  <div class="users-page narrow">
    <header class="page-header">
      <div>
        <a href="{{ route('users.index') }}" class="back-link"><i class="bi bi-arrow-left"></i> Terug naar gebruikers</a>
        <h1>Nieuwe gebruiker</h1>
      </div>
    </header>

    <form method="POST" action="{{ route('users.store') }}" class="form-card" novalidate>
      @csrf

      <p class="form-intro">
        De gebruiker krijgt een mail met een link om zelf een wachtwoord in te stellen. De link is {{ $inviteDays }} dagen geldig.
      </p>

      <div @class(['field', 'error' => $errors->has('name')])>
        <label for="name">Naam</label>
        <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus autocomplete="off">
        @error('name')
          <p class="error-msg">{{ $message }}</p>
        @enderror
      </div>

      <div @class(['field', 'error' => $errors->has('email')])>
        <label for="email">E-mailadres</label>
        <input type="email" name="email" id="email" value="{{ old('email') }}" required autocomplete="off">
        @error('email')
          <p class="error-msg">{{ $message }}</p>
        @enderror
      </div>

      <div class="field">
        <label class="role-toggle">
          <input type="checkbox" name="is_admin" id="is_admin" value="1" @checked(old('is_admin'))>
          <span>
            <strong>Beheerder</strong>
            <small>Mag activiteiten en gebruikers beheren.</small>
          </span>
        </label>
        @error('is_admin')
          <p class="error-msg">{{ $message }}</p>
        @enderror
      </div>

      <div class="form-actions">
        <a href="{{ route('users.index') }}" class="btn-secondary">Annuleren</a>
        <button type="submit" class="btn-primary"><i class="bi bi-send"></i> Gebruiker aanmaken</button>
      </div>
    </form>
  </div>
</x-app-layout>

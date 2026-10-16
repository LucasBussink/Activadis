<x-auth-split title="Welkom bij Activadis">
  @if ($valid)
    <p class="intro">
      Stel een wachtwoord in voor <strong>{{ $email }}</strong>. Daarna kun je meteen inloggen.
    </p>

    <form method="POST" action="{{ route('password.set.store') }}">
      @csrf

      <input type="hidden" name="token" value="{{ $token }}">
      <input type="hidden" name="email" value="{{ $email }}">

      <div class="field @error('password') error @enderror">
        <label for="password">Wachtwoord</label>
        <input id="password" type="password" name="password" required autofocus autocomplete="new-password">
        @error('password')
          <p class="error-msg">{{ $message }}</p>
        @enderror
        <p class="hint">Minimaal 8 tekens.</p>
      </div>

      <div class="field">
        <label for="password_confirmation">Herhaal wachtwoord</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
      </div>

      <button type="submit" class="btn-primary">Account activeren</button>
    </form>
  @else
    <p class="status-message error">
      Deze link is ongeldig of verlopen. Vraag een beheerder om je opnieuw uit te nodigen.
    </p>

    <a href="{{ route('login') }}" class="back-link"><i class="bi bi-arrow-left"></i> Naar inloggen</a>
  @endif
</x-auth-split>

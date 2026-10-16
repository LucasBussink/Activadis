<x-auth-split title="Wachtwoord vergeten">
  <p class="intro">
    Vul je e-mailadres in. Als er een account bij hoort, sturen we je een link om een nieuw wachtwoord te kiezen.
  </p>

  @if (session('status'))
    <p class="status-message">{{ session('status') }}</p>
  @endif

  <form method="POST" action="{{ route('password.email') }}">
    @csrf

    <div class="field @error('email') error @enderror">
      <label for="email">E-mailadres</label>
      <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
      @error('email')
        <p class="error-msg">{{ $message }}</p>
      @enderror
    </div>

    <button type="submit" class="btn-primary">Stuur me een link</button>
  </form>

  <a href="{{ route('login') }}" class="back-link"><i class="bi bi-arrow-left"></i> Terug naar inloggen</a>
</x-auth-split>

<x-auth-split title="Nieuw wachtwoord">
  <p class="intro">Kies een nieuw wachtwoord voor je account.</p>

  <form method="POST" action="{{ route('password.store') }}">
    @csrf

    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <div class="field @error('email') error @enderror">
      <label for="email">E-mailadres</label>
      <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autocomplete="username">
      @error('email')
        <p class="error-msg">{{ $message }}</p>
      @enderror
    </div>

    <div class="field @error('password') error @enderror">
      <label for="password">Nieuw wachtwoord</label>
      <input id="password" type="password" name="password" required autofocus autocomplete="new-password">
      @error('password')
        <p class="error-msg">{{ $message }}</p>
      @enderror
      <p class="hint">Minimaal 8 tekens.</p>
    </div>

    <div class="field @error('password_confirmation') error @enderror">
      <label for="password_confirmation">Herhaal wachtwoord</label>
      <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
      @error('password_confirmation')
        <p class="error-msg">{{ $message }}</p>
      @enderror
    </div>

    <button type="submit" class="btn-primary">Wachtwoord opslaan</button>
  </form>
</x-auth-split>

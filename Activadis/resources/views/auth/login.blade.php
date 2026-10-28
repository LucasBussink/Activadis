<x-auth-visual-layout>
  <div class="login-page">
    <div class="login-visual">
      <div class="hero-anim-slot">
        <img src="{{ asset('images/hero-animation.gif') }}" alt="" id="hero-animation">
      </div>
      <p class="login-visual-caption">Activadis</p>
    </div>

    <div class="login-panel">
      <div class="login-panel-inner">
        <h1>Inloggen</h1>

        @if (session('status'))
          <p class="status-message">{{ session('status') }}</p>
        @endif

        <form method="POST" action="{{ route('login') }}">
          @csrf

          <div class="field @error('email') error @enderror">
            <label for="email">E-mailadres</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
              autocomplete="username">
            @error('email')
              <p class="error-msg">{{ $message }}</p>
            @enderror
          </div>

          <div class="field @error('password') error @enderror">
            <label for="password">Wachtwoord</label>
            <input id="password" type="password" name="password" required autocomplete="current-password">
            @error('password')
              <p class="error-msg">{{ $message }}</p>
            @enderror
          </div>

          <div class="checkbox-row">
            <label>
              <input type="checkbox" name="remember" id="remember_me">
              Onthoud mij
            </label>

            @if (Route::has('password.request'))
              <a href="{{ route('password.request') }}" class="forgot-link">Wachtwoord vergeten?</a>
            @endif
          </div>

          <button type="submit" class="btn-primary">Inloggen</button>
        </form>

        <p class="external-link">Ben je extern? <a href="{{ route('external.index') }}">Bekijk de activiteiten voor externen</a></p>
      </div>
    </div>
  </div>
</x-auth-visual-layout>

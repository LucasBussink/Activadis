@push('styles')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  @vite(['resources/css/pages/activity-detail.css'])
@endpush

@php
  $start = $activity->starts_at?->copy()->locale('nl');
  $end = $activity->ends_at?->copy()->locale('nl');
  $deadline = $activity->signup_deadline?->copy()->locale('nl');

  $multiDay = $start && $end && ! $start->isSameDay($end);

  if (! $start) {
      $dateText = 'Datum volgt';
  } elseif ($multiDay) {
      $dateText = ucfirst($start->isoFormat('dddd D MMMM')) . ' t/m ' . $end->isoFormat('dddd D MMMM YYYY');
  } else {
      $dateText = ucfirst($start->isoFormat('dddd D MMMM YYYY'));
  }

  if (! $start) {
      $timeText = null;
  } elseif ($multiDay) {
      $timeText = 'Vanaf ' . $start->format('H:i') . ($end ? ', tot ' . $end->format('H:i') . ' op de laatste dag' : '');
  } else {
      $timeText = $start->format('H:i') . ($end ? ' - ' . $end->format('H:i') : '');
  }

  $registered = $activity->signups_count + $activity->external_signups_count;
  $max = $activity->max_participants;
  $isFull = $max && $registered >= $max;
  $spotsLeft = $max ? max($max - $registered, 0) : null;
  $percentage = $max ? min(100, (int) round($registered / $max * 100)) : 0;

  $state = match (true) {
      $activity->hasEnded() => 'ended',
      $deadline?->isPast() => 'closed',
      $isFull => 'full',
      default => 'open',
  };

  $images = $activity->images;

  if ($activity->personal_contribution === null) {
      $contributionText = 'Bijdrage volgt';
  } elseif ((float) $activity->personal_contribution === 0.0) {
      $contributionText = 'Gratis';
  } else {
      $contributionText = 'Eigen bijdrage € ' . number_format((float) $activity->personal_contribution, 2, ',', '.');
  }
@endphp

<x-app-layout>
  <div class="activity-detail-page">
    <header class="page-header">
      <a href="{{ route('external.index') }}" class="back-link">
        <i class="bi bi-arrow-left"></i> Terug naar overzicht
      </a>

      <div class="title-row">
        <h1>{{ $activity->title ?? 'Naamloze activiteit' }}</h1>
        @if ($isFull)
          <span class="status-pill full">Vol</span>
        @endif
      </div>

      <ul class="key-facts">
        <li><i class="bi bi-calendar-event"></i> {{ $dateText }}</li>
        @if ($timeText)
          <li><i class="bi bi-clock"></i> {{ $timeText }}</li>
        @endif
        <li><i class="bi bi-wallet2"></i> {{ $contributionText }}</li>
      </ul>
    </header>

    @if (session('status'))
      <p class="flash"><i class="bi bi-check-circle-fill"></i> {{ session('status') }}</p>
    @endif
    @if (session('error'))
      <p class="flash error"><i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}</p>
    @endif

    <div @class(['hero', 'has-photo' => $images->isNotEmpty()])>
      @if ($images->isNotEmpty())
        <div class="slides">
          <img src="{{ $images->first()->url() }}" alt="{{ $activity->title ?? 'Activiteit' }}">
        </div>
      @else
        <i class="bi bi-calendar-event hero-icon"></i>
      @endif
    </div>

    <div class="detail-columns">
      <div class="detail-main">
        <section class="detail-section">
          <h2>Over deze activiteit</h2>

          @if ($activity->description)
            <p class="description">{{ $activity->description }}</p>
          @else
            <p class="muted">Er is (nog) geen omschrijving.</p>
          @endif
        </section>

        @if ($activity->includes_food)
          <section class="detail-section">
            <h2>Eten</h2>
            <p class="description">{{ $activity->food_description ?? 'Er is eten bij deze activiteit.' }}</p>
          </section>
        @endif
      </div>

      <aside class="detail-side">
        <section class="detail-section info-card">
          <h2>Aanmelden</h2>

          <div class="signup-panel">
            <div class="capacity">
              <div class="capacity-header">
                <span><i class="bi bi-people"></i> Deelnemers</span>
                <strong>{{ $registered }}{{ $max ? '/' . $max : '' }}</strong>
              </div>

              @if ($max)
                <div @class(['bar', 'full' => $isFull])>
                  <span style="width: {{ $percentage }}%"></span>
                </div>
                <p class="hint">
                  {{ $isFull ? 'Deze activiteit is vol.' : $spotsLeft . ' ' . ($spotsLeft === 1 ? 'plek' : 'plekken') . ' over' }}
                </p>
              @endif
            </div>

            <div class="signup-box {{ $state }}">
              @if ($state === 'open')
                <form method="POST" action="{{ route('external.store', $activity) }}" class="external-form">
                  @csrf

                  {{-- Verborgen veld tegen bots, mensen zien dit niet --}}
                  <div style="position: absolute; left: -9999px;" aria-hidden="true">
                    <label for="website">Website</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                  </div>

                  <div class="field">
                    <label for="name">Naam</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" maxlength="100" required autocomplete="name" @class(['has-error' => $errors->has('name')])>
                    @error('name')
                      <p class="field-error">{{ $message }}</p>
                    @enderror
                  </div>

                  <div class="field">
                    <label for="email">E-mailadres</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" maxlength="255" required autocomplete="email" @class(['has-error' => $errors->has('email')])>
                    @error('email')
                      <p class="field-error">{{ $message }}</p>
                    @enderror
                    <p class="hint">Hier sturen we de bevestiging naar toe.</p>
                  </div>

                  @if ($activity->allow_partner)
                    <label class="partner-toggle">
                      <input type="checkbox" name="brings_partner" value="1" @checked(old('brings_partner'))>
                      <span>Ik neem een +1 mee</span>
                    </label>
                  @endif

                  @if ($activity->includes_food)
                    <div class="field">
                      <label for="dietary_notes">Dieetwensen of allergieën <span class="optional">(optioneel)</span></label>
                      <textarea id="dietary_notes" name="dietary_notes" rows="3" maxlength="500" placeholder="Bijvoorbeeld vegetarisch, notenallergie. Ook voor je +1." @class(['has-error' => $errors->has('dietary_notes')])>{{ old('dietary_notes') }}</textarea>
                      @error('dietary_notes')
                        <p class="field-error">{{ $message }}</p>
                      @enderror
                    </div>
                  @endif

                  <button type="submit" class="btn-primary">
                    <i class="bi bi-person-plus"></i> Aanmelden
                  </button>

                  @if ($deadline)
                    <p class="hint">Aanmelden kan tot {{ $deadline->isoFormat('D MMMM, HH:mm') }}</p>
                  @endif
                </form>
              @else
                <div class="signup-state">
                  <i @class([
                      'bi',
                      'bi-lock-fill' => $state === 'closed',
                      'bi-calendar-check' => $state === 'ended',
                      'bi-x-circle-fill' => $state === 'full',
                  ])></i>
                  <div>
                    <p class="state-title">
                      {{ match ($state) {
                          'ended' => 'Activiteit is geweest',
                          'closed' => 'Aanmelding gesloten',
                          'full' => 'Activiteit is vol',
                      } }}
                    </p>
                    <p class="state-text">
                      {{ match ($state) {
                          'ended' => 'Aanmelden kan niet meer.',
                          'closed' => 'De deadline om je aan te melden is verstreken.',
                          'full' => 'Alle plekken zijn bezet.',
                      } }}
                    </p>
                  </div>
                </div>
              @endif
            </div>
          </div>
        </section>
      </aside>
    </div>
  </div>
</x-app-layout>

@push('styles')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  @vite(['resources/css/pages/activity-detail.css'])
@endpush

@php
  //* Datums in het Nederlands, op kopieen zodat het model niet wordt aangepast
  $start = $activity->starts_at?->copy()->locale('nl');
  $end = $activity->ends_at?->copy()->locale('nl');
  $deadline = $activity->signup_deadline?->copy()->locale('nl');

  $multiDay = $start && $end && !$start->isSameDay($end);
  $days = $multiDay ? (int) $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1 : 1;

  if (!$start) {
      $dateText = 'Datum volgt';
  } elseif ($multiDay) {
      $dateText = ucfirst($start->isoFormat('dddd D MMMM')) . ' t/m ' . $end->isoFormat('dddd D MMMM YYYY');
  } else {
      $dateText = ucfirst($start->isoFormat('dddd D MMMM YYYY'));
  }

  if (!$start) {
      $timeText = '—';
  } elseif ($multiDay) {
      $timeText = 'Vanaf ' . $start->format('H:i') . ($end ? ', tot ' . $end->format('H:i') . ' op de laatste dag' : '');
  } else {
      $timeText = $start->format('H:i') . ($end ? ' - ' . $end->format('H:i') : '');
  }

  //* Relatieve tijd, bijv. "over 12 dagen" of "nu bezig"
  if (!$start) {
      $startsInText = null;
  } elseif ($start->isFuture()) {
      $startsInText = $start->diffForHumans(['parts' => 1]);
  } elseif ($end?->isFuture()) {
      $startsInText = 'Nu bezig';
  } else {
      $startsInText = 'Afgelopen';
  }

  $deadlineClosed = $deadline?->isPast();
  $deadlineSoon = $deadline && !$deadlineClosed && $deadline->diffInHours(now(), true) < 48;
  $deadlineLeftText = $deadline && !$deadlineClosed
      ? 'Nog ' . $deadline->diffForHumans(['parts' => 1, 'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE])
      : null;
  $isDraft = $activity->status === \App\Models\Activity::STATUS_DRAFT;
  $registered = $activity->signups_count;
  $max = $activity->max_participants;
  $isFull = $max && $registered >= $max;

  $images = $activity->images;
  $partnerCount = $activity->signups->where('brings_partner', true)->count();
  $dietCount = $activity->signups->filter(fn ($signup) => filled($signup->dietary_notes))->count();

  if ($activity->personal_contribution === null) {
      $contributionText = '—';
  } elseif ((float) $activity->personal_contribution === 0.0) {
      $contributionText = 'Gratis';
  } else {
      $contributionText = '€ ' . number_format((float) $activity->personal_contribution, 2, ',', '.');
  }
@endphp

<x-app-layout>
  <div class="activity-detail-page">
    <header class="page-header">
      <a href="{{ route('activities.index') }}" class="back-link">
        <i class="bi bi-arrow-left"></i> Terug naar overzicht
      </a>

      <div class="title-row">
        <h1>{{ $activity->title ?? 'Naamloos concept' }}</h1>

        @if ($isDraft)
          <span class="status-pill">Concept</span>
        @endif
        <span class="status-pill signed-up" data-pill="signed-up" @if (! $isSignedUp) hidden @endif><i class="bi bi-check-lg"></i> Ingeschreven</span>
        <span class="status-pill full" data-pill="full" @if (! $isFull || $isSignedUp) hidden @endif>Vol</span>

        @if (auth()->user()?->isAdmin())
          <a href="{{ route('activities.edit', $activity) }}" class="edit-link">
            <i class="bi bi-pencil-square"></i> Bewerken
          </a>
        @endif
      </div>

      <ul class="key-facts">
        <li><i class="bi bi-calendar-event"></i> {{ $dateText }}</li>
        @if ($start)
          <li><i class="bi bi-clock"></i> {{ $timeText }}</li>
        @endif
        <li><i class="bi bi-wallet2"></i> {{ match (true) {
            $contributionText === '—' => 'Bijdrage volgt',
            $contributionText === 'Gratis' => 'Gratis',
            default => 'Eigen bijdrage ' . $contributionText,
        } }}</li>
        @if ($startsInText)
          <li class="highlight"><i class="bi bi-stopwatch"></i> {{ ucfirst($startsInText) }}</li>
        @endif
      </ul>
    </header>

    @if (session('status'))
      <p class="flash"><i class="bi bi-check-circle-fill"></i> {{ session('status') }}</p>
    @endif
    @if (session('error'))
      <p class="flash error"><i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}</p>
    @endif

    {{-- Foto's --}}
    <div @class(['hero', 'has-photo' => $images->isNotEmpty()]) {{ $images->count() > 1 ? 'data-carousel' : '' }}>
      @if ($images->isNotEmpty())
        <div class="slides" data-slides>
          @foreach ($images as $image)
            <img src="{{ $image->url() }}" alt="{{ $activity->title ?? 'Activiteit' }}">
          @endforeach
        </div>
      @else
        <i class="bi bi-calendar-event hero-icon"></i>
      @endif

      @if ($start)
        <div class="date-badge">
          <span class="day">{{ $start->format('j') }}</span>
          <span class="month">{{ rtrim($start->isoFormat('MMM'), '.') }}</span>
        </div>
      @endif

      @if ($images->count() > 1)
        <button type="button" class="carousel-btn prev" data-dir="-1" aria-label="Vorige foto"><i class="bi bi-chevron-left"></i></button>
        <button type="button" class="carousel-btn next" data-dir="1" aria-label="Volgende foto"><i class="bi bi-chevron-right"></i></button>
        <div class="dots">
          @foreach ($images as $image)
            <span @class(['active' => $loop->first])></span>
          @endforeach
        </div>
      @endif
    </div>

    <div class="detail-columns">
      <div class="detail-main">

        {{-- Omschrijving --}}
        <section class="detail-section">
          <h2>Over deze activiteit</h2>

          @if ($activity->description)
            <p class="description">{{ $activity->description }}</p>
          @else
            <p class="muted">Er is (nog) geen omschrijving.</p>
          @endif

          @if ($multiDay || $activity->includes_food || $activity->allow_partner || $activity->is_external)
            <div class="tags">
              @if ($multiDay)
                <span class="tag days">{{ $days }} dagen</span>
              @endif
              @if ($activity->includes_food)
                <span class="tag">Inclusief eten</span>
              @endif
              @if ($activity->allow_partner)
                <span class="tag">+1 toegestaan</span>
              @endif
              @if ($activity->is_external)
                <span class="tag">Extern</span>
              @endif
            </div>
          @endif
        </section>

        {{-- Eten --}}
        @if ($activity->includes_food)
          <section class="detail-section">
            <h2>Eten</h2>
            <p class="description">{{ $activity->food_description ?? '—' }}</p>
          </section>
        @endif

        {{-- Deelnemerslijst, alleen voor beheerders --}}
        @if (auth()->user()?->isAdmin())
          <section class="detail-section">
            <h2>
              Ingeschreven
              <span class="count">{{ $registered }}</span>
              @if ($partnerCount > 0)
                <span class="count partners">+ {{ $partnerCount }} {{ $partnerCount === 1 ? 'partner' : 'partners' }}</span>
              @endif
              @if ($dietCount > 0)
                <span class="count partners">{{ $dietCount }} {{ $dietCount === 1 ? 'dieetwens' : 'dieetwensen' }}</span>
              @endif
            </h2>

            @if ($activity->signups->isEmpty())
              <p class="muted">Nog niemand ingeschreven.</p>
            @else
              <ul class="participants">
                @foreach ($activity->signups as $signup)
                  <li>
                    <span class="avatar">{{ mb_strtoupper(mb_substr($signup->user->name, 0, 1)) }}</span>
                    <div>
                      <p class="name">
                        {{ $signup->user->name }}
                        @if ($signup->brings_partner)
                          <span class="partner-badge">+1</span>
                        @endif
                      </p>
                      <p class="email">{{ $signup->user->email }}</p>
                      @if (filled($signup->dietary_notes))
                        <p class="diet"><i class="bi bi-cup-hot"></i> {{ $signup->dietary_notes }}</p>
                      @endif
                    </div>
                  </li>
                @endforeach
              </ul>
            @endif
          </section>
        @endif
      </div>

      {{-- Praktische info --}}
      <aside class="detail-side">
        <section class="detail-section info-card">
          <h2>Praktisch</h2>

          <ul class="info-list">
            <li>
              <i class="bi bi-hourglass-split"></i>
              <div>
                <p class="label">Inschrijven tot</p>
                <p @class(['value', 'closed' => $deadlineClosed])>
                  @if (!$deadline)
                    —
                  @elseif ($deadlineClosed)
                    Inschrijving gesloten
                  @else
                    {{ ucfirst($deadline->isoFormat('dddd D MMMM, HH:mm')) }}
                  @endif
                </p>
                @if ($deadlineLeftText)
                  <p @class(['sub', 'urgent' => $deadlineSoon])>{{ $deadlineLeftText }}</p>
                @endif
              </div>
            </li>
            <li>
              <i class="bi bi-cup-hot"></i>
              <div>
                <p class="label">Eten</p>
                <p class="value">{{ $activity->includes_food ? 'Inclusief eten' : 'Geen eten inbegrepen' }}</p>
              </div>
            </li>
            <li>
              <i class="bi bi-person-plus"></i>
              <div>
                <p class="label">+1</p>
                <p class="value">{{ $activity->allow_partner ? 'Toegestaan' : 'Niet toegestaan' }}</p>
              </div>
            </li>
          </ul>


          {{-- Bezetting en inschrijven, zonder de pagina te herladen --}}
          <livewire:signup-panel :activity="$activity" />
        </section>
      </aside>
    </div>
  </div>

  <script>
    // Het inschrijfblok (Livewire) meldt als je bent in- of uitgeschreven, zodat de labels bij de titel meegaan
    document.addEventListener('livewire:init', () => {
      Livewire.on('signup-changed', ({ signedUp, full }) => {
        document.querySelector('[data-pill="signed-up"]')?.toggleAttribute('hidden', ! signedUp);
        document.querySelector('[data-pill="full"]')?.toggleAttribute('hidden', ! full || signedUp);
      });
    });

    // Fotocarousel: pijltjes en bolletjes. Swipen werkt via scroll-snap in de CSS.
    (() => {
      const carousel = document.querySelector('[data-carousel]');
      if (! carousel) return;

      const slides = carousel.querySelector('[data-slides]');
      const dots = carousel.querySelectorAll('.dots span');
      const count = dots.length;
      const current = () => Math.round(slides.scrollLeft / slides.clientWidth);

      carousel.querySelectorAll('[data-dir]').forEach((button) => {
        button.addEventListener('click', () => {
          const target = (current() + Number(button.dataset.dir) + count) % count;
          slides.scrollTo({ left: target * slides.clientWidth, behavior: 'smooth' });
        });
      });

      slides.addEventListener('scroll', () => {
        dots.forEach((dot, index) => dot.classList.toggle('active', index === current()));
      }, { passive: true });
    })();
  </script>
</x-app-layout>

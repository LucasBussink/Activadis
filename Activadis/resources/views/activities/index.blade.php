@push('styles')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  @vite(['resources/css/pages/activities.css'])
@endpush

<x-app-layout>
  <div class="activities-page">
    <header class="page-header">
      <div>
        <h1>Activiteiten</h1>
<p class="subtitle" id="activity-subtitle">
          @php $isAdmin = auth()->user()?->isAdmin(); @endphp
          @if ($isAdmin)
            @php $conceptCount = $activities->where('status', 'draft')->count(); @endphp
            {{ $activities->count() - $conceptCount }} gepubliceerd, {{ $conceptCount }} {{ $conceptCount === 1 ? 'concept' : 'concepten' }}
          @else
            {{ $activities->count() }} {{ $activities->count() === 1 ? 'activiteit' : 'activiteiten' }}
          @endif
        </p>
      </div>

      <div class="search-bar">
        <i class="bi bi-search"></i>
        <input type="search" id="activity-search" placeholder="Zoek een activiteit..." aria-label="Zoek activiteiten" autocomplete="off">
        <button type="button" class="search-clear" id="search-clear" aria-label="Zoekopdracht wissen" hidden><i class="bi bi-x-lg"></i></button>
      </div>

      @if (auth()->user()?->isAdmin())
        <a href="{{ route('activities.create') }}" class="btn-primary">
          <i class="bi bi-plus-lg"></i> Nieuwe activiteit
        </a>
      @endif
    </header>

    @if ($activities->isNotEmpty())
      <div class="toolbar">
        <div class="chips" role="group" aria-label="Sorteren" id="sort-chips">
          <span class="chips-label">Sorteer op</span>
          <button type="button" class="chip active" data-sort="start" aria-pressed="true">Eerstvolgende</button>
          <button type="button" class="chip" data-sort="deadline" aria-pressed="false">Inschrijftermijn</button>
          <button type="button" class="chip" data-sort="title" aria-pressed="false">A tot Z</button>
        </div>

        @if ($isAdmin)
          <label class="toggle-expired">
            <input type="checkbox" id="show-expired">
            <i class="bi bi-clock-history"></i>
            <span>Toon verlopen</span>
          </label>
        @endif
      </div>
    @endif

    @if (session('status'))
      <p class="flash"><i class="bi bi-check-circle-fill"></i> {{ session('status') }}</p>
    @endif

    @if ($activities->isEmpty())
      <div class="empty-state">
        <i class="bi bi-calendar-x"></i>
        <p>Nog geen activiteiten.</p>
      </div>
    @else
            @php
        $sections = [
          'Gepubliceerd' => $activities->where('status', 'published')->sortBy('starts_at'),
          'Concepten' => $activities->where('status', 'draft')->sortBy('starts_at'),
        ];
      @endphp

      @foreach ($sections as $sectionTitle => $sectionActivities)
        @continue($sectionActivities->isEmpty())

        <section class="section">
          @if (auth()->user()?->isAdmin())
            <h2 class="section-title">
              {{ $sectionTitle }}
              <span class="count">{{ $sectionActivities->count() }}</span>
            </h2>
          @endif

          <div class="cards">
            @foreach ($sectionActivities as $activity)
              @php
                //* Datums in het Nederlands, op kopieen zodat het model niet wordt aangepast
                $start = $activity->starts_at?->copy()->locale('nl');
                //* ends_at staat (nog) niet in de casts van het model, dus eerst omzetten naar Carbon
                $end = $activity->ends_at ? \Illuminate\Support\Carbon::parse($activity->ends_at)->locale('nl') : null;
                $deadline = $activity->signup_deadline?->copy()->locale('nl');

                $multiDay = $start && $end && !$start->isSameDay($end);
                $days = $multiDay ? (int) $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1 : 1;

                if (!$start) {
                    $dateText = 'Datum volgt';
                } elseif ($multiDay) {
                    $dateText = ucfirst($start->isoFormat('ddd D MMM')) . ' t/m ' . $end->isoFormat('ddd D MMM');
                } else {
                    $dateText = ucfirst($start->isoFormat('dddd D MMMM'));
                }

                if (!$start) {
                    $timeText = null;
                } elseif ($multiDay) {
                    $timeText = 'Vanaf ' . $start->format('H:i');
                } else {
                    $timeText = $start->format('H:i') . ($end ? ' - ' . $end->format('H:i') : '');
                }

                $expired = ($end ?? $start)?->isPast() ?? false;
                $deadlineClosed = $deadline?->isPast();
                $registered = $activity->signups_count ?? 0;
                $images = $activity->images;
                $isFull = $activity->max_participants && $registered >= $activity->max_participants;
              @endphp

              <article
                @class(['card', 'is-expired' => $expired])
                @if ($expired) hidden @endif
                data-status="{{ $activity->status }}"
                data-expired="{{ $expired ? '1' : '0' }}"
                data-title="{{ mb_strtolower($activity->title ?? '') }}"
                data-search="{{ mb_strtolower(($activity->title ?? '').' '.($activity->description ?? '').' '.$dateText) }}"
                data-start="{{ $start?->timestamp ?? '' }}"
                data-deadline="{{ $deadline?->timestamp ?? '' }}"
              >
                <div @class(['card-media', 'has-photo' => $images->isNotEmpty()]) {{ $images->count() > 1 ? 'data-carousel' : '' }}>
                  @if ($images->isNotEmpty())
                    <div class="slides" data-slides>
                      @foreach ($images as $image)
                        <img src="{{ $image->url() }}" alt="{{ $activity->title ?? 'Activiteit' }}" loading="lazy">
                      @endforeach
                    </div>
                  @else
                    <i class="bi bi-calendar-event card-media-icon"></i>
                  @endif

                  @if ($start)
                    <div class="date-badge">
                      <span class="day">{{ $start->format('j') }}</span>
                      <span class="month">{{ rtrim($start->isoFormat('MMM'), '.') }}</span>
                    </div>
                  @else
                    <div class="date-badge">
                      <span class="day"><i class="bi bi-calendar"></i></span>
                      <span class="month">Volgt</span>
                    </div>
                  @endif

                  @if ($activity->status === 'draft')
                    <span class="status-pill">Concept</span>
                  @elseif ($expired)
                    <span class="status-pill expired">Verlopen</span>
                  @elseif ($activity->is_signed_up)
                    <span class="status-pill signed-up"><i class="bi bi-check-lg"></i> Ingeschreven</span>
                  @elseif ($isFull)
                    <span class="status-pill full">Vol</span>
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

                <div class="card-body">
                  <p class="card-date">{{ $dateText }}</p>

                  <h2 class="card-title">
                    <a href="{{ route('activities.show', $activity) }}">{{ $activity->title ?? 'Naamloze activiteit' }}</a>
                  </h2>

                  @if ($activity->description)
                    <p class="card-description">{{ $activity->description }}</p>
                  @endif

                  <ul class="card-meta">
                    @if ($timeText)
                      <li><i class="bi bi-clock"></i> {{ $timeText }}</li>
                    @endif
                    @if ($activity->max_participants)
                      <li><i class="bi bi-people"></i> {{ $registered }}/{{ $activity->max_participants }} ingeschreven</li>
                    @else
                      <li><i class="bi bi-people"></i> {{ $registered }} ingeschreven</li>
                    @endif
                  </ul>

                  @if ($multiDay || $activity->includes_food || $activity->allow_partner || $activity->is_external)
                    <div class="card-tags">
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

                  <div class="card-footer">
                    @if ($deadline)
                      <span class="deadline {{ $deadlineClosed ? 'closed' : '' }}">
                        {{ $deadlineClosed ? 'Inschrijving gesloten' : 'Inschrijven tot ' . rtrim($deadline->isoFormat('D MMM'), '.') }}
                      </span>
                    @else
                      <span class="deadline">Geen inschrijfdeadline</span>
                    @endif

                    <span class="card-cta">Bekijk <i class="bi bi-arrow-right"></i></span>
                  </div>
                </div>
              </article>
            @endforeach
          </div>
        </section>
      @endforeach

      <div class="empty-state" id="no-results" hidden>
        <i class="bi bi-search"></i>
        <p>Geen activiteiten gevonden.</p>
      </div>
    @endif
  </div>
  <script>
    // Zoeken, sorteren en verlopen verbergen. Alles gebeurt in de browser, bij elke toetsaanslag.
    (() => {
      const input = document.getElementById('activity-search');
      if (! input) return;

      const clearButton = document.getElementById('search-clear');
      const chips = document.querySelectorAll('#sort-chips .chip');
      const expiredToggle = document.getElementById('show-expired');
      const noResults = document.getElementById('no-results');
      const subtitle = document.getElementById('activity-subtitle');
      const cards = Array.from(document.querySelectorAll('.cards .card'));
      const isAdmin = Boolean(document.querySelector('.section-title'));

      let sortKey = 'start';

      // Hoofdletters en accenten negeren: "cafe" vindt ook "café"
      const normalize = (text) => text.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
      cards.forEach((card) => { card.dataset.search = normalize(card.dataset.search); });

      // Activiteiten zonder datum komen altijd onderaan
      const number = (value) => (value === '' ? Infinity : Number(value));
      const comparers = {
        start: (a, b) => number(a.dataset.start) - number(b.dataset.start),
        deadline: (a, b) => number(a.dataset.deadline) - number(b.dataset.deadline),
        title: (a, b) => a.dataset.title.localeCompare(b.dataset.title, 'nl'),
      };

      const plural = (n, one, many) => `${n} ${n === 1 ? one : many}`;

      const update = () => {
        const query = normalize(input.value.trim());
        const showExpired = expiredToggle?.checked ?? false;
        clearButton.hidden = query === '';

        // Filteren
        cards.forEach((card) => {
          const matches = card.dataset.search.includes(query);
          const hiddenByExpiry = card.dataset.expired === '1' && ! showExpired;
          card.hidden = ! matches || hiddenByExpiry;
        });

        // Sorteren binnen elke sectie
        document.querySelectorAll('.cards').forEach((container) => {
          Array.from(container.children)
            .sort(comparers[sortKey])
            .forEach((card) => container.appendChild(card));
        });

        // Lege secties verbergen en tellers bijwerken
        let visibleTotal = 0;
        let visibleDrafts = 0;
        document.querySelectorAll('.section').forEach((section) => {
          const visible = section.querySelectorAll('.card:not([hidden])');
          section.hidden = visible.length === 0;
          const count = section.querySelector('.count');
          if (count) count.textContent = visible.length;
          visibleTotal += visible.length;
          visibleDrafts += section.querySelectorAll('.card[data-status="draft"]:not([hidden])').length;
        });

        if (noResults) noResults.hidden = visibleTotal > 0;

        if (subtitle) {
          subtitle.textContent = isAdmin
            ? `${visibleTotal - visibleDrafts} gepubliceerd, ${plural(visibleDrafts, 'concept', 'concepten')}`
            : plural(visibleTotal, 'activiteit', 'activiteiten');
        }
      };

      input.addEventListener('input', update);
      expiredToggle?.addEventListener('change', update);

      clearButton.addEventListener('click', () => {
        input.value = '';
        input.focus();
        update();
      });

      // Escape leegt het zoekveld
      input.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && input.value !== '') {
          input.value = '';
          update();
        }
      });

      chips.forEach((chip) => {
        chip.addEventListener('click', () => {
          sortKey = chip.dataset.sort;
          chips.forEach((other) => {
            const active = other === chip;
            other.classList.toggle('active', active);
            other.setAttribute('aria-pressed', active);
          });
          update();
        });
      });

      update();
    })();

    // Fotocarousel op de kaartjes: pijltjes, bolletjes en automatisch doorbladeren.
    // Swipen werkt via scroll-snap in de CSS.
    (() => {
      const INTERVAL = 2500; // ms tussen twee foto's
      const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

      document.querySelectorAll('[data-carousel]').forEach((carousel) => {
        const slides = carousel.querySelector('[data-slides]');
        const dots = carousel.querySelectorAll('.dots span');
        const count = dots.length;
        const card = carousel.closest('.card');

        let timer = null;
        let visible = false; // kaartje staat in beeld
        let paused = false;  // muis erboven, focus erin of aangeraakt

        const current = () => Math.round(slides.scrollLeft / slides.clientWidth);

        const goTo = (direction) => {
          const target = (current() + direction + count) % count;
          slides.scrollTo({ left: target * slides.clientWidth, behavior: 'smooth' });
        };

        // Plant de volgende foto in. Een beetje willekeur zodat alle kaartjes niet tegelijk wisselen.
        const schedule = () => {
          clearTimeout(timer);
          if (! visible || paused || reduceMotion) return;
          timer = setTimeout(() => {
            goTo(1);
            schedule();
          }, INTERVAL + Math.random() * 1500);
        };

        carousel.querySelectorAll('[data-dir]').forEach((button) => {
          button.addEventListener('click', () => {
            goTo(Number(button.dataset.dir));
            schedule();
          });
        });

        slides.addEventListener('scroll', () => {
          dots.forEach((dot, index) => dot.classList.toggle('active', index === current()));
        }, { passive: true });

        // Pauzeer zodra iemand met het kaartje bezig is, ga verder als hij weg is
        const pause = () => { paused = true; schedule(); };
        const resume = () => { paused = false; schedule(); };
        card.addEventListener('mouseenter', pause);
        card.addEventListener('mouseleave', resume);
        card.addEventListener('focusin', pause);
        card.addEventListener('focusout', resume);
        card.addEventListener('touchstart', pause, { passive: true });
        card.addEventListener('touchend', resume, { passive: true });

        // Alleen doorbladeren als het kaartje in beeld is
        new IntersectionObserver(([entry]) => {
          visible = entry.isIntersecting;
          schedule();
        }).observe(card);
      });
    })();
  </script>
</x-app-layout>

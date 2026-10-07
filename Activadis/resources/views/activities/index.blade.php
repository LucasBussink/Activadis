@push('styles')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  @vite(['resources/css/pages/activities.css'])
@endpush

<x-app-layout>
  <div class="activities-page">
    <header class="page-header">
      <div>
        <h1>Activiteiten</h1>
                <p class="subtitle">
          @if (auth()->user()?->isAdmin())
            @php $conceptCount = $activities->where('status', 'draft')->count(); @endphp
            {{ $activities->count() - $conceptCount }} gepubliceerd, {{ $conceptCount }} {{ $conceptCount === 1 ? 'concept' : 'concepten' }}
          @else
            {{ $activities->count() }} {{ $activities->count() === 1 ? 'activiteit' : 'activiteiten' }}
          @endif
        </p>
      </div>

      @if (auth()->user()?->isAdmin())
        <a href="{{ route('activities.create') }}" class="btn-primary">
          <i class="bi bi-plus-lg"></i> Nieuwe activiteit
        </a>
      @endif
    </header>

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

                $deadlineClosed = $deadline?->isPast();
                $registered = $activity->registrations_count ?? 0;
                $isFull = $activity->max_participants && $registered >= $activity->max_participants;
              @endphp

              <a href="{{ route('activities.show', $activity) }}" class="card">
                <div class="card-media">
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
                  @elseif ($isFull)
                    <span class="status-pill full">Vol</span>
                  @endif

                  <i class="bi bi-calendar-event card-media-icon"></i>
                </div>

                <div class="card-body">
                  <p class="card-date">{{ $dateText }}</p>

                  <h2 class="card-title">{{ $activity->title ?? 'Naamloze activiteit' }}</h2>

                  @if ($activity->description)
                    <p class="card-description">{{ $activity->description }}</p>
                  @endif

                  <ul class="card-meta">
                    @if ($timeText)
                      <li><i class="bi bi-clock"></i> {{ $timeText }}</li>
                    @endif
                    <li>
                      <i class="bi bi-tag"></i>
                      {{ $activity->cost !== null && (float) $activity->cost > 0 ? '€ ' . number_format((float) $activity->cost, 2, ',', '.') : 'Gratis' }}
                    </li>
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
              </a>
            @endforeach
          </div>
        </section>
      @endforeach
    @endif
  </div>
</x-app-layout>

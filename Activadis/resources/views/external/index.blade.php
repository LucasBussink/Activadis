@push('styles')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  @vite(['resources/css/pages/activities.css'])
@endpush

<x-app-layout>
  <div class="activities-page">
    <header class="page-header">
      <div>
        <h1>Activiteiten voor externen</h1>
        <p class="subtitle">
          {{ $activities->count() }} {{ $activities->count() === 1 ? 'activiteit' : 'activiteiten' }}, aanmelden kan zonder account
        </p>
      </div>
    </header>

    @if ($activities->isEmpty())
      <div class="empty-state">
        <i class="bi bi-calendar-x"></i>
        <p>Er zijn op dit moment geen activiteiten voor externen.</p>
      </div>
    @else
      <div class="cards">
        @foreach ($activities as $activity)
          @php
            $start = $activity->starts_at?->copy()->locale('nl');
            $end = $activity->ends_at ? \Illuminate\Support\Carbon::parse($activity->ends_at)->locale('nl') : null;
            $deadline = $activity->signup_deadline?->copy()->locale('nl');

            $multiDay = $start && $end && ! $start->isSameDay($end);
            $days = $multiDay ? (int) $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1 : 1;

            if (! $start) {
                $dateText = 'Datum volgt';
            } elseif ($multiDay) {
                $dateText = ucfirst($start->isoFormat('ddd D MMM')) . ' t/m ' . $end->isoFormat('ddd D MMM');
            } else {
                $dateText = ucfirst($start->isoFormat('dddd D MMMM'));
            }

            if (! $start) {
                $timeText = null;
            } elseif ($multiDay) {
                $timeText = 'Vanaf ' . $start->format('H:i');
            } else {
                $timeText = $start->format('H:i') . ($end ? ' - ' . $end->format('H:i') : '');
            }

            $deadlineClosed = $deadline?->isPast();
            $registered = $activity->signups_count + $activity->external_signups_count;
            $isFull = $activity->max_participants && $registered >= $activity->max_participants;
            $image = $activity->images->first();
          @endphp

          <article class="card">
            <div @class(['card-media', 'has-photo' => $image])>
              @if ($image)
                <div class="slides">
                  <img src="{{ $image->url() }}" alt="{{ $activity->title ?? 'Activiteit' }}" loading="lazy">
                </div>
              @else
                <i class="bi bi-calendar-event card-media-icon"></i>
              @endif

              @if ($start)
                <div class="date-badge">
                  <span class="day">{{ $start->format('j') }}</span>
                  <span class="month">{{ rtrim($start->isoFormat('MMM'), '.') }}</span>
                </div>
              @endif

              @if ($isFull)
                <span class="status-pill full">Vol</span>
              @endif
            </div>

            <div class="card-body">
              <p class="card-date">{{ $dateText }}</p>

              <h2 class="card-title">
                <a href="{{ route('external.show', $activity) }}">{{ $activity->title ?? 'Naamloze activiteit' }}</a>
              </h2>

              @if ($activity->description)
                <p class="card-description">{{ $activity->description }}</p>
              @endif

              <ul class="card-meta">
                @if ($timeText)
                  <li><i class="bi bi-clock"></i> {{ $timeText }}</li>
                @endif
                <li><i class="bi bi-people"></i> {{ $registered }}{{ $activity->max_participants ? '/' . $activity->max_participants : '' }} aangemeld</li>
              </ul>

              @if ($multiDay || $activity->includes_food || $activity->allow_partner)
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
                </div>
              @endif

              <div class="card-footer">
                @if ($deadline)
                  <span class="deadline {{ $deadlineClosed ? 'closed' : '' }}">
                    {{ $deadlineClosed ? 'Aanmelding gesloten' : 'Aanmelden tot ' . rtrim($deadline->isoFormat('D MMM'), '.') }}
                  </span>
                @else
                  <span class="deadline">Geen deadline</span>
                @endif

                <span class="card-cta">Bekijk <i class="bi bi-arrow-right"></i></span>
              </div>
            </div>
          </article>
        @endforeach
      </div>
    @endif
  </div>
</x-app-layout>

@push('styles')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  @vite(['resources/css/pages/activity-detail.css'])
@endpush

<x-app-layout>
  <div class="activity-detail-page">
    <div class="detail-columns" style="grid-template-columns: minmax(0, 560px);">
      <section class="detail-section info-card">
        @if (! $signup)
          <h2>Aanmelding niet gevonden</h2>
          <p class="description">Deze link is ongeldig, of je hebt je al afgemeld.</p>
          <p style="margin-top: 16px;"><a href="{{ route('external.index') }}" class="back-link"><i class="bi bi-arrow-left"></i> Naar de activiteiten</a></p>
        @elseif ($done)
          <h2>Je bent afgemeld</h2>
          <p class="description">Jammer dat je niet kunt komen. Je plek bij <strong>{{ $signup->activity->title }}</strong> is vrijgegeven.</p>
          <p style="margin-top: 16px;"><a href="{{ route('external.index') }}" class="back-link"><i class="bi bi-arrow-left"></i> Naar de activiteiten</a></p>
        @elseif (! $canCancel)
          <h2>Afmelden kan niet meer</h2>
          <p class="description">De aanmelding voor <strong>{{ $signup->activity->title }}</strong> is gesloten. Neem contact op met de organisatie als je toch niet kunt komen.</p>
        @else
          <h2>Afmelden voor {{ $signup->activity->title }}?</h2>
          <p class="description">Je staat aangemeld als {{ $signup->name }} ({{ $signup->email }}). Als je je afmeldt, komt je plek vrij.</p>

          <form method="POST" action="{{ route('external.destroy', $signup->token) }}" style="margin-top: 16px;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-primary"><i class="bi bi-x-lg"></i> Ja, meld me af</button>
          </form>
        @endif
      </section>
    </div>
  </div>
</x-app-layout>

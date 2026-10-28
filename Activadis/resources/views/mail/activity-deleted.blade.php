<x-mail::message>
# Activiteit geannuleerd

Helaas gaat de activiteit **{{ $activity->title ?? 'Naamloos' }}** ({{ $activity->period() }}) niet door.
Je inschrijving is daarmee komen te vervallen.

<x-mail::button :url="$isExternal ? route('external.index') : route('activities.index')">
Bekijk andere activiteiten
</x-mail::button>

Groet,<br>
{{ config('app.name') }}
</x-mail::message>

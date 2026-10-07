<x-app-layout>
    <h1 class="mb-6 text-2xl font-semibold text-brand-secondary">Activiteiten</h1>

    @if ($activities->isEmpty())
        <p class="text-gray-500">Nog geen activiteiten.</p>
    @else
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($activities as $activity)
                <a href="{{ route('activities.show', $activity) }}" class="flex flex-col rounded-lg border border-gray-200 bg-white p-6 transition hover:border-brand-primary hover:shadow-sm">
                    <div class="mb-3 flex items-start justify-between gap-2">
                        <h2 class="text-base font-semibold text-brand-secondary">{{ $activity->title ?? 'Naamloos concept' }}</h2>
                        <span @class([
                            'inline-flex shrink-0 rounded-full px-2 py-0.5 text-[11px] font-medium',
                            'bg-brand-accent text-brand-secondary' => $activity->status === 'draft',
                            'bg-brand-primary/10 text-brand-primary' => $activity->status === 'published',
                        ])>
                            {{ $activity->status === 'draft' ? 'Concept' : 'Gepubliceerd' }}
                        </span>
                    </div>

                    {{-- Belangrijke info: praktische zaken die direct relevant zijn voor deelname --}}
                    <dl class="grid grid-cols-2 gap-x-3 gap-y-2 rounded-md bg-brand-accent/60 p-3 text-sm">
                        <div>
                            <dt class="text-xs text-brand-secondary/70">Start</dt>
                            <dd class="font-semibold text-brand-secondary">{{ \App\Models\Activity::formatDate($activity->starts_at) ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-brand-secondary/70">Einde</dt>
                            <dd class="font-semibold text-brand-secondary">{{ \App\Models\Activity::formatDate($activity->ends_at) ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-brand-secondary/70">Inschrijven tot</dt>
                            <dd class="font-semibold text-brand-secondary">{{ \App\Models\Activity::formatDate($activity->signup_deadline) ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-brand-secondary/70">Eigen bijdrage</dt>
                            <dd class="font-semibold text-brand-secondary">
                                @if ($activity->personal_contribution === null)
                                    —
                                @elseif ($activity->personal_contribution > 0)
                                    € {{ number_format((float) $activity->personal_contribution, 2, ',', '.') }}
                                @else
                                    Gratis
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-brand-secondary/70">Deelnemers</dt>
                            <dd class="font-semibold text-brand-secondary">
                                @if ($activity->min_participants === null && $activity->max_participants === null)
                                    —
                                @elseif ($activity->max_participants === null)
                                    min. {{ $activity->min_participants }}
                                @elseif ($activity->min_participants === null)
                                    max. {{ $activity->max_participants }}
                                @else
                                    {{ $activity->min_participants }}–{{ $activity->max_participants }}
                                @endif
                            </dd>
                        </div>
                    </dl>

                    {{-- Algemene info: aanvullende, minder cruciale details --}}
                    <div class="mt-4 space-y-3">
                        @if ($activity->description)
                            <p class="line-clamp-2 text-sm text-gray-500">{{ $activity->description }}</p>
                        @endif

                        <div class="flex flex-wrap gap-1.5">
                            @if ($activity->includes_food)
                                <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-600">Inclusief eten</span>
                            @endif
                            @if ($activity->allow_partner)
                                <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-600">+1 toegestaan</span>
                            @endif
                            @if ($activity->is_external)
                                <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-600">Extern</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</x-app-layout>

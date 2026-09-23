@extends('layouts.app')

@section('title', 'Activiteiten')

@section('content')
    <h1 class="mb-6 text-2xl font-semibold text-brand-secondary">Activiteiten</h1>

    @if ($activities->isEmpty())
        <p class="text-gray-500">Nog geen activiteiten.</p>
    @else
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($activities as $activity)
                <div class="flex flex-col rounded-lg border border-gray-200 bg-white p-6">
                    <div class="mb-3 flex items-start justify-between gap-2">
                        <h2 class="text-base font-semibold text-brand-secondary">{{ $activity->title }}</h2>
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
                            <dt class="text-xs text-brand-secondary/70">Datum &amp; tijd</dt>
                            <dd class="font-semibold text-brand-secondary">{{ $activity->starts_at->format('d-m-Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-brand-secondary/70">Inschrijven tot</dt>
                            <dd class="font-semibold text-brand-secondary">{{ $activity->signup_deadline?->format('d-m-Y H:i') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-brand-secondary/70">Kosten</dt>
                            <dd class="font-semibold text-brand-secondary">
                                {{ $activity->cost !== null ? '€ '.number_format((float) $activity->cost, 2, ',', '.') : 'Gratis' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-brand-secondary/70">Deelnemers</dt>
                            <dd class="font-semibold text-brand-secondary">{{ $activity->min_participants ?? '—' }}–{{ $activity->max_participants ?? '—' }}</dd>
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
                                <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-600">Partner toegestaan</span>
                            @endif
                            @if ($activity->is_external)
                                <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-600">Extern</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection

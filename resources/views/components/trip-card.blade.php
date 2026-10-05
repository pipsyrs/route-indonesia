@props(['trip', 'query', 'facilities'])
@php
    $hasEnoughSeats = $trip['seats_left'] >= (int) ($query['passengers'] ?? 1);
    $isSeatsLow = $trip['seats_left'] <= 3;
    $tripUrl = route('catalog.show', ['id' => $trip['id'], 'date' => $query['date']]);
@endphp

<article class="card grid gap-5 p-5 transition-shadow hover:shadow-md hover:shadow-brand-900/5 sm:p-6 md:grid-cols-[1fr_auto] md:gap-8"
         data-trip-card
         data-price="{{ $trip['price'] }}"
         data-depart="{{ $trip['depart'] }}"
         data-duration="{{ $trip['duration_minutes'] }}"
         data-rating="{{ $trip['rating'] }}"
         data-operator="{{ $trip['operator'] }}"
         data-facilities="{{ implode(',', $trip['facilities']) }}"
         data-seats="{{ $trip['seats_left'] }}"
         aria-labelledby="trip-{{ $trip['id'] }}-title">
    <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
            <h3 id="trip-{{ $trip['id'] }}-title" class="text-base font-bold text-ink">{{ $trip['operator'] }}</h3>
            <x-rating :value="$trip['rating']" />
            <span class="text-sm text-ink-subtle">{{ $trip['vehicle'] }}</span>
        </div>

        <div class="mt-4 flex items-center gap-3 sm:gap-4">
            <div>
                <p class="text-xl font-bold tabular-nums text-ink">{{ $trip['depart'] }}</p>
                <p class="text-xs text-ink-subtle">{{ $trip['origin'] }}</p>
            </div>
            <div class="flex min-w-16 flex-1 flex-col items-center gap-1 sm:max-w-40">
                <span class="text-xs font-medium text-ink-muted">{{ $trip['duration'] }}</span>
                <span class="relative h-px w-full bg-line-strong" aria-hidden="true">
                    <span class="absolute top-1/2 left-0 size-1.5 -translate-y-1/2 rounded-full bg-line-strong"></span>
                    <span class="absolute top-1/2 right-0 size-1.5 -translate-y-1/2 rounded-full bg-brand-500"></span>
                </span>
                <span class="sr-only">Durasi {{ $trip['duration'] }}</span>
            </div>
            <div>
                <p class="text-xl font-bold tabular-nums text-ink">
                    {{ $trip['arrive'] }}@if ($trip['arrives_next_day'])<sup class="ml-0.5 text-[10px] font-semibold text-warn-ink">+1</sup>@endif
                </p>
                <p class="text-xs text-ink-subtle">{{ $trip['destination'] }}</p>
            </div>
        </div>

        <ul class="mt-4 flex flex-wrap gap-1.5" aria-label="Fasilitas">
            @foreach ($trip['facilities'] as $code)
                <li><x-facility-badge :code="$code" :label="$facilities[$code] ?? $code" /></li>
            @endforeach
        </ul>
    </div>

    <div class="flex items-end justify-between gap-4 border-t border-line pt-4 md:flex-col md:items-end md:justify-between md:border-t-0 md:border-l md:pt-0 md:pl-8">
        <div class="md:text-right">
            <p class="text-xs text-ink-subtle">per orang</p>
            <x-price :amount="$trip['price']" class="text-xl font-bold text-ink" />
            <p @class([
                'mt-1 text-xs font-medium',
                'text-warn-ink' => $isSeatsLow,
                'text-ink-muted' => ! $isSeatsLow,
            ])>
                Sisa {{ $trip['seats_left'] }} kursi
            </p>
        </div>

        @if ($hasEnoughSeats)
            <a href="{{ $tripUrl }}" class="btn-primary px-6">
                Lihat detail
                <span class="sr-only">jadwal {{ $trip['operator'] }} pukul {{ $trip['depart'] }}</span>
            </a>
        @else
            <button type="button" class="btn-outline" disabled>Kursi tidak cukup</button>
        @endif
    </div>
</article>

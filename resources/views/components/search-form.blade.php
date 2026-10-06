@props(['cities', 'query' => null, 'idPrefix' => 'cari'])
@php
    $selectedFrom = $query['from'] ?? 'Jakarta';
    $selectedTo = $query['to'] ?? 'Bandung';
    $selectedDate = $query['date'] ?? now()->addDay()->toDateString();
@endphp

<form action="{{ route('catalog.index') }}" method="get" novalidate data-module="search-form"
      data-error-same-city="{{ __('Origin and destination cannot be the same.') }}"
      data-error-no-date="{{ __('Choose a departure date.') }}"
      data-error-past-date="{{ __('Departure date cannot be earlier than today.') }}"
      {{ $attributes->merge(['class' => 'grid gap-3']) }}>
    <div class="grid gap-3 md:grid-cols-[1fr_auto_1fr_minmax(0,0.9fr)_auto] md:items-end">
        <div>
            <label for="{{ $idPrefix }}-from" class="form-label">{{ __('From') }}</label>
            <div class="relative">
                <x-icon name="map-pin" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-ink-subtle" />
                <select id="{{ $idPrefix }}-from" name="from" class="form-input appearance-none pl-9" data-search-from required>
                    @foreach ($cities as $city)
                        <option value="{{ $city }}" @selected($city === $selectedFrom)>{{ $city }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <button type="button" class="btn-outline size-11 self-end justify-self-center rounded-md p-0 max-md:-my-1 max-md:rotate-90" data-search-swap>
            <x-icon name="arrows-exchange" class="size-5" />
            <span class="sr-only">{{ __('Swap origin and destination') }}</span>
        </button>

        <div>
            <label for="{{ $idPrefix }}-to" class="form-label">{{ __('To') }}</label>
            <div class="relative">
                <x-icon name="map-2" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-ink-subtle" />
                <select id="{{ $idPrefix }}-to" name="to" class="form-input appearance-none pl-9" data-search-to required>
                    @foreach ($cities as $city)
                        <option value="{{ $city }}" @selected($city === $selectedTo)>{{ $city }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label for="{{ $idPrefix }}-date" class="form-label">{{ __('Departure date') }}</label>
            <input id="{{ $idPrefix }}-date" type="date" name="date" class="form-input"
                   value="{{ $selectedDate }}" min="{{ now()->toDateString() }}" data-search-date required>
        </div>

        <button type="submit" class="btn-primary h-11 md:px-6">
            <x-icon name="search" class="size-4" />
            {{ __('Find schedules') }}
        </button>
    </div>

    <p class="form-error" data-search-error hidden></p>
</form>

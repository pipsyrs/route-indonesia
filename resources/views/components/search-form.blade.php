@props(['cities', 'query' => null, 'idPrefix' => 'cari'])
@php
    $selectedFrom = $query['from'] ?? 'Jakarta';
    $selectedTo = $query['to'] ?? 'Bandung';
    $selectedDate = $query['date'] ?? now()->addDay()->toDateString();
    $selectedPassengers = (int) ($query['passengers'] ?? 1);
@endphp

<form action="{{ route('catalog.index') }}" method="get" novalidate data-module="search-form"
      {{ $attributes->merge(['class' => 'grid gap-3']) }}>
    <div class="grid gap-3 md:grid-cols-[1fr_auto_1fr_minmax(0,0.9fr)_minmax(0,0.6fr)_auto] md:items-end">
        <div>
            <label for="{{ $idPrefix }}-from" class="form-label">Dari</label>
            <div class="relative">
                <x-icon name="map-pin" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-ink-subtle" />
                <select id="{{ $idPrefix }}-from" name="from" class="form-input appearance-none pl-9" data-search-from required>
                    @foreach ($cities as $city)
                        <option value="{{ $city }}" @selected($city === $selectedFrom)>{{ $city }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <button type="button" class="btn-outline size-11 self-end justify-self-center rounded-xl p-0 max-md:-my-1 max-md:rotate-90" data-search-swap>
            <x-icon name="arrows-exchange" class="size-5" />
            <span class="sr-only">Tukar kota asal dan tujuan</span>
        </button>

        <div>
            <label for="{{ $idPrefix }}-to" class="form-label">Ke</label>
            <div class="relative">
                <x-icon name="map-2" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-ink-subtle" />
                <select id="{{ $idPrefix }}-to" name="to" class="form-input appearance-none pl-9" data-search-to required>
                    @foreach ($cities as $city)
                        <option value="{{ $city }}" @selected($city === $selectedTo)>{{ $city }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-[1fr_0.7fr] gap-3 md:contents">
            <div>
                <label for="{{ $idPrefix }}-date" class="form-label">Tanggal berangkat</label>
                <input id="{{ $idPrefix }}-date" type="date" name="date" class="form-input"
                       value="{{ $selectedDate }}" min="{{ now()->toDateString() }}" data-search-date required>
            </div>

            <div>
                <label for="{{ $idPrefix }}-passengers" class="form-label">Penumpang</label>
                <select id="{{ $idPrefix }}-passengers" name="passengers" class="form-input appearance-none">
                    @for ($count = 1; $count <= 6; $count++)
                        <option value="{{ $count }}" @selected($count === $selectedPassengers)>{{ $count }} orang</option>
                    @endfor
                </select>
            </div>
        </div>

        <button type="submit" class="btn-primary h-11 md:px-6">
            <x-icon name="search" class="size-4" />
            Cari jadwal
        </button>
    </div>

    <p class="form-error" data-search-error hidden></p>
</form>

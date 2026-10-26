@props(['query' => null, 'idPrefix' => 'cari'])

<form action="{{ route('catalog.index') }}" method="get" role="search" data-module="search-form"
      {{ $attributes->merge(['class' => 'grid gap-3']) }}>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <label for="{{ $idPrefix }}-q" class="sr-only">{{ __('Search keyword') }}</label>
            <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-5 -translate-y-1/2 text-ink-subtle" />
            <input id="{{ $idPrefix }}-q" type="search" name="q" class="form-input h-12 pl-10 text-base"
                   value="{{ $query['q'] ?? '' }}" maxlength="100" autocomplete="off" enterkeyhint="search"
                   placeholder="{{ __('Search routes, cities, or operators…') }}" data-search-q>
        </div>

        {{-- Tanggal opsional dari katalog ikut terbawa saat mencari ulang --}}
        @if (! empty($query['date']))
            <input type="hidden" name="date" value="{{ $query['date'] }}">
        @endif

        <button type="submit" class="btn-primary h-12 sm:px-6">
            <x-icon name="search" class="size-4" />
            {{ __('Find schedules') }}
        </button>
    </div>
</form>

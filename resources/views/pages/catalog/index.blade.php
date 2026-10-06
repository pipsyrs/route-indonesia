@extends('layouts.app')

@php
    $routeTitle = $query['from'] && $query['to'] ? $query['from'].' ke '.$query['to'] : ($query['from'] ?? $query['to'] ?? 'Semua rute');
    $operators = array_values(array_unique(array_column($trips, 'operator')));
    sort($operators);
@endphp

@section('title', 'Katalog: '.$routeTitle)

@section('module', 'search-filter')

@section('content')
    {{-- Ringkasan filter; form ubah memakai <details> agar tetap jalan tanpa JS --}}
    <section class="border-b border-line bg-surface">
        <div class="container-page py-5">
            <details class="group" @if ($searchError) open @endif>
                <summary class="flex cursor-pointer list-none flex-wrap items-center justify-between gap-4 [&::-webkit-details-marker]:hidden">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold tracking-[0.14em] text-brand-ink uppercase">Katalog jadwal</p>
                        <h1 class="mt-1 flex flex-wrap items-center gap-x-2 text-xl font-bold tracking-tight text-ink md:text-2xl">
                            @if ($query['from'] && $query['to'])
                                {{ $query['from'] }}
                                <x-icon name="arrow-right" class="size-5 text-ink-subtle" />
                                <span class="sr-only">ke</span>
                                {{ $query['to'] }}
                            @elseif ($query['from'])
                                Dari {{ $query['from'] }}
                            @elseif ($query['to'])
                                Menuju {{ $query['to'] }}
                            @else
                                Semua rute
                            @endif
                        </h1>
                        <p class="mt-1 text-sm text-ink-muted">Berangkat {{ $query['date_label'] }}</p>
                    </div>
                    <span class="btn-outline">
                        <x-icon name="adjustments-horizontal" class="size-4" />
                        <span class="group-open:hidden">Ubah pencarian</span>
                        <span class="hidden group-open:inline">Tutup</span>
                    </span>
                </summary>

                <div class="mt-5 border-t border-line pt-5">
                    <x-search-form :cities="$cities" :query="$query" id-prefix="ubah" />
                </div>
            </details>
        </div>

    </section>

    <div class="container-page py-8">
        @if ($searchError)
            <div class="mb-6 flex items-start gap-3 rounded-md bg-warn-soft p-4 text-sm text-warn-ink" role="alert">
                <x-icon name="alert-circle" class="size-5" />
                <p>{{ $searchError }}</p>
            </div>
        @endif

        @if ($trips === [])
            {{-- Empty state dari server: rute/tanggal tanpa jadwal --}}
            <div class="mx-auto flex max-w-lg flex-col items-center py-16 text-center">
                <span class="grid size-14 place-items-center rounded-lg bg-surface-muted text-ink-subtle">
                    <x-icon name="bus" class="size-7" />
                </span>
                <h2 class="mt-5 text-lg font-bold text-ink">Belum ada jadwal untuk pencarian ini</h2>
                <p class="mt-2 text-sm text-ink-muted">
                    @if ($searchError)
                        Pilih kota tujuan yang berbeda dari kota asal.
                    @else
                        Ubah kota asal dan tujuan, atau lihat semua rute.
                    @endif
                </p>
                <a href="{{ route('catalog.index', ['date' => $query['date']]) }}" class="btn-outline mt-6">Lihat semua rute</a>
            </div>
        @else
            <div class="grid gap-6 lg:grid-cols-[17rem_1fr] lg:gap-8">
                {{-- Filter: panel samping di desktop, laci dari bawah di mobile --}}
                <aside id="panel-filter"
                       class="fixed inset-0 z-40 hidden overflow-y-auto bg-canvas p-4 lg:static lg:z-auto lg:block lg:overflow-visible lg:bg-transparent lg:p-0"
                       data-filter-panel aria-label="Filter jadwal">
                    <div class="mb-4 flex items-center justify-between lg:hidden">
                        <h2 class="text-lg font-bold text-ink">Filter</h2>
                        <button type="button" class="btn-ghost" data-filter-close>
                            <x-icon name="x" class="size-5" />
                            <span class="sr-only">Tutup filter</span>
                        </button>
                    </div>

                    <form class="space-y-6 lg:sticky lg:top-24" data-filter-form>
                        <fieldset>
                            <legend class="text-sm font-semibold text-ink">Waktu berangkat</legend>
                            <div class="mt-3 grid grid-cols-2 gap-2">
                                @foreach (['pagi' => ['Pagi', '00-11', 'sunrise'], 'siang' => ['Siang', '11-15', 'sun'], 'sore' => ['Sore', '15-18', 'sunset'], 'malam' => ['Malam', '18-24', 'moon']] as $slot => [$label, $range, $icon])
                                    <label class="cursor-pointer">
                                        <input type="checkbox" name="time" value="{{ $slot }}" class="peer sr-only">
                                        <span class="flex flex-col items-center gap-1 rounded-md border border-line bg-surface px-2 py-2.5 text-center text-xs text-ink-muted transition-colors peer-checked:border-brand-600 peer-checked:bg-brand-soft peer-checked:text-brand-ink peer-focus-visible:outline-2 peer-focus-visible:outline-brand-500">
                                            <x-icon :name="$icon" class="size-5" />
                                            <span class="font-semibold">{{ $label }}</span>
                                            <span>{{ $range }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend class="text-sm font-semibold text-ink">Harga maksimum</legend>
                            @php
                                $prices = array_column($trips, 'price');
                                [$minPrice, $maxPrice] = [min($prices), max($prices)];
                            @endphp
                            <input type="range" name="max_price" min="{{ $minPrice }}" max="{{ $maxPrice }}" step="5000" value="{{ $maxPrice }}"
                                   class="mt-3 w-full accent-brand-600" aria-describedby="harga-maks-label" data-filter-price>
                            <p id="harga-maks-label" class="mt-1 text-sm text-ink-muted">Sampai <x-price :amount="$maxPrice" class="font-semibold text-ink" data-filter-price-label /></p>
                        </fieldset>

                        <fieldset>
                            <legend class="text-sm font-semibold text-ink">Operator</legend>
                            <div class="mt-3 space-y-2">
                                @foreach ($operators as $operator)
                                    <label class="flex cursor-pointer items-center gap-2.5 text-sm text-ink">
                                        <input type="checkbox" name="operator" value="{{ $operator }}" class="size-4 rounded accent-brand-600">
                                        {{ $operator }}
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend class="text-sm font-semibold text-ink">Fasilitas</legend>
                            <div class="mt-3 space-y-2">
                                @foreach ($facilities as $code => $label)
                                    <label class="flex cursor-pointer items-center gap-2.5 text-sm text-ink">
                                        <input type="checkbox" name="facility" value="{{ $code }}" class="size-4 rounded accent-brand-600">
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <div class="flex gap-2">
                            <button type="reset" class="btn-outline flex-1" data-filter-reset>Reset</button>
                            <button type="button" class="btn-primary flex-1 lg:hidden" data-filter-close>Terapkan</button>
                        </div>
                    </form>
                </aside>

                <div class="min-w-0">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <p class="text-sm text-ink-muted" aria-live="polite">
                            <span class="font-semibold text-ink" data-filter-count>{{ count($trips) }}</span> jadwal ditemukan
                        </p>
                        <div class="flex items-center gap-2">
                            <button type="button" class="btn-outline lg:hidden" data-filter-open aria-controls="panel-filter">
                                <x-icon name="adjustments-horizontal" class="size-4" />
                                Filter
                            </button>
                            <label for="urutkan" class="sr-only">Urutkan</label>
                            <select id="urutkan" class="form-input w-auto appearance-none pr-8" data-filter-sort>
                                <option value="depart">Berangkat paling awal</option>
                                <option value="price">Harga terendah</option>
                                <option value="duration">Durasi tercepat</option>
                                <option value="rating">Rating tertinggi</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-4" data-filter-list>
                        @foreach ($trips as $trip)
                            <x-trip-card :trip="$trip" :query="$query" :facilities="$facilities" />
                        @endforeach
                    </div>

                    {{-- Empty state sisi klien: filter tidak menyisakan jadwal --}}
                    <div class="flex flex-col items-center rounded-lg border border-dashed border-line-strong px-6 py-12 text-center" data-filter-empty hidden>
                        <x-icon name="search" class="size-7 text-ink-subtle" />
                        <h2 class="mt-4 font-bold text-ink">Tidak ada jadwal yang cocok</h2>
                        <p class="mt-1 text-sm text-ink-muted">Longgarkan filter untuk melihat jadwal lain.</p>
                        <button type="button" class="btn-outline mt-5" data-filter-reset>Reset filter</button>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

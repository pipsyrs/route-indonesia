@extends('layouts.app')

@section('title', $trip['operator'].' '.$trip['origin'].' ke '.$trip['destination'])

@section('module', 'add-to-cart')

@php
    // Snapshot untuk keranjang; dibaca JS dari <script type="application/json">, bukan dari teks DOM.
    $tripForCart = [
        'tripId' => $trip['id'],
        'operator' => $trip['operator'],
        'vehicle' => $trip['vehicle'],
        'origin' => $trip['origin'],
        'destination' => $trip['destination'],
        'date' => $date,
        'dateLabel' => $dateLabel,
        'depart' => $trip['depart'],
        'arrive' => $trip['arrive'],
        'price' => $trip['price'],
        'maxQty' => $maxQty,
        'pickups' => array_map(fn ($point) => ['id' => $point['id'], 'name' => $point['name'], 'time' => $point['time']], $trip['pickups']),
        'dropoffs' => array_map(fn ($point) => ['id' => $point['id'], 'name' => $point['name'], 'time' => $point['time']], $trip['dropoffs']),
    ];
    $isSoldOut = $maxQty < 1;
@endphp

@section('content')
    <div class="container-page py-6 md:py-10">
        <a href="{{ route('catalog.index', ['from' => $trip['origin'], 'to' => $trip['destination'], 'date' => $date]) }}" class="btn-ghost -ml-3">
            <x-icon name="arrow-left" class="size-4" />
            Kembali ke katalog
        </a>

        <div class="mt-4 grid gap-6 lg:grid-cols-[1fr_22rem] lg:gap-10">
            <div class="min-w-0 space-y-6">
                {{-- Kepala: rute sebagai focal point --}}
                <header>
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm">
                        <span class="font-semibold text-ink">{{ $trip['operator'] }}</span>
                        <x-rating :value="$trip['rating']" />
                        <span class="text-ink-subtle">{{ $trip['vehicle'] }}</span>
                    </div>
                    <h1 class="mt-2 flex flex-wrap items-center gap-x-3 text-3xl font-extrabold tracking-tight text-ink md:text-4xl">
                        {{ $trip['origin'] }}
                        <x-icon name="arrow-right" class="size-7 text-ink-subtle" />
                        <span class="sr-only">ke</span>
                        {{ $trip['destination'] }}
                    </h1>
                    <p class="mt-2 flex items-center gap-2 text-ink-muted">
                        <x-icon name="calendar" class="size-4" />
                        {{ $dateLabel }}
                    </p>
                </header>

                {{-- Linimasa perjalanan --}}
                <section class="card p-5 sm:p-6" aria-labelledby="perjalanan-title">
                    <h2 id="perjalanan-title" class="text-base font-bold text-ink">Perjalanan</h2>
                    <ol class="mt-5 space-y-0">
                        <li class="relative flex gap-4 pb-8">
                            <span class="absolute top-6 bottom-0 left-[0.6875rem] w-px bg-line-strong" aria-hidden="true"></span>
                            <span class="relative mt-1 size-[1.375rem] shrink-0 rounded-full border-4 border-brand-soft bg-brand-600" aria-hidden="true"></span>
                            <div>
                                <p class="text-xl font-bold tabular-nums text-ink">{{ $trip['depart'] }}</p>
                                <p class="font-semibold text-ink">{{ $trip['pickups'][0]['name'] ?? $trip['origin'] }}</p>
                                <p class="text-sm text-ink-subtle">{{ $trip['pickups'][0]['address'] ?? $trip['origin'] }}</p>
                                <p class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-surface-muted px-3 py-1 text-xs font-medium text-ink-muted">
                                    <x-icon name="clock" class="size-3.5" />
                                    {{ $trip['duration'] }}
                                </p>
                            </div>
                        </li>
                        <li class="flex gap-4">
                            <span class="mt-1 size-[1.375rem] shrink-0 rounded-full border-4 border-line bg-surface" aria-hidden="true"></span>
                            <div>
                                <p class="text-xl font-bold tabular-nums text-ink">
                                    {{ $trip['arrive'] }}@if ($trip['arrives_next_day'])<sup class="ml-0.5 text-[10px] font-semibold text-warn-ink">+1</sup><span class="sr-only">(hari berikutnya)</span>@endif
                                </p>
                                <p class="font-semibold text-ink">{{ $trip['dropoffs'][0]['name'] ?? $trip['destination'] }}</p>
                                <p class="text-sm text-ink-subtle">{{ $trip['dropoffs'][0]['address'] ?? $trip['destination'] }}</p>
                            </div>
                        </li>
                    </ol>
                </section>

                <section class="card p-5 sm:p-6" aria-labelledby="fasilitas-title">
                    <h2 id="fasilitas-title" class="text-base font-bold text-ink">Fasilitas</h2>
                    <ul class="mt-4 flex flex-wrap gap-2">
                        @foreach ($trip['facilities'] as $code)
                            <li><x-facility-badge :code="$code" :label="$facilityLabels[$code] ?? $code" class="px-3 py-1.5 text-sm" /></li>
                        @endforeach
                    </ul>
                </section>
            </div>

            {{-- Panel pemesanan: lengket di desktop --}}
            <aside class="lg:sticky lg:top-24 lg:self-start">
                <form class="card p-5 sm:p-6" novalidate data-add-to-cart-form>
                    <p class="text-sm text-ink-subtle">Harga per orang</p>
                    <x-price :amount="$trip['price']" class="text-3xl font-extrabold tracking-tight text-ink" />
                    <p @class(['mt-1 text-sm font-medium', 'text-warn-ink' => $trip['seats_left'] <= 3, 'text-ink-muted' => $trip['seats_left'] > 3])>
                        Sisa {{ $trip['seats_left'] }} kursi
                    </p>

                    @if (count($trip['pickups']) > 1)
                        <label for="titik-jemput" class="form-label mt-5">Titik jemput</label>
                        <select id="titik-jemput" class="form-input appearance-none" data-pickup>
                            @foreach ($trip['pickups'] as $point)
                                <option value="{{ $point['id'] }}">{{ $point['name'] }} ({{ $point['time'] }})</option>
                            @endforeach
                        </select>
                    @endif
                    @if (count($trip['dropoffs']) > 1)
                        <label for="titik-antar" class="form-label mt-4">Titik antar</label>
                        <select id="titik-antar" class="form-input appearance-none" data-dropoff>
                            @foreach ($trip['dropoffs'] as $point)
                                <option value="{{ $point['id'] }}">{{ $point['name'] }} ({{ $point['time'] }})</option>
                            @endforeach
                        </select>
                    @endif

                    <div class="mt-5 flex items-center justify-between gap-4">
                        <label for="jumlah-tiket" class="text-sm font-medium text-ink">Jumlah tiket</label>
                        <div class="flex items-center rounded-xl border border-line-strong">
                            <button type="button" class="grid size-10 place-items-center text-ink-muted hover:text-ink disabled:opacity-40" data-qty-step="-1" @disabled($isSoldOut)>
                                <x-icon name="minus" class="size-4" />
                                <span class="sr-only">Kurangi</span>
                            </button>
                            <input id="jumlah-tiket" type="number" inputmode="numeric" min="1" max="{{ max($maxQty, 1) }}" value="1"
                                   class="w-12 border-0 bg-transparent text-center font-semibold tabular-nums text-ink [appearance:textfield] focus:outline-none [&::-webkit-inner-spin-button]:appearance-none"
                                   data-qty-input @disabled($isSoldOut)>
                            <button type="button" class="grid size-10 place-items-center text-ink-muted hover:text-ink disabled:opacity-40" data-qty-step="1" @disabled($isSoldOut)>
                                <x-icon name="plus" class="size-4" />
                                <span class="sr-only">Tambah</span>
                            </button>
                        </div>
                    </div>
                    <p class="form-hint">Maksimal {{ $maxQty }} tiket per jadwal.</p>

                    <div class="mt-5 flex items-baseline justify-between gap-4 border-t border-line pt-4">
                        <span class="text-sm font-semibold text-ink">Subtotal</span>
                        <x-price :amount="$trip['price']" class="text-xl font-bold text-brand-ink" data-qty-subtotal />
                    </div>

                    <button type="submit" class="btn-primary mt-5 w-full py-3" @disabled($isSoldOut)>
                        <x-icon name="shopping-cart" class="size-5" />
                        {{ $isSoldOut ? 'Kursi habis' : 'Tambah ke keranjang' }}
                    </button>

                    {{-- Muncul setelah item ditambahkan --}}
                    <div class="mt-4 rounded-xl bg-ok-soft p-4 text-sm text-ok-ink" data-added-notice role="status" hidden>
                        <p class="flex items-center gap-2 font-semibold"><x-icon name="circle-check" class="size-5" /> Ditambahkan ke keranjang</p>
                        <a href="{{ route('cart') }}" class="btn-primary mt-3 w-full">Lihat keranjang</a>
                    </div>
                </form>
            </aside>
        </div>
    </div>

    <script type="application/json" data-trip-json>@json($tripForCart)</script>
@endsection

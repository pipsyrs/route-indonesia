@extends('layouts.app')

@section('title', trim($trip['operator'].' '.($trip['route_name'] ?? '')))

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
        'seatLayout' => $seatMap['layout'],
        'occupied' => $seatMap['occupied'],
    ];
    $isSoldOut = $maxQty < 1;
    // Fallback (data DB): titik jemput/antar tanpa deskripsi.
    $itinerary = ($trip['itinerary'] ?? []) ?: [
        ['title' => $trip['pickups'][0]['name'] ?? $trip['origin'], 'description' => $trip['pickups'][0]['address'] ?? $trip['origin']],
        ['title' => $trip['dropoffs'][0]['name'] ?? $trip['destination'], 'description' => $trip['dropoffs'][0]['address'] ?? $trip['destination']],
    ];
@endphp

@section('content')
    <div class="container-page py-6 md:py-10">
        <a href="{{ route('catalog.index', ['from' => $trip['origin'], 'to' => $trip['destination'], 'date' => $date]) }}" class="btn-ghost -ml-3">
            <x-icon name="arrow-left" class="size-4" />
            Kembali ke katalog
        </a>

        <div class="mt-4 grid gap-6 lg:grid-cols-[1fr_22rem] lg:gap-10">
            <div class="min-w-0 space-y-6">
                {{-- Kepala: operator sebagai judul, rute induk sebagai subjudul --}}
                <header>
                    <h1 class="text-3xl font-extrabold tracking-tight text-ink md:text-4xl">{{ $trip['operator'] }}</h1>
                    @if (! empty($trip['route_name']))
                        <p class="mt-1 text-lg font-semibold text-ink-muted">{{ $trip['route_name'] }}</p>
                    @endif
                    <p class="mt-2 flex items-center gap-2 text-ink-muted">
                        <x-icon name="calendar" class="size-4" />
                        {{ $dateLabel }}
                    </p>
                </header>

                {{-- Linimasa perjalanan --}}
                <section class="card p-5 sm:p-6" aria-labelledby="perjalanan-title">
                    <h2 id="perjalanan-title" class="text-base font-bold text-ink">Itenarary Trip</h2>
                    <ol class="mt-5 space-y-0">
                        @foreach ($itinerary as $step)
                            <li @class(['relative flex gap-4', 'pb-8' => ! $loop->last])>
                                @unless ($loop->last)
                                    <span class="absolute top-6 bottom-0 left-[0.6875rem] w-px bg-line-strong" aria-hidden="true"></span>
                                @endunless
                                <span @class([
                                    'relative mt-1 size-[1.375rem] shrink-0 rounded-full border-4',
                                    'border-brand-soft bg-brand-600' => $loop->first,
                                    'border-line bg-surface' => ! $loop->first,
                                ]) aria-hidden="true"></span>
                                <div>
                                    <p class="font-semibold text-ink">{{ $step['title'] }}</p>
                                    <p class="mt-1 text-sm leading-relaxed text-ink-muted">{{ $step['description'] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </section>

                @if (filled($trip['description'] ?? null))
                <section class="card p-5 sm:p-6" aria-labelledby="deskripsi-title">
                    <h2 id="deskripsi-title" class="text-base font-bold text-ink">Deskripsi</h2>
                    <p class="mt-3 leading-relaxed text-ink-muted">
                        {{ $trip['description'] }}
                    </p>
                </section>
                @endif
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
                                <option value="{{ $point['id'] }}">{{ $point['name'] }}</option>
                            @endforeach
                        </select>
                    @endif
                    @if (count($trip['dropoffs']) > 1)
                        <label for="titik-antar" class="form-label mt-4">Titik antar</label>
                        <select id="titik-antar" class="form-input appearance-none" data-dropoff>
                            @foreach ($trip['dropoffs'] as $point)
                                <option value="{{ $point['id'] }}">{{ $point['name'] }}</option>
                            @endforeach
                        </select>
                    @endif

                    @unless ($isSoldOut)
                        {{-- Denah inline: jumlah tiket = jumlah kursi dipilih (add-to-cart.js) --}}
                        <fieldset class="mt-5" aria-describedby="kursi-hint">
                            <legend class="text-sm font-medium text-ink">Pilih kursi</legend>
                            <p id="kursi-hint" class="form-hint mt-0.5">Maksimal {{ $maxQty }} kursi per jadwal. Bagian depan kendaraan di atas.</p>
                            <div class="mx-auto mt-3 grid w-full max-w-72 gap-2 rounded-lg border border-line bg-surface p-3" data-seat-grid></div>
                            <ul class="mt-3 flex flex-wrap justify-center gap-x-4 gap-y-2 text-xs text-ink-muted" aria-label="Keterangan denah">
                                <li class="flex items-center gap-2"><span class="size-4 rounded-md border border-line-strong bg-seat-available"></span> Tersedia</li>
                                <li class="flex items-center gap-2"><span class="size-4 rounded-md bg-seat-occupied"></span> Terisi</li>
                                <li class="flex items-center gap-2"><span class="size-4 rounded-md bg-seat-selected"></span> Dipilih</li>
                                <li class="flex items-center gap-2"><x-icon name="steering-wheel" class="size-4 text-ink-subtle" /> Sopir</li>
                            </ul>
                            <p class="form-error text-center" role="alert" data-seat-error hidden></p>
                        </fieldset>

                        <div class="mt-5 space-y-2 border-t border-line pt-4">
                            <p class="flex justify-between gap-4 text-sm" aria-live="polite">
                                <span class="text-ink-muted">Kursi dipilih</span>
                                <span class="text-right font-semibold tabular-nums text-ink" data-seat-summary>-</span>
                            </p>
                            <p class="flex items-baseline justify-between gap-4">
                                <span class="text-sm font-semibold text-ink">Subtotal</span>
                                <x-price :amount="0" class="text-xl font-bold text-brand-ink" data-qty-subtotal />
                            </p>
                        </div>
                    @endunless

                    <button type="submit" class="btn-primary mt-5 w-full py-3" data-add-submit disabled>
                        <x-icon name="shopping-cart" class="size-5" />
                        <span data-add-label>{{ $isSoldOut ? 'Kursi habis' : 'Tambah ke keranjang' }}</span>
                    </button>

                    {{-- Muncul setelah item ditambahkan/diperbarui; satu-satunya pengumuman (tanpa toast) --}}
                    <div class="mt-4 rounded-md bg-ok-soft p-4 text-sm text-ok-ink" data-added-notice role="status" hidden>
                        <p class="flex items-center gap-2 font-semibold"><x-icon name="circle-check" class="size-5" /> <span data-added-text>Ditambahkan ke keranjang</span></p>
                        <a href="{{ route('cart') }}" class="btn-primary mt-3 w-full">Lihat keranjang</a>
                    </div>
                </form>
            </aside>
        </div>
    </div>

    <x-seat-map-templates />
    <script type="application/json" data-trip-json>@json($tripForCart)</script>
@endsection

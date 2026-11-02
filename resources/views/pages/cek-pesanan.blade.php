@extends('layouts.app')

@section('title', 'Cek pesanan')

@section('module', 'order-check')

@php
    // Preview data only: lookup to the database (POST + rate limit) comes in the next phase.
    $demoBooking = [
        'code' => 'RID-DEMO26',
        'origin' => 'Jakarta',
        'destination' => 'Bandung',
        'date' => 'Sabtu, 14 November 2026',
        'operator' => 'Nusantara Shuttle',
        'pickup' => ['name' => 'Pool Kuningan City', 'time' => '06.45'],
        'depart' => '07.00',
        'dropoff' => ['name' => 'Pool Pasteur', 'time' => '10.15'],
        'passengers' => [['name' => 'Arif Pratama', 'seat' => '3'], ['name' => 'Rina Lestari', 'seat' => '4']],
        'method' => 'Transfer Bank',
        'total' => 290000,
    ];
    $demoOrder = [
        'code' => 'ORD-DEMO2026',
        'items' => [
            ['name' => 'Travel Neck Pillow', 'qty' => 2, 'price' => 85000],
            ['name' => 'Packing Cube Set', 'qty' => 1, 'price' => 159000],
        ],
        'shipping' => 18000,
        'courier' => 'Kurir reguler',
        'tracking' => 'DEMO1234567890',
        'city' => 'Bandung, Jawa Barat',
        'timeline' => [
            ['label' => 'Pesanan dibuat', 'time' => '28 Okt 2026, 10.12', 'state' => 'done'],
            ['label' => 'Pembayaran dikonfirmasi', 'time' => '28 Okt 2026, 13.40', 'state' => 'done'],
            ['label' => 'Dikemas', 'time' => '29 Okt 2026, 09.05', 'state' => 'done'],
            ['label' => 'Dalam pengiriman', 'time' => '29 Okt 2026, 16.20', 'state' => 'current'],
            ['label' => 'Diterima', 'time' => 'Estimasi 1 Nov 2026', 'state' => 'upcoming'],
        ],
    ];
    $orderSubtotal = collect($demoOrder['items'])->sum(fn (array $item) => $item['qty'] * $item['price']);
    $tabs = [
        ['kind' => 'booking', 'icon' => 'ticket', 'label' => 'Kode booking', 'sub' => 'Tiket trip'],
        ['kind' => 'order', 'icon' => 'shopping-cart', 'label' => 'ID pesanan', 'sub' => 'Belanja shop'],
    ];
    $forms = [
        'booking' => ['code' => $demoBooking['code'], 'label' => 'Kode booking', 'rule' => 'booking-code', 'placeholder' => 'RID-XXXXXX', 'max' => 10, 'hint' => 'Format RID-XXXXXX atau RI-XXXXXX.'],
        'order' => ['code' => $demoOrder['code'], 'label' => 'ID pesanan', 'rule' => 'order-code', 'placeholder' => 'ORD-XXXXXXXX', 'max' => 12, 'hint' => 'Format ORD-XXXXXXXX atau RI-XXXXXX.'],
    ];
@endphp

@section('content')
    <div class="container-page grid gap-10 py-10 md:py-14 lg:grid-cols-[1fr_1.1fr] lg:items-start">
        <div class="max-w-md">
            <span class="grid size-12 place-items-center rounded-md bg-brand-soft text-brand-ink">
                <x-icon name="ticket" class="size-6" />
            </span>
            <h1 class="mt-5 text-3xl font-bold tracking-tight text-ink md:text-4xl">Cek status pesanan</h1>
            <p class="mt-3 leading-relaxed text-ink-muted">
                Tanpa perlu masuk akun. Pilih jenis pesanan, lalu masukkan kodenya beserta email atau nomor HP yang dipakai saat memesan.
            </p>
            <p class="mt-6 text-sm text-ink-muted">
                Butuh bantuan? Hubungi <a href="tel:+622150891234" class="font-semibold text-brand-ink underline underline-offset-2">(021) 5089-1234</a>.
            </p>
        </div>

        <section class="card p-5 sm:p-6" aria-labelledby="form-cek-title">
            <h2 id="form-cek-title" class="sr-only">Formulir cek pesanan</h2>

            <div class="grid grid-cols-2 gap-1 rounded-md bg-surface-muted p-1" role="tablist" aria-label="Jenis pesanan">
                @foreach ($tabs as $tab)
                    <button type="button" id="cek-tab-{{ $tab['kind'] }}" role="tab"
                            aria-selected="{{ $loop->first ? 'true' : 'false' }}" aria-controls="cek-panel-{{ $tab['kind'] }}"
                            tabindex="{{ $loop->first ? '0' : '-1' }}" data-check-tab
                            class="flex items-center justify-center gap-2 rounded-md px-3 py-2.5 text-sm font-semibold text-ink-muted transition hover:text-ink aria-selected:bg-surface aria-selected:text-ink aria-selected:shadow-sm">
                        <x-icon :name="$tab['icon']" class="size-4" />
                        <span class="text-left leading-tight">
                            {{ $tab['label'] }}
                            <span class="block text-xs font-normal text-ink-subtle">{{ $tab['sub'] }}</span>
                        </span>
                    </button>
                @endforeach
            </div>

            @foreach ($forms as $kind => $form)
                <div id="cek-panel-{{ $kind }}" role="tabpanel" aria-labelledby="cek-tab-{{ $kind }}" @unless ($loop->first) hidden @endunless>
                    <form class="mt-5 grid gap-4" novalidate data-order-check-form data-kind="{{ $kind }}" data-demo-code="{{ $form['code'] }}">
                        <div>
                            <label for="cek-{{ $kind }}-code" class="form-label">{{ $form['label'] }}</label>
                            <input id="cek-{{ $kind }}-code" type="text" class="form-input font-mono uppercase tracking-wider" maxlength="{{ $form['max'] }}"
                                   autocomplete="off" autocapitalize="characters" spellcheck="false" placeholder="{{ $form['placeholder'] }}"
                                   aria-describedby="cek-{{ $kind }}-code-hint cek-{{ $kind }}-code-error" data-rule="{{ $form['rule'] }}" data-order-code>
                            <p id="cek-{{ $kind }}-code-hint" class="form-hint">{{ $form['hint'] }}</p>
                            <p id="cek-{{ $kind }}-code-error" class="form-error" hidden></p>
                        </div>

                        <div>
                            <label for="cek-{{ $kind }}-contact" class="form-label">Email atau nomor HP</label>
                            <input id="cek-{{ $kind }}-contact" type="text" class="form-input" maxlength="150" autocomplete="off" spellcheck="false"
                                   aria-describedby="cek-{{ $kind }}-contact-error" data-rule="contact" data-order-contact>
                            <p id="cek-{{ $kind }}-contact-error" class="form-error" hidden></p>
                        </div>

                        <button type="submit" class="btn-primary mt-1">
                            <x-icon name="search" class="size-4" />
                            Cek pesanan
                        </button>

                        <div class="flex flex-wrap items-center gap-x-3 gap-y-2 rounded-md bg-surface-muted p-3 text-xs text-ink-muted">
                            <x-icon name="info-circle" class="size-4" />
                            <p class="flex-1">
                                Pratinjau tampilan: hasil memakai data contoh, belum terhubung ke sistem. Coba kode
                                <span class="font-mono font-semibold text-ink">{{ $form['code'] }}</span> dengan email apa saja.
                            </p>
                            <button type="button" class="btn-ghost px-2 py-1 text-xs" data-fill-demo>Isi kode demo</button>
                        </div>
                    </form>
                </div>
            @endforeach
        </section>

        <p class="sr-only" role="status" data-check-status></p>

        {{-- Booking result (trip) --}}
        <article class="card overflow-hidden outline-none lg:col-span-2" tabindex="-1" aria-labelledby="hasil-booking-title" data-result="booking" hidden>
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-line bg-surface-muted/60 px-5 py-4 sm:px-6">
                <div>
                    <h2 id="hasil-booking-title" class="text-xs text-ink-subtle">Kode booking</h2>
                    <p class="font-mono text-xl font-bold tracking-wider text-ink">{{ $demoBooking['code'] }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="chip bg-surface text-ink-subtle">Data contoh</span>
                    <span class="chip bg-ok-soft text-ok-ink"><x-icon name="circle-check" class="size-3.5" /> Lunas</span>
                </div>
            </header>

            <div class="grid gap-8 p-5 sm:p-6 md:grid-cols-[1.2fr_1fr]">
                <div>
                    <p class="text-sm text-ink-muted">{{ $demoBooking['date'] }} · {{ $demoBooking['operator'] }}</p>
                    <p class="mt-1 text-2xl font-bold tracking-tight text-ink">{{ $demoBooking['origin'] }} <span class="text-ink-subtle" aria-hidden="true">→</span><span class="sr-only">ke</span> {{ $demoBooking['destination'] }}</p>

                    <ol class="mt-5 space-y-4 border-l-2 border-line pl-5">
                        <li class="relative">
                            <span class="absolute top-1.5 -left-[1.6rem] size-2.5 rounded-full bg-brand-600" aria-hidden="true"></span>
                            <p class="text-xs text-ink-subtle">Jemput · {{ $demoBooking['pickup']['time'] }} (berangkat {{ $demoBooking['depart'] }})</p>
                            <p class="font-semibold text-ink">{{ $demoBooking['pickup']['name'] }}, {{ $demoBooking['origin'] }}</p>
                        </li>
                        <li class="relative">
                            <span class="absolute top-1.5 -left-[1.6rem] size-2.5 rounded-full border-2 border-brand-600 bg-surface" aria-hidden="true"></span>
                            <p class="text-xs text-ink-subtle">Turun · perkiraan {{ $demoBooking['dropoff']['time'] }}</p>
                            <p class="font-semibold text-ink">{{ $demoBooking['dropoff']['name'] }}, {{ $demoBooking['destination'] }}</p>
                        </li>
                    </ol>
                </div>

                <div class="space-y-5">
                    <div>
                        <h3 class="text-sm font-semibold text-ink">Penumpang & kursi</h3>
                        <ul class="mt-2 divide-y divide-line text-sm">
                            @foreach ($demoBooking['passengers'] as $passenger)
                                <li class="flex items-center justify-between gap-3 py-2">
                                    <span class="text-ink">{{ $passenger['name'] }}</span>
                                    <span class="chip bg-surface-muted text-ink-muted">Kursi {{ $passenger['seat'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <dl class="space-y-2 border-t border-dashed border-line-strong pt-4 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-ink-muted">Metode</dt><dd class="text-ink">{{ $demoBooking['method'] }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-ink-muted">Total dibayar</dt><dd><x-price :amount="$demoBooking['total']" class="font-bold text-brand-ink" /></dd></div>
                    </dl>
                </div>
            </div>
        </article>

        {{-- Order result (shop) --}}
        <article class="card overflow-hidden outline-none lg:col-span-2" tabindex="-1" aria-labelledby="hasil-order-title" data-result="order" hidden>
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-line bg-surface-muted/60 px-5 py-4 sm:px-6">
                <div>
                    <h2 id="hasil-order-title" class="text-xs text-ink-subtle">ID pesanan</h2>
                    <p class="font-mono text-xl font-bold tracking-wider text-ink">{{ $demoOrder['code'] }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="chip bg-surface text-ink-subtle">Data contoh</span>
                    <span class="chip bg-brand-soft text-brand-ink"><x-icon name="bus" class="size-3.5" /> Dalam pengiriman</span>
                </div>
            </header>

            <div class="grid gap-8 p-5 sm:p-6 md:grid-cols-[1.2fr_1fr]">
                <div>
                    <h3 class="text-sm font-semibold text-ink">Produk</h3>
                    <ul class="mt-2 divide-y divide-line text-sm">
                        @foreach ($demoOrder['items'] as $item)
                            <li class="flex items-start justify-between gap-4 py-3">
                                <span>
                                    <span class="block font-medium text-ink">{{ $item['name'] }}</span>
                                    <span class="text-ink-muted">{{ $item['qty'] }} × <x-price :amount="$item['price']" /></span>
                                </span>
                                <x-price :amount="$item['qty'] * $item['price']" class="text-ink" />
                            </li>
                        @endforeach
                    </ul>
                    <dl class="mt-2 space-y-2 border-t border-dashed border-line-strong pt-4 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-ink-muted">Subtotal</dt><dd><x-price :amount="$orderSubtotal" class="text-ink" /></dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-ink-muted">Ongkos kirim</dt><dd><x-price :amount="$demoOrder['shipping']" class="text-ink" /></dd></div>
                        <div class="flex justify-between gap-4 border-t border-line pt-3 text-base"><dt class="font-semibold text-ink">Total</dt><dd><x-price :amount="$orderSubtotal + $demoOrder['shipping']" class="font-bold text-brand-ink" /></dd></div>
                    </dl>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-ink">Status pengiriman</h3>
                    <p class="mt-1 text-sm text-ink-muted">{{ $demoOrder['courier'] }} · resi <span class="font-mono text-ink">{{ $demoOrder['tracking'] }}</span> · {{ $demoOrder['city'] }}</p>
                    <ol class="mt-4 space-y-4">
                        @foreach ($demoOrder['timeline'] as $step)
                            <li class="flex gap-3" @if ($step['state'] === 'current') aria-current="step" @endif>
                                <span @class([
                                    'mt-0.5 grid size-5 shrink-0 place-items-center rounded-full',
                                    'bg-ok-soft text-ok-ink' => $step['state'] === 'done',
                                    'bg-brand-600 text-white' => $step['state'] === 'current',
                                    'border border-line-strong bg-surface' => $step['state'] === 'upcoming',
                                ]) aria-hidden="true">
                                    @if ($step['state'] === 'done')
                                        <x-icon name="check" class="size-3" />
                                    @elseif ($step['state'] === 'current')
                                        <span class="size-1.5 rounded-full bg-white"></span>
                                    @endif
                                </span>
                                <span class="text-sm">
                                    <span @class(['block font-medium', 'text-ink' => $step['state'] !== 'upcoming', 'text-ink-subtle' => $step['state'] === 'upcoming'])>{{ $step['label'] }}</span>
                                    <span class="text-xs text-ink-subtle">{{ $step['time'] }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </article>

        {{-- Not found --}}
        <div class="rounded-lg bg-danger-soft p-5 text-danger-ink outline-none sm:p-6 lg:col-span-2" tabindex="-1" data-result="not-found" hidden>
            <div class="flex items-start gap-3">
                <x-icon name="alert-circle" class="size-6" />
                <div>
                    <h2 class="font-bold">Pesanan tidak ditemukan</h2>
                    <p class="mt-1 text-sm">
                        Tidak ada pesanan dengan <span data-not-found-label></span> <span class="font-mono font-semibold" data-not-found-code></span>.
                        Periksa lagi kode dan email atau nomor HP-nya. Untuk pratinjau, coba <span class="font-mono font-semibold" data-not-found-demo></span>.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

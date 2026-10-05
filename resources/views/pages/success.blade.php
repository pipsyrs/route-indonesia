@extends('layouts.app')

@section('title', 'E-ticket '.$bookingCode)

@section('module', 'success')

@section('content')
    <div class="container-page py-6 md:py-8">
        <x-stepper :current="4" />

        <div class="mx-auto mt-8 max-w-3xl">
            <header class="flex flex-col items-start gap-4 sm:flex-row sm:items-center print:hidden">
                <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-ok-soft text-ok-ink">
                    <x-icon name="circle-check" class="size-8" />
                </span>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-ink md:text-3xl">Pembayaran berhasil</h1>
                    <p class="mt-1 text-ink-muted">Simpan kode booking untuk mengecek pesanan dan saat naik kendaraan.</p>
                </div>
            </header>

            {{-- Kartu e-ticket --}}
            <article class="card mt-8 overflow-hidden" aria-labelledby="tiket-title">
                <div class="flex flex-wrap items-center justify-between gap-4 bg-brand-600 p-5 text-white sm:p-6">
                    <div>
                        <h2 id="tiket-title" class="text-sm text-brand-50">Kode booking</h2>
                        <p class="mt-0.5 font-mono text-2xl font-bold tracking-wider sm:text-3xl" data-booking-code>{{ $bookingCode }}</p>
                    </div>
                    <button type="button" class="btn border border-white/40 text-white hover:bg-white/10 print:hidden"
                            data-copy="{{ $bookingCode }}" data-copy-message="Kode booking disalin">
                        <x-icon name="copy" class="size-4" />
                        Salin kode
                    </button>
                </div>

                <div class="grid gap-6 p-5 sm:p-6 md:grid-cols-[1.3fr_1fr]">
                    <div>
                        <p class="text-sm text-ink-muted">{{ $dateLabel }}</p>
                        <div class="mt-3 grid grid-cols-[auto_1fr] gap-x-4 gap-y-1">
                            <p class="text-xl font-bold tabular-nums text-ink">{{ $pickup['time'] }}</p>
                            <div>
                                <p class="font-semibold text-ink">{{ $pickup['name'] }}</p>
                                <p class="text-sm text-ink-muted">{{ $pickup['address'] }}, {{ $trip['origin'] }}</p>
                            </div>
                            <span class="col-start-1 mx-auto my-1 h-6 w-px bg-line-strong" aria-hidden="true"></span>
                            <span></span>
                            <p class="text-xl font-bold tabular-nums text-ink">{{ $dropoff['time'] }}</p>
                            <div>
                                <p class="font-semibold text-ink">{{ $dropoff['name'] }}</p>
                                <p class="text-sm text-ink-muted">{{ $dropoff['address'] }}, {{ $trip['destination'] }}</p>
                            </div>
                        </div>
                    </div>

                    <dl class="grid grid-cols-2 gap-x-4 gap-y-4 text-sm md:grid-cols-1">
                        <div>
                            <dt class="text-ink-subtle">Operator</dt>
                            <dd class="font-semibold text-ink">{{ $trip['operator'] }}</dd>
                            <dd class="text-ink-muted">{{ $trip['vehicle'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-ink-subtle">Kursi</dt>
                            <dd class="font-semibold text-ink">{{ implode(', ', $seats) }}</dd>
                        </div>
                        <div>
                            <dt class="text-ink-subtle">Metode bayar</dt>
                            <dd class="font-semibold text-ink">{{ $paidWith ?? 'Tidak diketahui' }}</dd>
                        </div>
                        <div>
                            <dt class="text-ink-subtle">Total bayar</dt>
                            <dd class="font-semibold text-ink"><x-price :amount="$total" /></dd>
                        </div>
                    </dl>
                </div>

                <div class="border-t border-dashed border-line-strong p-5 sm:p-6">
                    <h3 class="text-sm font-bold text-ink">Penumpang</h3>
                    <ul class="mt-3 grid gap-2 sm:grid-cols-2" data-ticket-passengers data-order-key="{{ $trip['id'] }}:{{ implode(',', $seats) }}">
                        @foreach ($seats as $index => $seat)
                            <li class="flex items-center justify-between gap-3 rounded-xl bg-surface-muted px-4 py-3 text-sm" data-ticket-passenger="{{ $seat }}">
                                <span class="font-medium text-ink" data-ticket-passenger-name>Penumpang {{ $index + 1 }}</span>
                                <span class="text-ink-muted">Kursi {{ $seat }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </article>

            <p class="mt-4 flex items-start gap-2 text-xs text-ink-subtle">
                <x-icon name="info-circle" class="size-4" />
                Ini tiket simulasi: kode booking belum tersimpan, jadi belum bisa dicek di halaman Cek Pesanan.
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row print:hidden">
                <button type="button" class="btn-primary" data-print>
                    <x-icon name="printer" class="size-4" />
                    Cetak e-ticket
                </button>
                <a href="{{ route('order.check') }}" class="btn-outline">Cek pesanan</a>
                <a href="{{ route('home') }}" class="btn-ghost">Kembali ke beranda</a>
            </div>
        </div>
    </div>
@endsection

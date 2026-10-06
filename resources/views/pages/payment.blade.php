@extends('layouts.app')

@section('title', 'Pembayaran')

@section('module', 'payment')

@section('content')
    @php
        $groupIcons = ['Virtual Account' => 'building-bank', 'E-Wallet' => 'device-mobile', 'QRIS' => 'qrcode'];
    @endphp

    <div class="container-page py-6 md:py-8">
        <x-stepper :current="3" />

        <a href="{{ route('booking', $orderQuery) }}" class="mt-6 inline-flex items-center gap-1.5 text-sm font-medium text-ink-muted hover:text-brand-ink">
            <x-icon name="arrow-left" class="size-4" />
            Ubah data penumpang
        </a>
        <h1 class="mt-3 text-2xl font-bold tracking-tight text-ink md:text-3xl">Pilih metode pembayaran</h1>

        {{-- Countdown: deadline pertama disimpan JS per pesanan agar refresh tidak mereset --}}
        <div class="mt-6 flex flex-wrap items-center justify-between gap-3 rounded-lg bg-brand-soft p-4 text-brand-ink sm:p-5"
             data-countdown data-deadline="{{ $deadline }}" data-order-key="{{ $trip['id'] }}:{{ implode(',', $seats) }}" role="timer">
            <p class="flex items-center gap-2.5 text-sm font-medium">
                <x-icon name="hourglass" class="size-5" />
                Selesaikan pembayaran dalam
            </p>
            <p class="text-2xl font-bold tabular-nums" data-countdown-value aria-live="off">30:00</p>
        </div>

        <div class="mt-4 hidden flex-wrap items-center justify-between gap-3 rounded-lg bg-danger-soft p-4 text-danger-ink" data-countdown-expired role="alert">
            <p class="flex items-center gap-2.5 text-sm font-medium">
                <x-icon name="circle-x" class="size-5" />
                Waktu pembayaran habis. Kursi Anda dilepas.
            </p>
            <a href="{{ route('trip.show', ['id' => $trip['id'], 'date' => $date, 'passengers' => count($seats)]) }}" class="btn-outline">Pesan ulang</a>
        </div>

        <form action="{{ route('booking.success') }}" method="get"
              class="mt-8 grid gap-8 lg:grid-cols-[1fr_22rem]" data-payment-form>
            @include('partials.order-query-fields')

            <div class="min-w-0 space-y-8">
                @foreach ($paymentMethods as $group => $methods)
                    <fieldset>
                        <legend class="flex items-center gap-2 text-base font-bold text-ink">
                            <x-icon :name="$groupIcons[$group] ?? 'wallet'" class="size-5 text-brand-ink" />
                            {{ $group }}
                        </legend>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            @foreach ($methods as $method)
                                <label class="cursor-pointer">
                                    <input type="radio" name="method" value="{{ $method['id'] }}" class="peer sr-only"
                                           data-method data-method-group="{{ $group }}" data-method-name="{{ $method['name'] }}" required>
                                    <span class="flex items-center gap-3 rounded-lg border border-line bg-surface p-4 transition-colors hover:border-brand-500 peer-checked:border-brand-600 peer-checked:bg-brand-soft peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand-500">
                                        <span class="grid h-9 w-12 shrink-0 place-items-center rounded-lg border border-line bg-canvas text-[11px] font-bold tracking-wide text-ink">{{ $method['short'] }}</span>
                                        <span class="text-sm font-semibold text-ink">{{ $method['name'] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach

                {{-- Instruksi per grup; ditampilkan JS sesuai metode terpilih --}}
                <section class="rounded-lg border border-dashed border-line-strong p-5" aria-live="polite" data-instructions>
                    <p class="flex items-center gap-2.5 text-sm text-ink-muted" data-instruction="none">
                        <x-icon name="info-circle" class="size-5" />
                        Pilih metode untuk melihat cara pembayaran.
                    </p>

                    <div data-instruction="Virtual Account" hidden>
                        <h2 class="font-bold text-ink">Transfer ke Virtual Account</h2>
                        <p class="mt-1 text-sm text-ink-muted">Nomor VA dibuat setelah pesanan tersimpan (fase berikutnya). Langkahnya:</p>
                        <ol class="mt-3 list-decimal space-y-1.5 pl-5 text-sm text-ink">
                            <li>Buka m-banking atau ATM bank yang dipilih.</li>
                            <li>Pilih menu transfer Virtual Account.</li>
                            <li>Masukkan nomor VA dan pastikan nominal sesuai total bayar.</li>
                        </ol>
                    </div>

                    <div data-instruction="E-Wallet" hidden>
                        <h2 class="font-bold text-ink">Bayar lewat aplikasi e-wallet</h2>
                        <p class="mt-1 text-sm text-ink-muted">Setelah menekan Bayar sekarang, aplikasi e-wallet terbuka untuk konfirmasi pembayaran.</p>
                    </div>

                    <div data-instruction="QRIS" hidden>
                        <h2 class="font-bold text-ink">Pindai kode QRIS</h2>
                        <p class="mt-1 text-sm text-ink-muted">Kode QR muncul setelah pesanan tersimpan dan bisa dipindai dari aplikasi bank atau e-wallet apa pun.</p>
                    </div>
                </section>
            </div>

            <div class="space-y-4 lg:sticky lg:top-24 lg:self-start">
                {{-- Data pemesan dari sessionStorage (diisi JS); tidak pernah lewat URL --}}
                <section class="card p-5" aria-labelledby="pemesan-ringkas-title" data-contact-summary hidden>
                    <h2 id="pemesan-ringkas-title" class="text-sm font-bold text-ink">Pemesan</h2>
                    <p class="mt-2 text-sm font-semibold text-ink" data-contact-name></p>
                    <p class="text-sm text-ink-muted" data-contact-detail></p>
                </section>

                <x-order-summary :trip="$trip" :date-label="$dateLabel" :seats="$seats"
                                 :pickup="$pickup" :dropoff="$dropoff"
                                 :subtotal="$subtotal" :service-fee="$serviceFee" :total="$total">
                    <button type="submit" class="btn-primary mt-5 w-full" data-pay-submit>
                        <x-icon name="lock" class="size-4" />
                        Bayar sekarang
                    </button>
                    <p class="mt-3 text-center text-xs text-ink-subtle">Simulasi. Pembayaran asli lewat payment gateway tersedia di fase berikutnya.</p>
                </x-order-summary>
            </div>
        </form>
    </div>
@endsection

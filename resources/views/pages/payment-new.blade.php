@extends('layouts.app')

@section('title', 'Pembayaran')

@section('module', 'checkout-payment')

@php
    // Hanya field yang dibutuhkan JS untuk membuat tagihan dummy.
    $methodsForJs = array_map(fn ($method) => ['id' => $method['id'], 'name' => $method['name'], 'type' => $method['type']], $paymentMethods);
@endphp

@section('content')
    <div class="container-page py-6 md:py-10" data-payment-root
         data-back-url="{{ route('checkout') }}" data-success-url="{{ route('payment.success') }}">
        <x-stepper :current="3" />

        {{-- Kerangka awal sampai JS menyiapkan tagihan --}}
        <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_22rem] lg:gap-8" data-payment-loading aria-hidden="true">
            <div class="space-y-4"><div class="skeleton h-24"></div><div class="skeleton h-64"></div></div>
            <div class="skeleton h-72"></div>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_22rem] lg:gap-8" data-payment-ready hidden>
            <div class="min-w-0 space-y-6">
                {{-- Hitung mundur: focal point halaman --}}
                <section class="flex flex-wrap items-center justify-between gap-4 rounded-lg bg-brand-soft p-5 text-brand-ink outline-none sm:p-6" tabindex="-1" data-countdown>
                    <div>
                        <p class="text-sm font-medium">Selesaikan pembayaran dalam</p>
                        <p class="mt-1 text-4xl font-extrabold tracking-tight tabular-nums" data-countdown-value aria-live="off">30:00</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs">Kode booking</p>
                        <p class="font-mono text-lg font-bold tracking-wider" data-booking-code></p>
                    </div>
                </section>

                <section class="items-start gap-3 rounded-lg bg-danger-soft p-5 text-danger-ink" data-payment-expired hidden role="alert">
                    <div class="flex items-start gap-3">
                        <x-icon name="circle-x" class="size-6" />
                        <div>
                            <p class="font-bold">Waktu pembayaran habis</p>
                            <p class="mt-1 text-sm">Tagihan ini tidak berlaku lagi. Buat tagihan baru untuk melanjutkan.</p>
                            <button type="button" class="btn-outline mt-4" data-payment-renew>
                                <x-icon name="refresh" class="size-4" />
                                Buat tagihan baru
                            </button>
                        </div>
                    </div>
                </section>

                <section class="card p-5 sm:p-6" aria-labelledby="tagihan-title">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h1 id="tagihan-title" class="text-lg font-bold text-ink" data-method-name></h1>
                            <p class="text-sm text-ink-muted">Total yang harus dibayar</p>
                            <p class="mt-1 text-2xl font-extrabold text-ink" data-payment-total></p>
                        </div>
                        <a href="{{ route('checkout') }}" class="btn-ghost text-sm">Ganti metode</a>
                    </div>

                    {{-- Virtual account --}}
                    <div class="mt-5 rounded-md border border-dashed border-line-strong p-4" data-va-panel hidden>
                        <p class="text-xs text-ink-subtle">Nomor virtual account</p>
                        <div class="mt-1 flex flex-wrap items-center justify-between gap-3">
                            <p class="font-mono text-xl font-bold tracking-wider text-ink" data-va-number></p>
                            <button type="button" class="btn-outline px-3 py-1.5 text-xs" data-copy="" data-copy-message="Nomor VA disalin" data-va-copy>
                                <x-icon name="copy" class="size-4" />
                                Salin
                            </button>
                        </div>
                    </div>

                    {{-- QRIS: placeholder, bukan QR asli --}}
                    <div class="mt-5 flex flex-col items-center rounded-md border border-dashed border-line-strong p-5 text-center" data-qris-panel hidden>
                        <div class="grid size-44 grid-cols-8 gap-0.5 rounded-lg bg-white p-2 ring-1 ring-line" aria-hidden="true" data-qris-art></div>
                        <p class="mt-3 font-mono text-xs text-ink-subtle" data-qris-payload></p>
                        <p class="mt-1 text-xs text-ink-subtle">Tampilan contoh, bukan kode QR yang bisa dipindai.</p>
                    </div>

                    <div class="mt-6">
                        <h2 class="text-sm font-semibold text-ink">Cara bayar</h2>
                        @foreach ($paymentMethods as $method)
                            <ol class="mt-3 list-decimal space-y-1.5 pl-5 text-sm text-ink-muted" data-instruction="{{ $method['id'] }}" hidden>
                                @foreach ($method['instructions'] as $step)
                                    <li>{{ $step }}</li>
                                @endforeach
                            </ol>
                        @endforeach
                    </div>
                </section>

                <section class="card p-5 sm:p-6" aria-labelledby="pemesan-title">
                    <h2 id="pemesan-title" class="text-base font-bold text-ink">Pemesan</h2>
                    <p class="mt-2 font-semibold text-ink" data-contact-name></p>
                    <p class="text-sm text-ink-muted" data-contact-detail></p>
                </section>
            </div>

            <aside class="lg:sticky lg:top-24 lg:self-start">
                {{-- Biaya layanan diambil JS dari ri_checkout --}}
                <x-cart-summary :service-fee="0" title="Pesananmu">
                    <button type="button" class="btn-primary mt-5 w-full py-3" data-pay-confirm>
                        <x-icon name="circle-check" class="size-5" />
                        Saya sudah bayar
                    </button>
                    <p class="mt-2 text-center text-xs text-ink-subtle">Mode demo: tidak ada transaksi sungguhan.</p>
                </x-cart-summary>
            </aside>
        </div>
    </div>

    <script type="application/json" data-payment-methods>@json($methodsForJs)</script>
@endsection

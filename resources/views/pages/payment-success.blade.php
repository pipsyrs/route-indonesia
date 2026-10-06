@extends('layouts.app')

@section('title', 'Pembayaran berhasil')

@section('module', 'order-success')

@section('content')
    <div class="container-page max-w-3xl py-6 md:py-10" data-success-root data-empty-url="{{ route('cart') }}">
        <x-stepper :current="4" />

        <div data-success-ready hidden>
            {{-- Focal point: konfirmasi + kode booking --}}
            <section class="mt-8 text-center">
                <span class="mx-auto grid size-16 place-items-center rounded-full bg-ok-soft text-ok-ink">
                    <x-icon name="circle-check" class="size-9" />
                </span>
                <h1 class="mt-5 text-3xl font-extrabold tracking-tight text-ink md:text-4xl">Pembayaran berhasil</h1>
                <p class="mt-2 text-ink-muted">E-tiket dikirim ke <span class="font-semibold text-ink" data-field="email"></span>.</p>

                <div class="mx-auto mt-6 inline-flex items-center gap-3 rounded-lg border border-dashed border-line-strong bg-surface px-5 py-3">
                    <span class="text-left">
                        <span class="block text-xs text-ink-subtle">Kode booking</span>
                        <span class="block font-mono text-2xl font-bold tracking-wider text-ink" data-field="code"></span>
                    </span>
                    <button type="button" class="btn-outline px-3 py-1.5 text-xs print:hidden" data-copy="" data-copy-message="Kode booking disalin" data-code-copy>
                        <x-icon name="copy" class="size-4" />
                        Salin
                    </button>
                </div>
            </section>

            <section class="card mt-8 p-5 sm:p-6" aria-labelledby="tiket-title">
                <h2 id="tiket-title" class="text-base font-bold text-ink">Detail perjalanan</h2>
                <ul class="mt-4 divide-y divide-line" data-success-items></ul>

                <dl class="mt-4 space-y-2 border-t border-dashed border-line-strong pt-4 text-sm">
                    <div class="flex justify-between gap-4"><dt class="text-ink-muted">Subtotal</dt><dd class="text-ink" data-field="subtotal"></dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-ink-muted">Biaya layanan</dt><dd class="text-ink" data-field="fee"></dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-ink-muted">Metode</dt><dd class="text-ink" data-field="method"></dd></div>
                    <div class="flex justify-between gap-4 border-t border-line pt-3 text-base"><dt class="font-semibold text-ink">Total dibayar</dt><dd class="font-bold text-brand-ink" data-field="total"></dd></div>
                </dl>
            </section>

            <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-center print:hidden">
                <button type="button" class="btn-outline" data-print>
                    <x-icon name="printer" class="size-4" />
                    Cetak
                </button>
                <a href="{{ route('account.orders') }}" class="btn-outline">Lihat pesanan saya</a>
                <a href="{{ route('catalog.index') }}" class="btn-primary">Pesan perjalanan lain</a>
            </div>
        </div>
    </div>

    <template data-success-item-template>
        <li class="py-4 first:pt-0 last:pb-0">
            <p class="font-semibold text-ink" data-field="route"></p>
            <p class="text-sm text-ink-muted" data-field="schedule"></p>
            <p class="text-sm text-ink-muted" data-field="pickup"></p>
            <p class="mt-2 text-sm text-ink"><span class="text-ink-subtle">Penumpang:</span> <span data-field="names"></span></p>
        </li>
    </template>
@endsection

@extends('layouts.app')

@section('title', 'Cek pesanan')

@section('module', 'order-check')

@section('content')
    <div class="container-page grid gap-10 py-10 md:py-14 lg:grid-cols-[1fr_1.1fr] lg:items-start">
        <div class="max-w-md">
            <span class="grid size-12 place-items-center rounded-md bg-brand-soft text-brand-ink">
                <x-icon name="ticket" class="size-6" />
            </span>
            <h1 class="mt-5 text-3xl font-bold tracking-tight text-ink md:text-4xl">Cek status pesanan</h1>
            <p class="mt-3 leading-relaxed text-ink-muted">
                Masukkan kode booking dan email atau nomor HP yang dipakai saat memesan.
            </p>
        </div>

        <section class="card p-5 sm:p-6" aria-labelledby="form-cek-title">
            <h2 id="form-cek-title" class="sr-only">Formulir cek pesanan</h2>

            {{-- Lookup ke database adalah fase berikutnya (POST + rate limit), jadi form belum dikirim --}}
            <form class="grid gap-4" novalidate data-order-check-form>
                <div>
                    <label for="kode-booking" class="form-label">Kode booking</label>
                    <input id="kode-booking" type="text" class="form-input font-mono uppercase tracking-wider" maxlength="10"
                           autocomplete="off" spellcheck="false" placeholder="RID-XXXXXX"
                           aria-describedby="kode-booking-hint kode-booking-error" data-order-code>
                    <p id="kode-booking-hint" class="form-hint">Format RID- diikuti 6 huruf atau angka.</p>
                    <p id="kode-booking-error" class="form-error" hidden></p>
                </div>

                <div>
                    <label for="kontak-cek" class="form-label">Email atau nomor HP</label>
                    <input id="kontak-cek" type="text" class="form-input" maxlength="150" autocomplete="email"
                           aria-describedby="kontak-cek-error" data-order-contact>
                    <p id="kontak-cek-error" class="form-error" hidden></p>
                </div>

                <button type="submit" class="btn-primary mt-1" disabled aria-describedby="cek-fase-info">
                    <x-icon name="search" class="size-4" />
                    Cek pesanan
                </button>

                <p id="cek-fase-info" class="flex items-start gap-2 rounded-md bg-surface-muted p-3 text-xs text-ink-muted">
                    <x-icon name="info-circle" class="size-4" />
                    Pengecekan pesanan sedang disiapkan dan aktif di fase berikutnya. Untuk bantuan sekarang, hubungi (021) 5089-1234.
                </p>
            </form>
        </section>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Keranjang')

@section('module', 'cart-page')

@section('content')
    <div class="container-page py-6 md:py-10">
        <x-stepper :current="1" />

        <h1 class="mt-6 text-2xl font-bold tracking-tight text-ink outline-none md:text-3xl" tabindex="-1" data-cart-heading>Keranjang</h1>

        {{-- Empty state; tampil default sampai JS membaca keranjang --}}
        <div class="mx-auto mt-6 flex max-w-lg flex-col items-center py-16 text-center" data-cart-empty>
            <span class="grid size-16 place-items-center rounded-lg bg-surface-muted text-ink-subtle">
                <x-icon name="shopping-cart" class="size-8" />
            </span>
            <h2 class="mt-5 text-lg font-bold text-ink">Keranjangmu masih kosong</h2>
            <p class="mt-2 text-sm text-ink-muted">Pilih jadwal di katalog, lalu tambahkan tiketnya ke sini.</p>
            <a href="{{ route('catalog.index') }}" class="btn-primary mt-6">Jelajahi katalog</a>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_22rem] lg:gap-8" data-cart-filled hidden>
            <ul class="space-y-4" data-cart-list aria-label="Item keranjang"></ul>

            <aside class="lg:sticky lg:top-24 lg:self-start">
                <x-cart-summary :service-fee="$serviceFee">
                    <a href="{{ route('checkout') }}" class="btn-primary mt-5 w-full py-3" data-checkout-link>
                        Lanjut ke checkout
                        <x-icon name="arrow-right" class="size-4" />
                    </a>
                    <p id="checkout-hint" class="form-hint flex items-center justify-center gap-1.5 text-center" data-checkout-hint hidden>
                        <x-icon name="info-circle" class="size-4" />Pilih kursi untuk semua tiket dulu
                    </p>
                    <a href="{{ route('catalog.index') }}" class="btn-ghost mt-2 w-full">Tambah jadwal lain</a>
                </x-cart-summary>
            </aside>
        </div>
    </div>

    {{-- Denah kursi per item; diisi cart-seat-modal.js --}}
    <x-modal id="seat-modal" title="Pilih kursi" data-seat-modal>
        <p class="text-sm font-semibold text-ink" data-seat-trip></p>
        <p class="mt-0.5 text-sm text-ink-muted" aria-live="polite"><span data-seat-count>0</span> kursi dipilih, maksimal <span data-seat-limit>0</span></p>

        <fieldset class="mx-auto mt-4 w-full max-w-72 rounded-lg border border-line bg-surface p-3 sm:p-4">
            <legend class="sr-only">Denah kursi, bagian depan kendaraan di atas</legend>
            <div class="grid gap-2" data-seat-grid></div>
        </fieldset>

        <ul class="mt-4 flex flex-wrap justify-center gap-x-5 gap-y-2 text-xs text-ink-muted" aria-label="Keterangan denah">
            <li class="flex items-center gap-2"><span class="size-4 rounded-md border border-line-strong bg-seat-available"></span> Tersedia</li>
            <li class="flex items-center gap-2"><span class="size-4 rounded-md bg-seat-occupied"></span> Terisi</li>
            <li class="flex items-center gap-2"><span class="size-4 rounded-md bg-seat-selected"></span> Dipilih</li>
            <li class="flex items-center gap-2"><x-icon name="steering-wheel" class="size-4 text-ink-subtle" /> Sopir</li>
        </ul>
        <p class="form-error text-center" role="alert" data-seat-error hidden></p>

        <x-slot:footer>
            <button type="button" class="btn-ghost" data-modal-close>Batal</button>
            <button type="button" class="btn-primary" data-seat-save disabled>Simpan kursi</button>
        </x-slot:footer>
    </x-modal>

    <x-seat-map-templates />

    {{-- Template baris; diisi JS hanya lewat textContent --}}
    <template data-cart-item-template>
        <li class="card grid gap-4 p-5 sm:grid-cols-[1fr_auto] sm:p-6" data-cart-item>
            <div class="min-w-0">
                <p class="text-lg font-bold text-ink" data-field="route"></p>
                <p class="text-sm text-ink-subtle" data-field="operator"></p>
                <dl class="mt-3 grid gap-1.5 text-sm text-ink-muted">
                    <div class="flex items-center gap-2"><dt class="sr-only">Tanggal</dt><x-icon name="calendar" class="size-4" /><dd data-field="date"></dd></div>
                    <div class="flex items-center gap-2"><dt class="sr-only">Jam</dt><x-icon name="clock" class="size-4" /><dd data-field="time"></dd></div>
                    <div class="flex items-center gap-2"><dt class="sr-only">Jemput</dt><x-icon name="map-pin" class="size-4" /><dd data-field="pickup"></dd></div>
                </dl>

                {{-- Status kursi; disembunyikan untuk produk --}}
                <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2" data-seat-block>
                    <p class="flex items-center gap-1.5 text-sm font-semibold text-brand-ink" data-seat-done hidden>
                        <x-icon name="circle-check" class="size-4" /><span data-field="seat-count"></span>
                    </p>
                    <p class="flex items-center gap-1.5 text-sm font-semibold text-warn-ink" data-seat-pending>
                        <x-icon name="info-circle" class="size-4" /><span data-field="seat-pending"></span>
                    </p>
                    <button type="button" class="btn-outline px-3 py-1.5" data-modal-open="seat-modal" data-seat-open>
                        <x-icon name="armchair" class="size-4" /><span data-field="seat-action">Pilih kursi</span>
                    </button>
                </div>
            </div>

            <div class="flex items-end justify-between gap-4 border-t border-line pt-4 sm:flex-col sm:border-t-0 sm:pt-0">
                <div class="sm:text-right">
                    <p class="text-xs text-ink-subtle" data-field="price"></p>
                    <p class="text-lg font-bold text-ink" data-field="line-total"></p>
                </div>
                <div class="flex items-center gap-2">
                    {{-- Hanya produk; qty tiket = jumlah kursi --}}
                    <label class="sr-only" data-qty-label>Jumlah</label>
                    <div class="flex items-center rounded-md border border-line-strong" data-qty-control>
                        <button type="button" class="grid size-9 place-items-center text-ink-muted hover:text-ink disabled:opacity-40" data-qty-step="-1">
                            <x-icon name="minus" class="size-4" /><span class="sr-only">Kurangi</span>
                        </button>
                        <input type="number" inputmode="numeric" min="1" class="w-10 border-0 bg-transparent text-center text-sm font-semibold tabular-nums text-ink [appearance:textfield] focus:outline-none [&::-webkit-inner-spin-button]:appearance-none" data-qty-input>
                        <button type="button" class="grid size-9 place-items-center text-ink-muted hover:text-ink disabled:opacity-40" data-qty-step="1">
                            <x-icon name="plus" class="size-4" /><span class="sr-only">Tambah</span>
                        </button>
                    </div>
                    <button type="button" class="btn-ghost px-2.5 text-ink-subtle hover:text-danger-ink" data-remove>
                        <x-icon name="trash" class="size-5" /><span class="sr-only">Hapus item</span>
                    </button>
                </div>
            </div>
        </li>
    </template>
@endsection

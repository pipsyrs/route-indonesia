@extends('layouts.app')

@section('title', 'Keranjang')

@section('module', 'cart-page')

@section('content')
    <div class="container-page py-6 md:py-10">
        <x-stepper :current="1" />

        <h1 class="mt-6 text-2xl font-bold tracking-tight text-ink outline-none md:text-3xl" tabindex="-1" data-cart-heading>Keranjang</h1>

        {{-- Empty state; tampil default sampai JS membaca keranjang --}}
        <div class="mx-auto mt-6 flex max-w-lg flex-col items-center py-16 text-center" data-cart-empty>
            <span class="grid size-16 place-items-center rounded-2xl bg-surface-muted text-ink-subtle">
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
                    <a href="{{ route('checkout') }}" class="btn-primary mt-5 w-full py-3">
                        Lanjut ke checkout
                        <x-icon name="arrow-right" class="size-4" />
                    </a>
                    <a href="{{ route('catalog.index') }}" class="btn-ghost mt-2 w-full">Tambah jadwal lain</a>
                </x-cart-summary>
            </aside>
        </div>
    </div>

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
            </div>

            <div class="flex items-end justify-between gap-4 border-t border-line pt-4 sm:flex-col sm:border-t-0 sm:pt-0">
                <div class="sm:text-right">
                    <p class="text-xs text-ink-subtle" data-field="price"></p>
                    <p class="text-lg font-bold text-ink" data-field="line-total"></p>
                </div>
                <div class="flex items-center gap-2">
                    <label class="sr-only" data-qty-label>Jumlah tiket</label>
                    <div class="flex items-center rounded-xl border border-line-strong">
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

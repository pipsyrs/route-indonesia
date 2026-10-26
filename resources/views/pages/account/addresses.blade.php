@extends('layouts.app')

@section('title', 'Alamat')

{{-- account-forms dipasang untuk tombol Keluar di sidebar --}}
@section('module', 'account-forms')

@section('content')
    <div class="container-page grid gap-8 py-8 md:py-12 lg:grid-cols-[15rem_1fr] lg:gap-12">
        <x-account-sidebar :user="$user" />

        {{-- Daftar alamat dirender modules/account-addresses.js dari localStorage (mode demo) --}}
        <div class="min-w-0 max-w-3xl" data-module="account-addresses"
             data-seed-name="{{ $user['name'] }}" data-seed-phone="{{ $user['phone'] }}">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-ink md:text-3xl">Alamat</h1>
                    <p class="mt-1 text-sm text-ink-muted" data-address-count></p>
                </div>
                <button type="button" class="btn-primary" data-modal-open="address-modal">
                    <x-icon name="plus" class="size-4" />
                    Tambah alamat
                </button>
            </div>

            <ul class="mt-6 grid gap-4 sm:grid-cols-2" data-address-list aria-label="Daftar alamat"></ul>

            <div class="mt-6 flex flex-col items-center rounded-lg border border-dashed border-line-strong px-6 py-12 text-center" data-address-empty hidden>
                <x-icon name="map-pin" class="size-7 text-ink-subtle" />
                <p class="mt-3 font-semibold text-ink">Belum ada alamat</p>
                <p class="mt-1 text-sm text-ink-muted">Simpan alamat agar checkout lebih cepat.</p>
                <button type="button" class="btn-primary mt-5" data-modal-open="address-modal">Tambah alamat</button>
            </div>

            <template data-address-template>
                <li class="card flex flex-col p-5 outline-none transition-colors focus-visible:ring-2 focus-visible:ring-brand-500/40 data-primary:border-brand-500 data-primary:ring-1 data-primary:ring-brand-500" tabindex="-1">
                    <div class="flex items-center gap-2">
                        <x-icon name="map-pin" class="size-4 shrink-0 text-ink-subtle" />
                        <h2 class="truncate font-semibold text-ink" data-slot="label"></h2>
                        <span class="chip ml-auto shrink-0 bg-brand-soft text-brand-ink" data-slot="primary" hidden>
                            <x-icon name="check" class="size-3.5" />
                            Utama
                        </span>
                    </div>
                    <p class="mt-3 text-sm font-medium text-ink" data-slot="name"></p>
                    <p class="text-sm text-ink-muted tabular-nums" data-slot="phone"></p>
                    <p class="mt-2 text-sm break-words text-ink-muted" data-slot="address"></p>
                    <p class="text-sm text-ink-muted" data-slot="region"></p>
                    <div class="mt-auto flex flex-wrap items-center gap-2 pt-4">
                        <button type="button" class="btn-outline px-3.5 py-2" data-action="set-primary">Jadikan utama</button>
                        <button type="button" class="btn-ghost ml-auto px-3 py-2 hover:bg-danger-soft hover:text-danger-ink" data-action="delete">
                            <x-icon name="trash" class="size-4" />
                            Hapus
                        </button>
                    </div>
                </li>
            </template>

            <x-modal id="address-modal" title="Tambah alamat" class="max-w-lg">
                <form id="address-form" method="dialog" novalidate data-address-form>
                    <fieldset>
                        <legend class="form-label">Label</legend>
                        <div class="flex flex-wrap gap-2">
                            @foreach (['Rumah', 'Kantor', 'Lainnya'] as $label)
                                <label class="cursor-pointer">
                                    <input type="radio" name="address-label" value="{{ $label }}" class="peer sr-only" @checked($loop->first)>
                                    <span class="inline-flex rounded-full border border-line-strong px-3.5 py-1.5 text-sm font-medium text-ink-muted transition-colors peer-checked:border-brand-600 peer-checked:bg-brand-soft peer-checked:text-brand-ink peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand-500">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="alamat-nama" class="form-label">Nama penerima</label>
                            <input id="alamat-nama" type="text" class="form-input" autocomplete="name" maxlength="60"
                                   data-field="name" data-rule="name" aria-describedby="alamat-nama-error">
                            <p id="alamat-nama-error" class="form-error" hidden></p>
                        </div>
                        <div>
                            <label for="alamat-hp" class="form-label">Nomor HP</label>
                            <input id="alamat-hp" type="tel" class="form-input" autocomplete="tel" inputmode="tel" maxlength="16" placeholder="08xxxxxxxxxx"
                                   data-field="phone" data-rule="phone" aria-describedby="alamat-hp-error">
                            <p id="alamat-hp-error" class="form-error" hidden></p>
                        </div>
                        <div class="sm:col-span-2">
                            <label for="alamat-lengkap" class="form-label">Alamat lengkap</label>
                            <textarea id="alamat-lengkap" rows="3" class="form-input resize-none" autocomplete="street-address" maxlength="200"
                                      placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan"
                                      data-field="address" data-rule="required" aria-describedby="alamat-lengkap-error"></textarea>
                            <p id="alamat-lengkap-error" class="form-error" hidden></p>
                        </div>
                        <div>
                            <label for="alamat-kota" class="form-label">Kota/Kabupaten</label>
                            <input id="alamat-kota" type="text" class="form-input" autocomplete="address-level2" maxlength="60"
                                   data-field="city" data-rule="required" aria-describedby="alamat-kota-error">
                            <p id="alamat-kota-error" class="form-error" hidden></p>
                        </div>
                        <div>
                            <label for="alamat-pos" class="form-label">Kode pos</label>
                            <input id="alamat-pos" type="text" class="form-input tabular-nums" autocomplete="postal-code" inputmode="numeric" maxlength="5"
                                   data-field="postal" data-postal aria-describedby="alamat-pos-error">
                            <p id="alamat-pos-error" class="form-error" hidden></p>
                        </div>
                    </div>
                </form>

                <x-slot:footer>
                    <button type="button" class="btn-outline" data-modal-close>Batal</button>
                    <button type="submit" form="address-form" class="btn-primary">Simpan alamat</button>
                </x-slot:footer>
            </x-modal>
        </div>
    </div>
@endsection

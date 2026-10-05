@extends('layouts.app')

@section('title', 'Profil')

@section('module', 'account-forms')

@section('content')
    <div class="container-page grid gap-8 py-8 md:py-12 lg:grid-cols-[15rem_1fr] lg:gap-12">
        <x-account-sidebar :user="$user" />

        <div class="min-w-0 max-w-2xl space-y-6">
            <h1 class="text-2xl font-bold tracking-tight text-ink md:text-3xl">Profil</h1>

            <form class="card p-5 sm:p-6" novalidate data-form="profile" aria-labelledby="profil-data-title">
                <h2 id="profil-data-title" class="text-base font-bold text-ink">Data diri</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="profil-nama" class="form-label">Nama lengkap</label>
                        <input id="profil-nama" type="text" class="form-input" autocomplete="name" maxlength="60" value="{{ $user['name'] }}"
                               data-field="name" data-rule="name" aria-describedby="profil-nama-error">
                        <p id="profil-nama-error" class="form-error" hidden></p>
                    </div>
                    <div>
                        <label for="profil-email" class="form-label">Email</label>
                        <input id="profil-email" type="email" class="form-input" autocomplete="email" maxlength="100" value="{{ $user['email'] }}"
                               data-field="email" data-rule="email" aria-describedby="profil-email-error">
                        <p id="profil-email-error" class="form-error" hidden></p>
                    </div>
                    <div>
                        <label for="profil-hp" class="form-label">Nomor HP</label>
                        <input id="profil-hp" type="tel" class="form-input" autocomplete="tel" inputmode="tel" maxlength="16" value="{{ $user['phone'] }}"
                               data-rule="phone" aria-describedby="profil-hp-error">
                        <p id="profil-hp-error" class="form-error" hidden></p>
                    </div>
                </div>
                <button type="submit" class="btn-primary mt-5">Simpan perubahan</button>
            </form>

            <form class="card p-5 sm:p-6" novalidate data-form="password" aria-labelledby="profil-sandi-title">
                <h2 id="profil-sandi-title" class="text-base font-bold text-ink">Ganti kata sandi</h2>
                <div class="mt-5 grid gap-4">
                    <div>
                        <label for="sandi-lama" class="form-label">Kata sandi saat ini</label>
                        <input id="sandi-lama" type="password" class="form-input" autocomplete="current-password" maxlength="100"
                               data-rule="required" aria-describedby="sandi-lama-error">
                        <p id="sandi-lama-error" class="form-error" hidden></p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="sandi-baru" class="form-label">Kata sandi baru</label>
                            <input id="sandi-baru" type="password" class="form-input" autocomplete="new-password" maxlength="100"
                                   data-rule="password" aria-describedby="sandi-baru-error">
                            <p id="sandi-baru-error" class="form-error" hidden></p>
                        </div>
                        <div>
                            <label for="sandi-ulang" class="form-label">Ulangi kata sandi baru</label>
                            <input id="sandi-ulang" type="password" class="form-input" autocomplete="new-password" maxlength="100"
                                   data-rule="same-as" data-same-as="#sandi-baru" aria-describedby="sandi-ulang-error">
                            <p id="sandi-ulang-error" class="form-error" hidden></p>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn-outline mt-5">Ganti kata sandi</button>
            </form>

            <p class="text-xs text-ink-subtle"><span class="font-semibold">Mode demo.</span> Perubahan tidak disimpan ke server.</p>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Daftar')

@section('module', 'account-forms')

@section('content')
    <div class="container-page grid max-w-md py-10 md:py-16">
        <h1 class="text-3xl font-extrabold tracking-tight text-ink">Buat akun</h1>
        <p class="mt-2 text-ink-muted">Satu akun untuk semua tiket perjalananmu.</p>

        <form class="card mt-6 space-y-4 p-5 sm:p-6" novalidate data-form="register" data-redirect="{{ route('account.dashboard') }}">
            <div>
                <label for="daftar-nama" class="form-label">Nama lengkap</label>
                <input id="daftar-nama" type="text" class="form-input" autocomplete="name" maxlength="60"
                       data-field="name" data-rule="name" aria-describedby="daftar-nama-error">
                <p id="daftar-nama-error" class="form-error" hidden></p>
            </div>
            <div>
                <label for="daftar-email" class="form-label">Email</label>
                <input id="daftar-email" type="email" class="form-input" autocomplete="email" maxlength="100"
                       data-field="email" data-rule="email" aria-describedby="daftar-email-error">
                <p id="daftar-email-error" class="form-error" hidden></p>
            </div>
            <div>
                <label for="daftar-hp" class="form-label">Nomor HP</label>
                <input id="daftar-hp" type="tel" class="form-input" autocomplete="tel" inputmode="tel" maxlength="16" placeholder="08xxxxxxxxxx"
                       data-rule="phone" aria-describedby="daftar-hp-error">
                <p id="daftar-hp-error" class="form-error" hidden></p>
            </div>
            <div>
                <label for="daftar-sandi" class="form-label">Kata sandi</label>
                <input id="daftar-sandi" type="password" class="form-input" autocomplete="new-password" maxlength="100"
                       data-rule="password" aria-describedby="daftar-sandi-hint daftar-sandi-error">
                <p id="daftar-sandi-hint" class="form-hint">Minimal 8 karakter.</p>
                <p id="daftar-sandi-error" class="form-error" hidden></p>
            </div>
            <div>
                <label for="daftar-sandi-ulang" class="form-label">Ulangi kata sandi</label>
                <input id="daftar-sandi-ulang" type="password" class="form-input" autocomplete="new-password" maxlength="100"
                       data-rule="same-as" data-same-as="#daftar-sandi" aria-describedby="daftar-sandi-ulang-error">
                <p id="daftar-sandi-ulang-error" class="form-error" hidden></p>
            </div>
            <label class="flex items-start gap-2.5 text-sm text-ink-muted">
                <input type="checkbox" class="mt-0.5 size-4 shrink-0 rounded accent-brand-600" data-rule="agree" aria-describedby="daftar-setuju-error">
                <span>Saya setuju dengan syarat dan kebijakan privasi.</span>
            </label>
            <p id="daftar-setuju-error" class="form-error -mt-2" hidden></p>
            <button type="submit" class="btn-primary w-full py-3">Daftar</button>
            <p class="rounded-xl bg-surface-muted px-3.5 py-2.5 text-xs text-ink-subtle">
                <span class="font-semibold text-ink-muted">Mode demo.</span> Akun tidak dibuat di server.
            </p>
        </form>

        <p class="mt-6 text-center text-sm text-ink-muted">
            Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-brand-ink hover:underline">Masuk</a>
        </p>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Masuk')

@section('module', 'account-forms')

@section('content')
    <div class="container-page grid max-w-md py-10 md:py-16">
        <h1 class="text-3xl font-extrabold tracking-tight text-ink">Masuk</h1>
        <p class="mt-2 text-ink-muted">Lihat riwayat pesanan dan isi data pemesan lebih cepat.</p>

        {{-- Tanpa action: JS validasi lalu simpan sesi demo di browser --}}
        <form class="card mt-6 space-y-4 p-5 sm:p-6" novalidate data-form="login" data-redirect="{{ route('account.dashboard') }}">
            <div>
                <label for="masuk-email" class="form-label">Email</label>
                <input id="masuk-email" type="email" class="form-input" autocomplete="email" maxlength="100"
                       data-field="email" data-rule="email" aria-describedby="masuk-email-error">
                <p id="masuk-email-error" class="form-error" hidden></p>
            </div>
            <div>
                <label for="masuk-sandi" class="form-label">Kata sandi</label>
                <input id="masuk-sandi" type="password" class="form-input" autocomplete="current-password" maxlength="100"
                       data-rule="password" aria-describedby="masuk-sandi-error">
                <p id="masuk-sandi-error" class="form-error" hidden></p>
            </div>
            <button type="submit" class="btn-primary w-full py-3">Masuk</button>
            <p class="rounded-xl bg-surface-muted px-3.5 py-2.5 text-xs text-ink-subtle">
                <span class="font-semibold text-ink-muted">Mode demo.</span> Kata sandi tidak dikirim ke mana pun.
            </p>
        </form>

        <p class="mt-6 text-center text-sm text-ink-muted">
            Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-brand-ink hover:underline">Daftar</a>
        </p>
    </div>
@endsection

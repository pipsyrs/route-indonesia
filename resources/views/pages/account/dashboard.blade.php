@extends('layouts.app')

@section('title', 'Akun saya')

@section('module', 'account-forms')

@section('content')
    <div class="container-page grid gap-8 py-8 md:py-12 lg:grid-cols-[15rem_1fr] lg:gap-12">
        <x-account-sidebar :user="$user" />

        <div class="min-w-0 space-y-8">
            {{-- Sapaan sebagai focal point --}}
            <section class="rounded-3xl bg-brand-600 p-6 text-white sm:p-8">
                <p class="text-sm text-brand-100">Halo,</p>
                <h1 class="mt-1 text-3xl font-extrabold tracking-tight">{{ $user['name'] }}</h1>
                <p class="mt-2 text-sm text-brand-100">
                    Bergabung sejak {{ \Illuminate\Support\Carbon::parse($user['joined'])->translatedFormat('F Y') }}
                </p>
                <div class="mt-6 flex flex-wrap gap-2">
                    <a href="{{ route('catalog.index') }}" class="btn bg-white text-brand-800 hover:bg-brand-50">
                        <x-icon name="search" class="size-4" />
                        Cari jadwal
                    </a>
                    <a href="{{ route('cart') }}" class="btn bg-white/15 text-white hover:bg-white/25">
                        <x-icon name="shopping-cart" class="size-4" />
                        Keranjang
                    </a>
                </div>
            </section>

            <section aria-labelledby="pesanan-terbaru-title">
                <div class="flex items-center justify-between gap-4">
                    <h2 id="pesanan-terbaru-title" class="text-lg font-bold text-ink">Pesanan terbaru</h2>
                    <a href="{{ route('account.orders') }}" class="text-sm font-semibold text-brand-ink hover:underline">Lihat semua</a>
                </div>
                <x-account-order-list :orders="$orders" class="mt-4" />
            </section>
        </div>
    </div>
@endsection

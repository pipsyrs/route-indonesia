@extends('layouts.app')

@section('title', 'Pesanan saya')

{{-- account-forms dipasang untuk tombol Keluar di sidebar --}}
@section('module', 'account-forms')

@section('content')
    <div class="container-page grid gap-8 py-8 md:py-12 lg:grid-cols-[15rem_1fr] lg:gap-12">
        <x-account-sidebar :user="$user" />

        <div class="min-w-0">
            <h1 class="text-2xl font-bold tracking-tight text-ink md:text-3xl">Pesanan saya</h1>
            <p class="mt-1 text-sm text-ink-muted">{{ count($orders) }} pesanan</p>
            <x-account-order-list :orders="$orders" class="mt-6" />
        </div>
    </div>
@endsection

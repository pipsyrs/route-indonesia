@extends('layouts.app')

@php
    $routeTitle = $query['route_name'] ?? ($query['q'] ? 'Hasil pencarian "'.$query['q'].'"' : null) ?? ($query['from'] && $query['to'] ? $query['from'].' ke '.$query['to'] : ($query['from'] ?? $query['to'] ?? 'Semua rute'));
@endphp

@section('title', 'Katalog: '.$routeTitle)

@section('module', 'search-filter')

@section('content')
    {{-- Ringkasan filter; form ubah memakai <details> agar tetap jalan tanpa JS --}}
    <section class="border-b border-line bg-surface">
        <div class="container-page py-5">
            <details class="group" @if ($searchError) open @endif>
                <summary class="flex cursor-pointer list-none flex-wrap items-center justify-between gap-4 [&::-webkit-details-marker]:hidden">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold tracking-[0.14em] text-brand-ink uppercase">Katalog jadwal</p>
                        <h1 class="mt-1 flex flex-wrap items-center gap-x-2 text-xl font-bold tracking-tight text-ink md:text-2xl">
                            @if ($query['route_name'])
                                {{ $query['route_name'] }}
                            @elseif ($query['q'])
                                Hasil pencarian "{{ $query['q'] }}"
                            @elseif ($query['from'] && $query['to'])
                                {{ $query['from'] }}
                                <x-icon name="arrow-right" class="size-5 text-ink-subtle" />
                                <span class="sr-only">ke</span>
                                {{ $query['to'] }}
                            @elseif ($query['from'])
                                Dari {{ $query['from'] }}
                            @elseif ($query['to'])
                                Menuju {{ $query['to'] }}
                            @else
                                Semua rute
                            @endif
                        </h1>
                        @if ($query['date_label'])
                            <p class="mt-1 text-sm text-ink-muted">Berangkat {{ $query['date_label'] }}</p>
                        @endif
                    </div>
                    <span class="btn-outline">
                        <x-icon name="adjustments-horizontal" class="size-4" />
                        <span class="group-open:hidden">Ubah pencarian</span>
                        <span class="hidden group-open:inline">Tutup</span>
                    </span>
                </summary>

                <div class="mt-5 border-t border-line pt-5">
                    <x-search-form :query="$query" id-prefix="ubah" />
                </div>
            </details>
        </div>

    </section>

    <div class="container-page py-8">
        @if ($searchError)
            <div class="mb-6 flex items-start gap-3 rounded-md bg-warn-soft p-4 text-sm text-warn-ink" role="alert">
                <x-icon name="alert-circle" class="size-5" />
                <p>{{ $searchError }}</p>
            </div>
        @endif

        @if ($trips === [])
            {{-- Empty state dari server: rute/tanggal tanpa jadwal --}}
            <div class="mx-auto flex max-w-lg flex-col items-center py-16 text-center">
                <span class="grid size-14 place-items-center rounded-lg bg-surface-muted text-ink-subtle">
                    <x-icon name="bus" class="size-7" />
                </span>
                <h2 class="mt-5 text-lg font-bold text-ink">Belum ada jadwal untuk pencarian ini</h2>
                <p class="mt-2 text-sm text-ink-muted">
                    @if ($searchError)
                        Pilih kota tujuan yang berbeda dari kota asal.
                    @else
                        Coba kata kunci lain, misalnya nama kota, operator, atau rute, atau lihat semua rute.
                    @endif
                </p>
                <a href="{{ route('catalog.index', ['date' => $query['date']]) }}" class="btn-outline mt-6">Lihat semua rute</a>
            </div>
        @else
            <div>
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <p class="text-sm text-ink-muted" aria-live="polite">
                            <span class="font-semibold text-ink" data-filter-count>{{ count($trips) }}</span> jadwal ditemukan
                        </p>
                        <div class="flex flex-wrap items-center gap-2">
                            {{-- Tanggal opsional: reload dengan ?date=, filter lain ikut via hidden input --}}
                            <form action="{{ route('catalog.index') }}" method="get" class="flex items-center gap-2">
                                @foreach (['q', 'route', 'from', 'to'] as $key)
                                    @if ($query[$key])
                                        <input type="hidden" name="{{ $key }}" value="{{ $query[$key] }}">
                                    @endif
                                @endforeach
                                <label for="tanggal" class="sr-only">Tanggal berangkat</label>
                                <input id="tanggal" type="date" name="date" class="form-input w-auto"
                                       value="{{ $query['date'] }}" min="{{ now()->toDateString() }}">
                                <button type="submit" class="btn-outline">Terapkan</button>
                            </form>
                            <label for="urutkan" class="sr-only">Urutkan</label>
                            <select id="urutkan" class="form-input w-auto appearance-none pr-8" data-filter-sort>
                                <option value="depart">Berangkat paling awal</option>
                                <option value="price">Harga terendah</option>
                                <option value="duration">Durasi tercepat</option>
                                <option value="rating">Rating tertinggi</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-4" data-filter-list>
                        @foreach ($trips as $trip)
                            <x-trip-card :trip="$trip" :query="$query" :facilities="$facilities" />
                        @endforeach
                    </div>
            </div>
        @endif
    </div>
@endsection

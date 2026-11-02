@extends('layouts.app')

@section('title', __('Community'))

@php
    // Sample communities until real partners are listed.
    $communities = [
        ['name' => 'Community A', 'city' => 'Jakarta', 'desc' => __('Weekend trips and meetups for travelers in the capital.')],
        ['name' => 'Community B', 'city' => 'Bandung', 'desc' => __('Hiking and nature trips around West Java.')],
        ['name' => 'Community C', 'city' => 'Yogyakarta', 'desc' => __('Culture and food trips across Central Java.')],
    ];
@endphp

@section('content')
    <section class="container-page pt-12 pb-10">
        <p class="text-xs font-semibold tracking-[0.18em] text-brand-500 uppercase">{{ __('Community') }}</p>
        <h1 class="mt-3 max-w-[22ch] text-3xl font-semibold tracking-tight text-balance text-ink md:text-5xl">{{ __('Grow with RouteIndonesia.') }}</h1>
        <p class="mt-4 max-w-[60ch] text-ink-muted">{{ __('We partner with operators, companies, and tourism businesses to move more people across Indonesia.') }}</p>
    </section>

    <section class="container-page py-6" aria-label="{{ __('Communities') }}">
        <ul class="grid gap-4 md:grid-cols-3">
            @foreach ($communities as $community)
                <li class="card p-6">
                    <x-icon name="users" class="size-6 text-brand-ink" />
                    <h2 class="mt-4 font-semibold text-ink">{{ $community['name'] }}</h2>
                    <p class="mt-1 flex items-center gap-1.5 text-xs font-medium text-ink-subtle">
                        <x-icon name="map-pin" class="size-3.5" />
                        {{ $community['city'] }}
                    </p>
                    <p class="mt-1.5 text-sm leading-relaxed text-ink-muted">{{ $community['desc'] }}</p>
                </li>
            @endforeach
        </ul>
    </section>
@endsection

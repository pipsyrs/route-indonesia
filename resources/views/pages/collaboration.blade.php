@extends('layouts.app')

@section('title', __('Collaboration'))

@php
    $partners = [
        ['icon' => 'bus', 'title' => __('Travel operators'), 'desc' => __('List your schedules and fill more seats through our booking platform.')],
        ['icon' => 'building-bank', 'title' => __('Corporate clients'), 'desc' => __('Arrange regular employee trips with one invoice and dedicated support.')],
        ['icon' => 'route', 'title' => __('Tourism and events'), 'desc' => __('Bundle transport with your tour packages or event tickets.')],
    ];
@endphp

@section('content')
    <section class="container-page pt-12 pb-10">
        <p class="text-xs font-semibold tracking-[0.18em] text-brand-500 uppercase">{{ __('Collaboration') }}</p>
        <h1 class="mt-3 max-w-[22ch] text-3xl font-semibold tracking-tight text-balance text-ink md:text-5xl">{{ __('Grow with RouteIndonesia.') }}</h1>
        <p class="mt-4 max-w-[60ch] text-ink-muted">{{ __('We partner with operators, companies, and tourism businesses to move more people across Indonesia.') }}</p>
    </section>

    <section class="container-page py-6" aria-label="{{ __('Partnership types') }}">
        <ul class="grid gap-4 md:grid-cols-3">
            @foreach ($partners as $partner)
                <li class="card p-6">
                    <x-icon :name="$partner['icon']" class="size-6 text-brand-ink" />
                    <h2 class="mt-4 font-semibold text-ink">{{ $partner['title'] }}</h2>
                    <p class="mt-1.5 text-sm leading-relaxed text-ink-muted">{{ $partner['desc'] }}</p>
                </li>
            @endforeach
        </ul>
    </section>

    <section class="container-page pt-10">
        <div class="flex flex-col gap-6 rounded-lg bg-brand-600 p-6 text-white sm:flex-row sm:items-center sm:justify-between sm:p-10">
            <div>
                <h2 class="text-xl font-semibold">{{ __("Let's talk partnership") }}</h2>
                <p class="mt-1 text-sm text-brand-100">{{ __('Send us a short note about your business and we will reply within two working days.') }}</p>
            </div>
            <a href="mailto:halo@routeindonesia.id" class="btn shrink-0 bg-white text-brand-800 hover:bg-brand-50">
                <x-icon name="mail" class="size-4" />
                halo@routeindonesia.id
            </a>
        </div>
    </section>
@endsection

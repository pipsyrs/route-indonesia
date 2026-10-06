@extends('layouts.app')

@section('title', __('Trip'))

@php
    $steps = [
        ['icon' => 'search', 'title' => __('Pick your route'), 'desc' => __('Choose origin, destination, and date. We show every departure from verified operators.')],
        ['icon' => 'armchair', 'title' => __('Choose your seat'), 'desc' => __('See the seat map and pick the spot you like before you pay.')],
        ['icon' => 'wallet', 'title' => __('Pay and go'), 'desc' => __('Pay via VA, e-wallet, or QRIS. Your e-ticket arrives right away.')],
    ];
@endphp

@section('content')
    <section class="container-page pt-12 pb-10">
        <p class="text-xs font-semibold tracking-[0.18em] text-brand-500 uppercase">{{ __('Trip') }}</p>
        <h1 class="mt-3 max-w-[22ch] text-3xl font-semibold tracking-tight text-balance text-ink md:text-5xl">{{ __('Intercity trips, without the hassle.') }}</h1>
        <p class="mt-4 max-w-[60ch] text-ink-muted">{{ __('Shuttle and travel vans between major cities in Java and beyond, with pickup points close to you.') }}</p>
        <a href="{{ route('catalog.index') }}" class="btn-primary mt-7">
            {{ __('Find schedules') }}
            <x-icon name="arrow-right" class="size-4" />
        </a>
    </section>

    <section class="container-page py-10" aria-labelledby="trip-steps-title">
        <h2 id="trip-steps-title" class="text-2xl font-semibold tracking-tight text-ink">{{ __('How it works') }}</h2>
        <ol class="mt-6 grid gap-4 md:grid-cols-3">
            @foreach ($steps as $step)
                <li class="card p-6">
                    <span class="text-sm font-semibold text-brand-500 tabular-nums">0{{ $loop->iteration }}</span>
                    <x-icon :name="$step['icon']" class="mt-4 size-6 text-brand-ink" />
                    <h3 class="mt-3 font-semibold text-ink">{{ $step['title'] }}</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-ink-muted">{{ $step['desc'] }}</p>
                </li>
            @endforeach
        </ol>
    </section>
@endsection

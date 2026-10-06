@extends('layouts.app')

@section('title', __('Careers'))

@php
    $values = [
        ['title' => __('Ship small, ship often'), 'desc' => __('We improve the product a little every week instead of waiting for perfect.')],
        ['title' => __('Passengers first'), 'desc' => __('Every decision starts with the person sitting in the seat.')],
        ['title' => __('Remote friendly'), 'desc' => __('Work from anywhere in Indonesia, meet the team in person a few times a year.')],
    ];
@endphp

@section('content')
    <section class="container-page pt-12 pb-10">
        <p class="text-xs font-semibold tracking-[0.18em] text-brand-500 uppercase">{{ __('Careers') }}</p>
        <h1 class="mt-3 max-w-[22ch] text-3xl font-semibold tracking-tight text-balance text-ink md:text-5xl">{{ __('Help us move Indonesia.') }}</h1>
        <p class="mt-4 max-w-[60ch] text-ink-muted">{{ __('We are a small team building the easiest way to travel between cities.') }}</p>
    </section>

    <section class="container-page py-6" aria-labelledby="career-values-title">
        <h2 id="career-values-title" class="text-2xl font-semibold tracking-tight text-ink">{{ __('How we work') }}</h2>
        <dl class="mt-6 grid gap-x-10 gap-y-6 border-t border-line pt-6 md:grid-cols-3">
            @foreach ($values as $value)
                <div>
                    <dt class="font-semibold text-ink">{{ $value['title'] }}</dt>
                    <dd class="mt-1.5 text-sm leading-relaxed text-ink-muted">{{ $value['desc'] }}</dd>
                </div>
            @endforeach
        </dl>
    </section>

    <section class="container-page pt-10" aria-labelledby="career-open-title">
        <div class="card p-6 sm:p-10">
            <h2 id="career-open-title" class="text-xl font-semibold text-ink">{{ __('Open positions') }}</h2>
            <p class="mt-2 max-w-[60ch] text-sm text-ink-muted">{{ __('There are no open positions right now. Send your CV anyway and we will reach out when a role fits you.') }}</p>
            <a href="mailto:halo@routeindonesia.id" class="btn-outline mt-6">
                <x-icon name="mail" class="size-4" />
                {{ __('Send your CV') }}
            </a>
        </div>
    </section>
@endsection

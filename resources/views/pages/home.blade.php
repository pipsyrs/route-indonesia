@extends('layouts.app')

@section('title', __('Intercity travel tickets'))

@section('content')
    {{-- Hero: slider sebagai focal point, form pencarian menempel di bawahnya --}}
    <x-hero-slider />

    <section class="container-page relative z-10 -mt-10 sm:-mt-14" aria-labelledby="cari-title">
        <div class="card p-4 shadow-xl shadow-brand-900/5 sm:p-6">
            <h2 id="cari-title" class="sr-only">{{ __('Search travel schedules') }}</h2>
            <x-search-form :cities="$cities" :query="['date' => $defaultDate]" />
        </div>
    </section>

    {{-- Rute populer: daftar dua kolom, bukan kartu seragam --}}
    <section class="container-page py-8 md:py-10" aria-labelledby="rute-title">
        <h2 id="rute-title" class="text-2xl font-bold tracking-tight text-ink md:text-3xl">{{ __('Popular routes this week') }}</h2>
        <p class="mt-2 max-w-[60ch] text-ink-muted">{{ __("Jump straight to schedules for tomorrow's departures.") }}</p>

        <ul class="mt-8 grid gap-x-10 md:grid-cols-2">
            @foreach ($popularRoutes as $route)
                <li class="border-b border-line">
                    <a href="{{ route('catalog.index', ['from' => $route['from'], 'to' => $route['to'], 'date' => $defaultDate]) }}"
                       class="group flex items-center gap-4 py-5">
                        <span class="grid size-11 shrink-0 place-items-center rounded-md bg-brand-soft text-brand-ink">
                            <x-icon name="bus" class="size-5" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="flex flex-wrap items-center gap-x-2 font-semibold text-ink">
                                {{ $route['from'] }}
                                <x-icon name="arrow-right" class="size-4 text-ink-subtle" />
                                <span class="sr-only">{{ __('to') }}</span>
                                {{ $route['to'] }}
                            </span>
                            <span class="mt-0.5 block text-sm text-ink-subtle">{{ $route['duration'] }}, {{ __(':count trips per day', ['count' => $route['trips']]) }}</span>
                        </span>
                        <span class="text-right">
                            <span class="block text-xs text-ink-subtle">{{ __('from') }}</span>
                            <x-price :amount="$route['price']" class="font-bold text-ink" />
                        </span>
                        <x-icon name="chevron-right" class="size-5 text-ink-subtle transition-transform group-hover:translate-x-0.5 group-hover:text-brand-ink" />
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- Katalog produk: kolom dipisah garis tipis, ala katalog furnitur --}}
    <section class="container-page py-8 md:py-10" aria-labelledby="produk-title">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 id="produk-title" class="text-2xl font-semibold tracking-tight text-ink md:text-3xl">{{ __('Product catalog') }}</h2>
                <p class="mt-2 max-w-[60ch] text-ink-muted">{{ __('Travel gear and trip essentials, picked by our team.') }}</p>
            </div>
            <a href="{{ route('shop') }}" class="btn rounded-full border border-line-strong text-ink transition-colors duration-200 hover:border-brand-600 hover:bg-brand-600 hover:text-white">
                {{ __('See all products') }}
                <x-icon name="arrow-right" class="size-4" />
            </a>
        </div>

        <ul class="product-grid mt-4 lg:grid-cols-4">
            @foreach ($products as $product)
                <li><x-product-card :product="$product" /></li>
            @endforeach
        </ul>
    </section>

    {{-- Girls Trip: banner foto full-width sebagai ajakan ke halaman Trip --}}
    <section class="container-page py-8 md:py-10" aria-labelledby="girls-trip-title">
        <div class="relative isolate overflow-hidden rounded-lg">
            <img src="https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=1600&q=80&auto=format&fit=crop"
                 alt="{{ __('Four friends embracing on a hilltop at sunset') }}" loading="lazy" width="1600" height="1067"
                 class="absolute inset-0 -z-10 size-full object-cover">
            <div class="absolute inset-0 -z-10 bg-gradient-to-t from-brand-950/85 via-brand-950/50 to-brand-950/10 md:bg-gradient-to-r md:from-brand-950/70 md:via-brand-950/40" aria-hidden="true"></div>
            <div class="flex min-h-72 max-w-xl flex-col justify-end px-6 py-8 sm:min-h-96 sm:px-10 sm:py-12">
                <h2 id="girls-trip-title" class="text-4xl font-semibold tracking-tight text-white md:text-5xl">{{ __('Girls Trip') }}</h2>
                <p class="mt-3 text-base text-brand-50 md:text-lg">{{ __('Plan a getaway with your best friends, seats side by side.') }}</p>
                <a href="{{ route('trip') }}" class="btn mt-6 w-fit bg-white text-brand-800 hover:bg-brand-50">
                    {{ __('Plan the trip') }}
                    <x-icon name="arrow-right" class="size-4" />
                </a>
            </div>
        </div>
    </section>

    {{-- Komunitas Behind The Route: semua konten di tengah, wordmark hitam di atas latar terang --}}
    @php
        $communityUrl = config('services.community_url');
    @endphp
    <section style="padding-bottom: 1rem;" aria-labelledby="community-title">
        <div class="flex flex-col items-center text-center">
            <img src="{{ asset('images/btr.png') }}" alt="Behind The Route" style="width: 50%; height: auto; display: block; text-align: center; align-items: center; justify-content: center;" loading="lazy"
                     class="h-auto w-[20rem] max-w-full md:w-[28rem]">
            <p class="mx-auto mt-6 max-w-prose font-light text-ink-muted">{{ __('Join Behind The Route, a community of travelers sharing stories, tips, and trips across Indonesia.') }}</p>
            <a href="{{ $communityUrl ?: route('collaboration') }}"
               @if ($communityUrl) target="_blank" rel="noopener noreferrer" @endif
               class="btn mt-7 rounded-full border border-line-strong text-ink transition-colors duration-200 hover:border-brand-600 hover:bg-brand-600 hover:text-white">
                {{ __('Join the community') }}
                @if ($communityUrl)
                    <span class="sr-only">{{ __('(opens in a new tab)') }}</span>
                @endif
                <x-icon name="arrow-right" class="size-4" />
            </a>
        </div>
    </section>

    {{-- Testimoni: slider 3 review per slide, kontrak data-attribute sama dengan hero --}}
    @if (count($testimonials) > 0)
        @php
            $reviewSlides = array_chunk($testimonials, 3);
            // Kolom mengikuti jumlah review bila < 3 agar tidak ada ruang kosong.
            $reviewCols = ['md:grid-cols-1', 'md:grid-cols-2', 'md:grid-cols-3'][min(3, count($testimonials)) - 1];
        @endphp
        <section class="container-page py-8 md:py-10" aria-labelledby="testimoni-title">
            <h2 id="testimoni-title" class="text-2xl font-semibold tracking-tight text-ink md:text-3xl">{{ __('What our passengers say') }}</h2>

            <div class="mt-8" aria-roledescription="carousel" aria-labelledby="testimoni-title" data-module="testimonial-slider">
                <div class="touch-pan-y overflow-hidden rounded-lg border border-line bg-surface select-none" data-slider-viewport>
                    <div class="slider-track" data-slider-track>
                        @foreach ($reviewSlides as $slide)
                            <div @class(['grid w-full shrink-0 divide-y divide-line md:divide-x md:divide-y-0', $reviewCols])
                                 role="group" aria-roledescription="slide"
                                 aria-label="{{ __(':current of :total', ['current' => $loop->iteration, 'total' => count($reviewSlides)]) }}" data-slide
                                 @unless ($loop->first) aria-hidden="true" inert @endunless>
                                @foreach ($slide as $testimonial)
                                    <figure class="flex flex-col justify-between gap-6 p-6 sm:p-8">
                                        <div>
                                            <div class="flex gap-0.5 text-accent-500" role="img" aria-label="{{ __('Rated :rating out of 5', ['rating' => $testimonial['rating']]) }}">
                                                @for ($star = 1; $star <= 5; $star++)
                                                    <x-icon :name="$star <= $testimonial['rating'] ? 'star-filled' : 'star'" :class="$star <= $testimonial['rating'] ? 'size-4' : 'size-4 text-line-strong'" />
                                                @endfor
                                            </div>
                                            <blockquote class="mt-4 leading-relaxed text-ink">
                                                <p>&ldquo;{{ $testimonial['text'] }}&rdquo;</p>
                                            </blockquote>
                                        </div>
                                        <figcaption class="flex items-center gap-3">
                                            <span class="grid size-10 shrink-0 place-items-center rounded-full bg-brand-soft text-sm font-semibold text-brand-ink" aria-hidden="true">
                                                {{ collect(explode(' ', $testimonial['name']))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('') }}
                                            </span>
                                            <span>
                                                <span class="block text-sm font-semibold text-ink">{{ $testimonial['name'] }}</span>
                                                <span class="block text-xs text-ink-subtle">{{ __('Passenger from :city', ['city' => $testimonial['city']]) }}</span>
                                            </span>
                                        </figcaption>
                                    </figure>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

                @if (count($reviewSlides) > 1)
                    <div class="mt-4 flex items-center justify-between gap-4">
                        <div class="flex gap-1.5">
                            @foreach ($reviewSlides as $slide)
                                <button type="button" class="h-1.5 w-6 rounded-full bg-line-strong transition-all aria-[current=true]:w-10 aria-[current=true]:bg-brand-600"
                                        data-slider-dot="{{ $loop->index }}" @if ($loop->first) aria-current="true" @endif>
                                    <span class="sr-only">{{ __('Show slide :number', ['number' => $loop->iteration]) }}</span>
                                </button>
                            @endforeach
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" class="btn-outline size-10 rounded-md p-0" data-slider-toggle
                                    data-label-play="{{ __('Play slideshow') }}" data-label-pause="{{ __('Pause slideshow') }}">
                                <x-icon name="player-pause" class="size-4" data-slider-icon="pause" />
                                <x-icon name="player-play" class="hidden size-4" data-slider-icon="play" />
                                <span class="sr-only" data-slider-toggle-label>{{ __('Pause slideshow') }}</span>
                            </button>
                            <button type="button" class="btn-outline size-10 rounded-md p-0" data-slider-prev>
                                <x-icon name="chevron-left" class="size-4" />
                                <span class="sr-only">{{ __('Previous slide') }}</span>
                            </button>
                            <button type="button" class="btn-outline size-10 rounded-md p-0" data-slider-next>
                                <x-icon name="chevron-right" class="size-4" />
                                <span class="sr-only">{{ __('Next slide') }}</span>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    @endif
    <x-product-buy-modal />
@endsection

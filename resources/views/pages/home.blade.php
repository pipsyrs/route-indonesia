@extends('layouts.app')

@section('title', 'Tiket travel antarkota')

@section('content')
    {{-- Hero: slider sebagai focal point, form pencarian menempel di bawahnya --}}
    <x-hero-slider />

    <section class="container-page relative z-10 -mt-10 sm:-mt-14" aria-labelledby="cari-title">
        <div class="card p-4 shadow-xl shadow-brand-900/5 sm:p-6">
            <h2 id="cari-title" class="sr-only">Cari jadwal travel</h2>
            <x-search-form :cities="$cities" :query="['date' => $defaultDate]" />
        </div>
    </section>

    {{-- Rute populer: daftar dua kolom, bukan kartu seragam --}}
    <section class="container-page py-14" aria-labelledby="rute-title">
        <h2 id="rute-title" class="text-2xl font-bold tracking-tight text-ink md:text-3xl">Rute populer minggu ini</h2>
        <p class="mt-2 max-w-[60ch] text-ink-muted">Langsung lihat jadwal untuk keberangkatan besok.</p>

        <ul class="mt-8 grid gap-x-10 md:grid-cols-2">
            @foreach ($popularRoutes as $route)
                <li class="border-b border-line">
                    <a href="{{ route('catalog.index', ['from' => $route['from'], 'to' => $route['to'], 'date' => $defaultDate]) }}"
                       class="group flex items-center gap-4 py-5">
                        <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-brand-soft text-brand-ink">
                            <x-icon name="bus" class="size-5" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="flex flex-wrap items-center gap-x-2 font-semibold text-ink">
                                {{ $route['from'] }}
                                <x-icon name="arrow-right" class="size-4 text-ink-subtle" />
                                <span class="sr-only">ke</span>
                                {{ $route['to'] }}
                            </span>
                            <span class="mt-0.5 block text-sm text-ink-subtle">{{ $route['duration'] }}, {{ $route['trips'] }} jadwal per hari</span>
                        </span>
                        <span class="text-right">
                            <span class="block text-xs text-ink-subtle">mulai</span>
                            <x-price :amount="$route['price']" class="font-bold text-ink" />
                        </span>
                        <x-icon name="chevron-right" class="size-5 text-ink-subtle transition-transform group-hover:translate-x-0.5 group-hover:text-brand-ink" />
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- Keunggulan: bento asimetris, satu sel besar berisi pratinjau denah kursi --}}
    <section class="container-page py-16" aria-labelledby="keunggulan-title">
        <h2 id="keunggulan-title" class="max-w-xl text-2xl font-bold tracking-tight text-ink md:text-3xl">Kenapa pesan di RouteIndonesia</h2>

        @php
            [$seatFeature, $otherFeatures] = [$features[1], [$features[0], $features[2], $features[3]]];
            $tileStyles = ['bg-surface border border-line', 'bg-brand-soft', 'bg-surface-muted'];
        @endphp

        <div class="mt-8 grid gap-4 md:grid-cols-2 lg:grid-cols-3 lg:grid-rows-2">
            <article class="flex flex-col justify-between gap-8 rounded-2xl bg-brand-600 p-6 text-white md:row-span-3 lg:row-span-2">
                <div>
                    <x-icon :name="$seatFeature['icon']" class="size-7" />
                    <h3 class="mt-4 text-xl font-bold">{{ $seatFeature['title'] }}</h3>
                    <p class="mt-2 max-w-[40ch] text-sm leading-relaxed text-brand-50">{{ $seatFeature['desc'] }}</p>
                </div>
                <div class="grid w-fit grid-cols-4 gap-2 rounded-xl bg-white/10 p-3" aria-hidden="true">
                    @foreach ([0, 0, 0, 1, 1, 0, 2, 1, 1, 0, 1, 2, 1, 1, 1, 1] as $state)
                        <span @class([
                            'size-8 rounded-lg',
                            'invisible' => $state === 0,
                            'border border-white/50' => $state === 1,
                            'bg-white' => $state === 2,
                        ])></span>
                    @endforeach
                </div>
            </article>

            @foreach ($otherFeatures as $index => $feature)
                <article @class(['rounded-2xl p-6', $tileStyles[$index], 'lg:col-span-2' => $index === 0])>
                    <x-icon :name="$feature['icon']" class="size-6 text-brand-ink" />
                    <h3 class="mt-4 font-bold text-ink">{{ $feature['title'] }}</h3>
                    <p class="mt-1.5 max-w-[48ch] text-sm leading-relaxed text-ink-muted">{{ $feature['desc'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    {{-- Testimoni: satu kutipan utama + dua pendukung --}}
    <section class="container-page py-14" aria-labelledby="testimoni-title">
        <h2 id="testimoni-title" class="text-2xl font-bold tracking-tight text-ink md:text-3xl">Kata penumpang kami</h2>

        <div class="mt-8 grid gap-4 lg:grid-cols-[1.3fr_1fr]">
            @foreach ($testimonials as $testimonial)
                <figure @class([
                    'flex flex-col justify-between rounded-2xl border border-line bg-surface p-6',
                    'lg:row-span-2 lg:p-8' => $loop->first,
                ])>
                    <div>
                        <div class="flex gap-0.5 text-accent-500" aria-label="Rating {{ $testimonial['rating'] }} dari 5">
                            @for ($star = 1; $star <= 5; $star++)
                                <x-icon :name="$star <= $testimonial['rating'] ? 'star-filled' : 'star'" :class="$star <= $testimonial['rating'] ? 'size-4' : 'size-4 text-line-strong'" />
                            @endfor
                        </div>
                        <blockquote @class([
                            'mt-4 text-ink',
                            'text-xl leading-snug font-semibold lg:text-2xl' => $loop->first,
                            'leading-relaxed' => ! $loop->first,
                        ])>
                            <p>&ldquo;{{ $testimonial['text'] }}&rdquo;</p>
                        </blockquote>
                    </div>
                    <figcaption class="mt-6 flex items-center gap-3">
                        <span class="grid size-10 place-items-center rounded-full bg-brand-soft text-sm font-bold text-brand-ink" aria-hidden="true">
                            {{ collect(explode(' ', $testimonial['name']))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('') }}
                        </span>
                        <span>
                            <span class="block text-sm font-semibold text-ink">{{ $testimonial['name'] }}</span>
                            <span class="block text-xs text-ink-subtle">Penumpang dari {{ $testimonial['city'] }}</span>
                        </span>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </section>

    {{-- CTA sekunder: cek status pesanan --}}
    <section class="container-page pt-6">
        <div class="flex flex-col gap-6 rounded-2xl bg-surface-muted p-6 sm:flex-row sm:items-center sm:justify-between sm:p-8">
            <div class="flex items-start gap-4">
                <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-surface text-brand-ink">
                    <x-icon name="ticket" class="size-6" />
                </span>
                <div>
                    <h2 class="text-lg font-bold text-ink">Sudah memesan tiket?</h2>
                    <p class="mt-1 text-sm text-ink-muted">Lihat status pembayaran dan detail perjalanan dengan kode booking Anda.</p>
                </div>
            </div>
            <a href="{{ route('order.check') }}" class="btn-outline shrink-0">Cek pesanan</a>
        </div>
    </section>
@endsection

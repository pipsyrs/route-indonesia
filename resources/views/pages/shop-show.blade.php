@extends('layouts.app')

@section('title', $product['name'])

@section('content')
    <div class="container-page pt-8">
        <a href="{{ route('shop') }}" class="inline-flex items-center gap-1.5 text-sm text-ink-muted hover:text-brand-ink">
            <x-icon name="arrow-left" class="size-4" />
            {{ __('Back to Shop') }}
        </a>
    </div>

    {{-- Detail: gambar jadi focal point, info di kanan dengan banyak ruang kosong --}}
    <article class="container-page grid gap-10 py-8 md:grid-cols-2 md:gap-16 md:py-12 lg:gap-24">
        <div class="relative aspect-[4/5] overflow-hidden rounded-md bg-surface-muted">
            @if (! empty($product['image']))
                <img src="{{ asset($product['image']) }}" alt="{{ $product['name'] }}" class="size-full object-cover">
            @else
                <x-icon name="shopping-cart" class="absolute inset-0 m-auto size-12 text-line-strong" />
            @endif
        </div>

        <div class="flex flex-col md:py-6">
            <p class="text-xs tracking-[0.18em] text-ink-subtle uppercase">
                {{ collect([$product['brand'] ?? null, $product['category'] ?? null])->filter()->implode(' · ') }}
            </p>
            <h1 class="mt-4 text-3xl leading-tight font-light tracking-tight text-balance text-ink md:text-5xl">{{ $product['name'] }}</h1>
            <x-price :amount="$product['price']" class="mt-5 text-xl font-medium text-ink tabular-nums" />

            <hr class="my-8 border-line">

            @if (! empty($product['description']))
                <p class="max-w-[52ch] leading-relaxed text-ink-muted">{{ $product['description'] }}</p>
            @endif

            <div class="mt-10 flex flex-wrap items-center gap-4">
                <button type="button" class="btn-primary px-10 tracking-[0.14em] uppercase"
                        data-modal-open="product-buy-modal"
                        data-product-slug="{{ $product['slug'] }}"
                        data-product-name="{{ $product['name'] }}"
                        data-product-price="{{ (int) $product['price'] }}"
                        data-product-image="{{ ! empty($product['image']) ? asset($product['image']) : '' }}">
                    <x-icon name="shopping-cart" class="size-4" />
                    {{ __('Buy') }}
                </button>
                <a href="{{ route('shop') }}" class="text-sm font-medium text-ink-muted underline-offset-4 hover:text-brand-ink hover:underline">{{ __('Continue shopping') }}</a>
            </div>
        </div>
    </article>

    @if (count($related) > 0)
        <section class="container-page border-t border-line pt-10 md:pt-14" aria-labelledby="related-title">
            <h2 id="related-title" class="text-2xl font-light tracking-tight text-ink">{{ __('You may also like') }}</h2>
            <ul class="product-grid mt-4 lg:grid-cols-4">
                @foreach ($related as $item)
                    <li><x-product-card :product="$item" /></li>
                @endforeach
            </ul>
        </section>
    @endif
    <x-product-buy-modal />
@endsection

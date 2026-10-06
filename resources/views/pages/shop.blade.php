@extends('layouts.app')

@section('title', __('Shop'))

@section('content')
    <section class="container-page pt-12 pb-8">
        <p class="text-xs font-semibold tracking-[0.18em] text-brand-500 uppercase">{{ __('Shop') }}</p>
        <h1 class="mt-3 max-w-[22ch] text-3xl font-semibold tracking-tight text-balance text-ink md:text-5xl">{{ __('Everything you need for the road.') }}</h1>
        <p class="mt-4 max-w-[60ch] text-ink-muted">{{ __('Travel gear and trip essentials, picked by our team.') }}</p>
    </section>

    <section class="container-page" aria-label="{{ __('Product catalog') }}">
        @if (count($products) === 0)
            <p class="border-y border-line py-12 text-center text-ink-muted">{{ __('No products yet. Check back soon.') }}</p>
        @else
            <h2 class="sr-only">{{ __('Product catalog') }}</h2>
            <ul class="product-grid md:grid-cols-3 xl:grid-cols-4">
                @foreach ($products as $product)
                    <li><x-product-card :product="$product" /></li>
                @endforeach
            </ul>
        @endif
    </section>
    <x-product-buy-modal />
@endsection

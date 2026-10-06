@props(['product'])

{{-- Kartu produk minimal: gambar 4:5, merek, nama + harga sebaris, tombol BUY membuka modal --}}
<article {{ $attributes->merge(['class' => 'flex h-full flex-col px-3 sm:px-5']) }}>
    <a href="{{ route('shop.show', $product['slug']) }}" class="group block flex-1">
        <div class="relative aspect-[4/5] overflow-hidden rounded-md bg-surface-muted">
            @if (! empty($product['image']))
                <img src="{{ asset($product['image']) }}" alt="{{ $product['name'] }}" loading="lazy"
                     class="size-full object-cover transition-transform duration-500 group-hover:scale-[1.03]">
            @else
                <x-icon name="shopping-cart" class="absolute inset-0 m-auto size-8 text-line-strong" />
            @endif
        </div>
        @if (! empty($product['brand']))
            <p class="mt-4 text-xs text-ink-subtle">{{ $product['brand'] }}</p>
        @endif
        <div @class(['flex flex-wrap items-baseline justify-between gap-x-3 gap-y-0.5', 'mt-1' => ! empty($product['brand']), 'mt-4' => empty($product['brand'])])>
            <h3 class="font-medium text-ink group-hover:text-brand-ink">{{ $product['name'] }}</h3>
            <x-price :amount="$product['price']" class="shrink-0 text-sm text-ink tabular-nums" />
        </div>
    </a>

    <button type="button" class="btn-outline mt-4 w-full py-2 text-xs tracking-[0.14em] uppercase"
            data-modal-open="product-buy-modal"
            data-product-slug="{{ $product['slug'] }}"
            data-product-name="{{ $product['name'] }}"
            data-product-price="{{ (int) $product['price'] }}"
            data-product-image="{{ ! empty($product['image']) ? asset($product['image']) : '' }}">
        {{ __('Buy') }}
        <span class="sr-only">{{ $product['name'] }}</span>
    </button>
</article>

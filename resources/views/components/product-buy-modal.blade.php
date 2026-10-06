{{-- Satu instance per halaman; nama & harga diisi modules/product-buy.js dari tombol BUY --}}
<x-modal id="product-buy-modal" :title="__('Add to cart')" data-module="product-buy"
         data-msg-added="{{ __(':name added to cart') }}"
         data-msg-partial="{{ __('Only :count added (max 10)') }}"
         data-msg-max="{{ __('You already have the maximum quantity of this product in your cart.') }}"
         data-msg-invalid="{{ __('Enter a quantity between 1 and 10.') }}">
    <form id="product-buy-form" method="dialog" novalidate>
        <div class="flex items-baseline justify-between gap-4">
            <p class="font-medium text-ink" data-product-name></p>
            <p class="shrink-0 text-sm text-ink-muted tabular-nums" data-product-price></p>
        </div>

        <label for="product-buy-qty" class="form-label mt-6">{{ __('Quantity') }}</label>
        <div class="flex w-fit items-center rounded-md border border-line-strong">
            <button type="button" class="grid size-10 place-items-center text-ink-muted hover:text-ink disabled:opacity-40" data-qty-step="-1">
                <x-icon name="minus" class="size-4" /><span class="sr-only">{{ __('Decrease') }}</span>
            </button>
            <input id="product-buy-qty" type="number" inputmode="numeric" min="1" max="10" step="1" value="1" autofocus
                   aria-describedby="product-buy-qty-error"
                   class="w-12 border-0 bg-transparent text-center text-sm font-semibold tabular-nums text-ink [appearance:textfield] focus:outline-none [&::-webkit-inner-spin-button]:appearance-none"
                   data-qty-input>
            <button type="button" class="grid size-10 place-items-center text-ink-muted hover:text-ink disabled:opacity-40" data-qty-step="1">
                <x-icon name="plus" class="size-4" /><span class="sr-only">{{ __('Increase') }}</span>
            </button>
        </div>
        <p id="product-buy-qty-error" class="form-error" data-qty-error hidden></p>
    </form>

    <x-slot:footer>
        <button type="button" class="btn-outline" data-modal-close>{{ __('Cancel') }}</button>
        <button type="submit" form="product-buy-form" class="btn-primary">
            <x-icon name="shopping-cart" class="size-4" />
            {{ __('Add to cart') }}
        </button>
    </x-slot:footer>
</x-modal>

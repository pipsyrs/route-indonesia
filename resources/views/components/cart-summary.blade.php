@props(['serviceFee' => 0, 'title' => 'Ringkasan belanja'])
{{-- Ringkasan multi-item; angka diisi JS dari ri_cart lewat data-summary-* --}}
<section {{ $attributes->merge(['class' => 'card p-5 sm:p-6']) }} aria-labelledby="ringkasan-keranjang-title">
    <h2 id="ringkasan-keranjang-title" class="text-base font-bold text-ink">{{ $title }}</h2>

    <ul class="mt-4 space-y-3 text-sm" data-summary-items></ul>

    <dl class="mt-4 space-y-3 border-t border-dashed border-line-strong pt-4 text-sm">
        <div class="flex justify-between gap-4">
            <dt class="text-ink-muted">Tiket (<span data-summary-count>0</span>)</dt>
            <dd class="text-ink" data-summary-subtotal>Rp 0</dd>
        </div>
        <div class="flex justify-between gap-4">
            <dt class="text-ink-muted">Biaya layanan</dt>
            <dd class="text-ink" data-summary-fee data-fee="{{ (int) $serviceFee }}"><x-price :amount="$serviceFee" /></dd>
        </div>
    </dl>

    <div class="mt-4 flex items-baseline justify-between gap-4 border-t border-line pt-4">
        <span class="text-sm font-semibold text-ink">Total bayar</span>
        <span class="text-xl font-bold text-brand-ink" data-summary-total aria-live="polite">Rp 0</span>
    </div>

    {{ $slot }}
</section>

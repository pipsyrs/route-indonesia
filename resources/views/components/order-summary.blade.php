@props([
    'trip',
    'dateLabel',
    'seats' => [],
    'pickup' => null,
    'dropoff' => null,
    'subtotal' => 0,
    'serviceFee' => 0,
    'total' => 0,
])
{{--
    Ringkasan pesanan. Elemen ber-atribut data-summary-* diperbarui oleh JS
    (pilih kursi) sehingga markup tetap satu sumber.
--}}
<section {{ $attributes->merge(['class' => 'card p-5 sm:p-6']) }} aria-labelledby="ringkasan-title">
    <h2 id="ringkasan-title" class="text-base font-bold text-ink">Ringkasan pesanan</h2>

    <div class="mt-4 rounded-xl bg-surface-muted p-4">
        <p class="flex items-center gap-2 font-semibold text-ink">
            {{ $trip['origin'] }}
            <x-icon name="arrow-right" class="size-4 text-ink-subtle" />
            <span class="sr-only">ke</span>
            {{ $trip['destination'] }}
        </p>
        <p class="mt-1 text-sm text-ink-muted">{{ $dateLabel }}</p>
        <p class="mt-1 text-sm text-ink-muted">{{ $trip['depart'] }} - {{ $trip['arrive'] }}, {{ $trip['operator'] }}</p>
    </div>

    <dl class="mt-4 space-y-3 text-sm">
        <div class="flex justify-between gap-4">
            <dt class="text-ink-muted">Kursi</dt>
            <dd class="text-right font-semibold text-ink" data-summary-seats>{{ $seats ? implode(', ', $seats) : 'Belum dipilih' }}</dd>
        </div>
        <div class="flex justify-between gap-4">
            <dt class="text-ink-muted">Jemput</dt>
            <dd class="text-right text-ink" data-summary-pickup>{{ $pickup ? $pickup['name'].' ('.$pickup['time'].')' : '-' }}</dd>
        </div>
        <div class="flex justify-between gap-4">
            <dt class="text-ink-muted">Antar</dt>
            <dd class="text-right text-ink" data-summary-dropoff>{{ $dropoff ? $dropoff['name'].' ('.$dropoff['time'].')' : '-' }}</dd>
        </div>
    </dl>

    <dl class="mt-4 space-y-3 border-t border-dashed border-line-strong pt-4 text-sm">
        <div class="flex justify-between gap-4">
            <dt class="text-ink-muted">
                Tiket <span data-summary-seat-count>{{ count($seats) }}</span> &times; <x-price :amount="$trip['price']" />
            </dt>
            <dd class="text-ink"><x-price :amount="$subtotal" data-summary-subtotal /></dd>
        </div>
        <div class="flex justify-between gap-4">
            <dt class="text-ink-muted">Biaya layanan</dt>
            <dd class="text-ink"><x-price :amount="$serviceFee" /></dd>
        </div>
    </dl>

    <div class="mt-4 flex items-baseline justify-between gap-4 border-t border-line pt-4">
        <span class="text-sm font-semibold text-ink">Total bayar</span>
        <x-price :amount="$total" class="text-xl font-bold text-brand-ink" data-summary-total data-base-total="{{ $total }}" />
    </div>

    {{ $slot }}
</section>

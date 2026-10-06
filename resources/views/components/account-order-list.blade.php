@props(['orders'])
@php
    $statusLabels = [
        'pending' => ['label' => 'Menunggu bayar', 'class' => 'bg-warn-soft text-warn-ink'],
        'paid' => ['label' => 'Lunas', 'class' => 'bg-brand-soft text-brand-ink'],
        'completed' => ['label' => 'Selesai', 'class' => 'bg-ok-soft text-ok-ink'],
        'cancelled' => ['label' => 'Dibatalkan', 'class' => 'bg-danger-soft text-danger-ink'],
    ];
@endphp

@if ($orders === [])
    <div class="flex flex-col items-center rounded-lg border border-dashed border-line-strong px-6 py-12 text-center">
        <x-icon name="ticket" class="size-7 text-ink-subtle" />
        <p class="mt-3 font-semibold text-ink">Belum ada pesanan</p>
        <a href="{{ route('catalog.index') }}" class="btn-primary mt-5">Jelajahi katalog</a>
    </div>
@else
    <ul {{ $attributes->merge(['class' => 'divide-y divide-line rounded-lg border border-line bg-surface']) }}>
        @foreach ($orders as $order)
            @php $status = $statusLabels[$order['status']] ?? ['label' => $order['status'], 'class' => 'bg-surface-muted text-ink-muted']; @endphp
            <li class="grid gap-3 p-4 sm:grid-cols-[1fr_auto] sm:items-center sm:p-5">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="font-semibold text-ink">{{ $order['origin'] }} &rarr; {{ $order['destination'] }}</p>
                        <span class="chip {{ $status['class'] }}">{{ $status['label'] }}</span>
                    </div>
                    <p class="mt-1 text-sm text-ink-muted">
                        {{ \Illuminate\Support\Carbon::parse($order['date'])->translatedFormat('j M Y') }}, {{ $order['depart'] }}
                        &middot; {{ $order['operator'] }} &middot; {{ $order['qty'] }} tiket
                    </p>
                    <p class="mt-0.5 font-mono text-xs text-ink-subtle">{{ $order['code'] }} &middot; {{ $order['method'] }}</p>
                </div>
                <x-price :amount="$order['total']" class="font-bold text-ink sm:text-right" />
            </li>
        @endforeach
    </ul>
@endif

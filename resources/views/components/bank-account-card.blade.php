@props(['bank', 'number', 'holder'])
@php
    $digits = preg_replace('/\D/', '', $number);
    // Kelompok 4 digit agar mudah dibaca; nilai salin tetap tanpa spasi.
    $display = $digits !== '' ? trim(chunk_split($digits, 4, ' ')) : $number;
@endphp
<li {{ $attributes->merge(['class' => 'rounded-md border border-line bg-surface p-4']) }}>
    <div class="flex items-center gap-3">
        <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-brand-soft text-xs font-extrabold tracking-wide text-brand-ink" aria-hidden="true">
            {{ mb_strtoupper(mb_substr($bank, 0, 4)) }}
        </span>
        <div class="min-w-0">
            <p class="font-semibold text-ink">{{ $bank }}</p>
            <p class="truncate text-xs text-ink-muted">a.n. {{ $holder }}</p>
        </div>
    </div>
    <div class="mt-3 flex flex-wrap items-center justify-between gap-3 rounded-md bg-surface-muted px-3 py-2.5">
        <p class="font-mono text-lg font-bold tracking-wider text-ink tabular-nums">
            <span class="sr-only">Nomor rekening {{ $bank }}: </span>{{ $display }}
        </p>
        <button type="button" class="btn-outline px-3 py-1.5 text-xs" data-copy="{{ $digits !== '' ? $digits : $number }}"
                data-copy-message="Nomor rekening {{ $bank }} disalin" aria-label="Salin nomor rekening {{ $bank }}">
            <x-icon name="copy" class="size-4" />
            Salin
        </button>
    </div>
</li>

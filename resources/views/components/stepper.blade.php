@props(['current' => 1])
@php
    $steps = ['Keranjang', 'Checkout', 'Pembayaran', 'Selesai'];
@endphp
<nav aria-label="Langkah pemesanan" {{ $attributes->merge(['class' => 'print:hidden']) }}>
    <ol class="flex items-center gap-2 overflow-x-auto pb-1 text-sm">
        @foreach ($steps as $index => $label)
            @php
                $number = $index + 1;
                $isDone = $number < $current;
                $isCurrent = $number === $current;
            @endphp
            <li class="flex shrink-0 items-center gap-2" @if ($isCurrent) aria-current="step" @endif>
                <span @class([
                    'grid size-7 place-items-center rounded-full text-xs font-bold',
                    'bg-brand-600 text-white' => $isCurrent,
                    'bg-ok-soft text-ok-ink' => $isDone,
                    'bg-surface-muted text-ink-subtle' => ! $isCurrent && ! $isDone,
                ])>
                    @if ($isDone)
                        <x-icon name="check" class="size-4" />
                    @else
                        {{ $number }}
                    @endif
                </span>
                <span @class([
                    'font-semibold text-ink' => $isCurrent,
                    'text-ink-muted' => ! $isCurrent,
                    'hidden sm:inline' => ! $isCurrent,
                ])>
                    {{ $label }}
                    @if ($isDone)<span class="sr-only">(selesai)</span>@endif
                </span>
                @unless ($loop->last)
                    <span class="h-px w-6 bg-line-strong sm:w-10" aria-hidden="true"></span>
                @endunless
            </li>
        @endforeach
    </ol>
</nav>

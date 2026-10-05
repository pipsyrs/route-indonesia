@props(['value'])
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 text-sm font-semibold text-ink']) }}>
    <x-icon name="star-filled" class="size-4 text-accent-500" />
    {{ number_format((float) $value, 1, ',', '.') }}
    <span class="sr-only">dari 5</span>
</span>

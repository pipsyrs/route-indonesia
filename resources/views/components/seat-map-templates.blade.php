{{-- Sel denah untuk utils/seat-map.js; kelas di Blade agar terbaca Tailwind --}}
<template data-seat-driver-template>
    <span class="grid aspect-square w-full place-items-center rounded-md bg-surface-muted text-ink-subtle" title="Sopir">
        <x-icon name="steering-wheel" class="size-5" /><span class="sr-only">Sopir</span>
    </span>
</template>
<template data-seat-aisle-template>
    <span class="aspect-square w-full" aria-hidden="true"></span>
</template>
<template data-seat-cell-template>
    <label class="relative cursor-pointer has-disabled:cursor-not-allowed">
        <input type="checkbox" class="peer sr-only" data-seat>
        <span class="grid aspect-square w-full place-items-center rounded-md border border-line-strong bg-seat-available text-sm font-semibold tabular-nums text-ink transition peer-enabled:hover:border-brand-500 peer-checked:border-seat-selected peer-checked:bg-seat-selected peer-checked:text-white peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand-500 peer-disabled:border-transparent peer-disabled:bg-seat-occupied peer-disabled:text-ink-subtle peer-disabled:line-through motion-safe:active:scale-95" data-seat-label></span>
    </label>
</template>

@props(['code', 'label'])
@php
    $icons = ['ac' => 'snowflake', 'wifi' => 'wifi', 'usb' => 'usb', 'charger' => 'usb', 'blanket' => 'moon', 'snack' => 'cookie', 'reclining' => 'armchair-2', 'toilet' => 'toilet-paper'];
@endphp
<span {{ $attributes->merge(['class' => 'chip bg-surface-muted text-ink-muted']) }}>
    <x-icon :name="$icons[$code] ?? 'check'" class="size-3.5" />
    {{ $label }}
</span>

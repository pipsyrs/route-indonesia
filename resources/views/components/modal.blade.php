@props(['id', 'title'])

{{-- Modal generik: buka dengan data-modal-open="{{ id }}", tutup dengan data-modal-close / Esc / klik backdrop (modules/modal.js) --}}
<dialog id="{{ $id }}" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title"
        {{ $attributes->merge(['class' => 'm-auto w-[calc(100%-2rem)] max-w-md rounded-md border border-line bg-surface p-0 text-ink shadow-xl backdrop:bg-ink/50 backdrop:backdrop-blur-[2px]']) }}>
    <div class="flex items-center justify-between gap-4 border-b border-line px-5 py-4">
        <h2 id="{{ $id }}-title" class="text-base font-semibold">{{ $title }}</h2>
        <button type="button" class="btn-ghost -mr-2 size-9 p-0" data-modal-close>
            <x-icon name="x" class="size-5" />
            <span class="sr-only">{{ __('Close') }}</span>
        </button>
    </div>

    <div class="px-5 py-5">{{ $slot }}</div>

    @isset($footer)
        <div class="flex flex-wrap justify-end gap-3 border-t border-line px-5 py-4">{{ $footer }}</div>
    @endisset
</dialog>

@props(['product'])

@php
    $variants = $product['model3d']['variants'] ?? [];
    $attribution = $product['model3d']['attribution'] ?? null;
    $control = 'grid size-9 place-items-center rounded-full text-ink-muted transition hover:bg-surface-muted hover:text-ink';
@endphp

{{-- Viewer 3D "Coba Virtual": buka/tutup lewat modules/modal.js; model-viewer di-lazy-load modules/product-viewer.js --}}
<dialog id="product-viewer-modal" role="dialog" aria-modal="true" aria-labelledby="product-viewer-title" aria-describedby="product-viewer-hint"
        class="m-auto h-[calc(100dvh-1rem)] max-h-none w-[calc(100%-1rem)] max-w-6xl overflow-hidden rounded-lg border border-line bg-surface p-0 text-ink shadow-2xl backdrop:bg-ink/60 backdrop:backdrop-blur-[2px] sm:h-[calc(100dvh-4rem)] sm:w-[calc(100%-4rem)]"
        data-module="product-viewer"
        data-msg-fullscreen="{{ __('Fullscreen') }}"
        data-msg-exit-fullscreen="{{ __('Exit fullscreen') }}"
        data-msg-ar-failed="{{ __('AR could not be started on this device.') }}">
    <div class="flex size-full flex-col bg-surface" data-viewer-shell>
        <header class="flex items-start justify-between gap-3 px-4 pt-4 pb-3 sm:px-6 sm:pt-5">
            <div class="min-w-0">
                <h2 id="product-viewer-title" class="line-clamp-2 text-base leading-snug font-semibold text-ink sm:truncate sm:text-lg">{{ __(':name — 3D View', ['name' => $product['name']]) }}</h2>
                <p id="product-viewer-hint" class="mt-0.5 text-xs text-ink-subtle">
                    {{-- Petunjuk per jenis pointer; elemen tersembunyi tidak ikut dibaca aria-describedby --}}
                    <span class="pointer-coarse:hidden">{{ __('Drag to rotate · Scroll to zoom') }}</span>
                    <span class="hidden pointer-coarse:inline">{{ __('Swipe to rotate · Pinch to zoom') }}</span>
                </p>
            </div>

            <div class="flex shrink-0 items-center gap-1 sm:gap-2">
                <div class="flex items-center rounded-full bg-surface-muted p-1" role="group" aria-label="{{ __('View mode') }}">
                    <button type="button" class="inline-flex h-8 items-center gap-1.5 rounded-full bg-surface px-3 text-xs font-semibold text-ink shadow-sm" aria-pressed="true">
                        <x-icon name="cube" class="size-4" />3D
                    </button>
                    <span class="group relative">
                        <button type="button" class="inline-flex h-8 items-center gap-1.5 rounded-full px-3 text-xs font-semibold text-ink-muted transition hover:text-ink aria-disabled:cursor-not-allowed aria-disabled:opacity-45 aria-disabled:hover:text-ink-muted"
                                aria-pressed="false" aria-disabled="true" aria-describedby="product-viewer-ar-tip" data-viewer-ar>
                            <x-icon name="augmented-reality" class="size-4" />AR
                        </button>
                        <span id="product-viewer-ar-tip" role="tooltip" data-viewer-ar-tip
                              class="pointer-events-none absolute top-full right-0 z-10 mt-2 rounded-md bg-ink px-2.5 py-1.5 text-xs whitespace-nowrap text-canvas opacity-0 shadow-lg transition group-focus-within:opacity-100 group-hover:opacity-100">
                            {{ __('AR is available on phones') }}
                        </span>
                    </span>
                </div>

                <button type="button" class="btn-ghost size-9 p-0" aria-pressed="false" data-viewer-fullscreen hidden>
                    <x-icon name="maximize" class="size-5" data-icon-enter />
                    <x-icon name="minimize" class="size-5" data-icon-exit hidden />
                    <span class="sr-only" data-label>{{ __('Fullscreen') }}</span>
                </button>
                <button type="button" class="btn-ghost -mr-2 size-9 p-0" data-modal-close>
                    <x-icon name="x" class="size-5" />
                    <span class="sr-only">{{ __('Close') }}</span>
                </button>
            </div>
        </header>

        {{-- Area viewer: focal point modal --}}
        <div class="relative mx-2 min-h-0 flex-1 overflow-hidden rounded-md bg-surface-muted sm:mx-6">
            <model-viewer class="block size-full bg-transparent" data-viewer
                          alt="{{ $product['name'] }}"
                          camera-controls touch-action="none"
                          camera-orbit="30deg 75deg 105%"
                          ar ar-modes="webxr scene-viewer quick-look" ar-scale="auto"
                          shadow-intensity="1" environment-image="neutral"
                          loading="eager" reveal="auto">
                {{-- Slot kosong: sembunyikan progress bar & tombol AR bawaan --}}
                <span slot="progress-bar"></span>
                <span slot="ar-button" hidden></span>
            </model-viewer>

            <div class="pointer-events-none absolute inset-x-0 top-0 h-0.5 bg-brand-600/10" data-viewer-progress hidden>
                <div class="h-full origin-left bg-brand-600 transition-transform duration-200" style="transform: scaleX(0)" data-viewer-progress-bar></div>
            </div>
            <p class="pointer-events-none absolute top-3 left-1/2 -translate-x-1/2 rounded-full bg-surface/90 px-3 py-1 text-xs font-medium whitespace-nowrap text-ink-muted shadow-sm"
               role="status" data-viewer-status hidden>
                {{ __('Loading 3D model…') }} <span class="tabular-nums" aria-hidden="true" data-viewer-percent>0%</span>
            </p>
            <p class="absolute top-3 left-1/2 max-w-[90%] -translate-x-1/2 rounded-md bg-ink px-3 py-1.5 text-center text-xs font-medium text-canvas shadow-lg"
               role="alert" data-viewer-notice hidden></p>

            <div class="absolute inset-0 grid place-items-center bg-surface-muted p-6 text-center" data-viewer-error hidden>
                <div class="max-w-xs">
                    <x-icon name="alert-circle" class="mx-auto size-8 text-danger-ink" />
                    <p class="mt-3 text-sm font-medium text-ink">{{ __('The 3D model failed to load.') }}</p>
                    <p class="mt-1 text-xs text-ink-subtle">{{ __('Check your connection, then try again.') }}</p>
                    <button type="button" class="btn-outline mt-4" data-viewer-retry>
                        <x-icon name="refresh" class="size-4" />{{ __('Try again') }}
                    </button>
                </div>
            </div>

            <button type="button" class="absolute bottom-3 left-3 grid size-10 place-items-center rounded-full border border-line bg-surface/90 text-ink-muted shadow-sm backdrop-blur transition hover:text-ink"
                    data-viewer-reset>
                <x-icon name="refresh" class="size-4" />
                <span class="sr-only">{{ __('Reset view') }}</span>
            </button>
            <div class="absolute right-3 bottom-3 flex items-center gap-0.5 rounded-full border border-line bg-surface/90 p-0.5 shadow-sm backdrop-blur"
                 role="group" aria-label="{{ __('Camera controls') }}">
                <button type="button" class="{{ $control }}" data-viewer-rotate="-1">
                    <x-icon name="rotate-ccw" class="size-4" /><span class="sr-only">{{ __('Rotate left') }}</span>
                </button>
                <button type="button" class="{{ $control }}" data-viewer-zoom="in">
                    <x-icon name="zoom-in" class="size-4" /><span class="sr-only">{{ __('Zoom in') }}</span>
                </button>
                <button type="button" class="{{ $control }}" data-viewer-zoom="out">
                    <x-icon name="zoom-out" class="size-4" /><span class="sr-only">{{ __('Zoom out') }}</span>
                </button>
                <button type="button" class="{{ $control }}" data-viewer-rotate="1">
                    <x-icon name="rotate-cw" class="size-4" /><span class="sr-only">{{ __('Rotate right') }}</span>
                </button>
            </div>
        </div>

        <div class="px-4 pt-3 pb-4 sm:px-6 sm:pb-5">
            <div class="flex items-baseline justify-between gap-4">
                <p id="product-viewer-variants" class="text-[11px] font-semibold tracking-[0.18em] text-ink-subtle uppercase">{{ __('Choose variant') }}</p>
                @if ($attribution)
                    <p class="truncate text-[10px] text-ink-subtle" title="{{ $attribution }}">{{ __('3D model') }}: {{ $attribution }}</p>
                @endif
            </div>
            <div class="-mx-4 mt-2.5 flex snap-x gap-3 overflow-x-auto px-4 pb-1 sm:mx-0 sm:px-0" role="group" aria-labelledby="product-viewer-variants">
                @foreach ($variants as $variant)
                    <button type="button" class="group w-20 shrink-0 snap-start text-left sm:w-24"
                            aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                            data-viewer-variant
                            data-src="{{ asset($variant['src']) }}"
                            data-poster="{{ ! empty($variant['thumbnail']) ? asset($variant['thumbnail']) : '' }}"
                            data-alt="{{ $product['name'] }} — {{ $variant['name'] }}">
                        <span class="block aspect-square overflow-hidden rounded-md border-2 border-line bg-surface-muted transition group-hover:border-line-strong group-aria-pressed:border-ink">
                            @if (! empty($variant['thumbnail']))
                                <img src="{{ asset($variant['thumbnail']) }}" alt="" loading="lazy" class="size-full object-contain p-1.5">
                            @else
                                <x-icon name="cube" class="m-auto size-full p-6 text-line-strong" />
                            @endif
                        </span>
                        <span class="mt-1.5 block truncate text-xs text-ink-muted group-aria-pressed:font-semibold group-aria-pressed:text-ink">{{ $variant['name'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</dialog>

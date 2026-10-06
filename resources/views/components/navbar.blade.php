@php
    $links = [
        ['label' => __('Home'), 'href' => route('home'), 'active' => request()->routeIs('home')],
        ['label' => __('Shop'), 'href' => route('shop'), 'active' => request()->routeIs('shop')],
        ['label' => __('Trip'), 'href' => route('trip'), 'active' => request()->routeIs('trip')],
        ['label' => __('Collaboration'), 'href' => route('collaboration'), 'active' => request()->routeIs('collaboration')],
        ['label' => __('Careers'), 'href' => route('career'), 'active' => request()->routeIs('career')],
    ];
    $locales = ['en' => 'English', 'id' => 'Bahasa Indonesia'];
    $currentLocale = app()->getLocale();
@endphp

<header class="sticky top-0 z-30 border-b border-line bg-canvas/85 backdrop-blur-md print:hidden" data-module="nav">
    <nav class="container-page flex h-16 items-center justify-between gap-4" aria-label="{{ __('Main navigation') }}">
        <x-logo />

        <ul class="hidden flex-1 items-center gap-1 md:flex">
            @foreach ($links as $link)
                <li>
                    <a href="{{ $link['href'] }}"
                       @class([
                           'rounded-md px-3.5 py-2 text-sm font-medium transition-colors',
                           'bg-brand-soft text-brand-ink' => $link['active'],
                           'text-ink-muted hover:bg-surface-muted hover:text-ink' => ! $link['active'],
                       ])
                       @if ($link['active']) aria-current="page" @endif>{{ $link['label'] }}</a>
                </li>
            @endforeach
        </ul>

        <div class="flex items-center gap-1">
            {{-- Pilihan bahasa; nama bahasa ditulis dalam bahasanya sendiri --}}
            <ul class="mr-1 flex items-center rounded-md border border-line p-0.5 text-xs font-semibold" aria-label="{{ __('Language') }}">
                @foreach ($locales as $code => $name)
                    <li>
                        <a href="{{ route('locale.switch', $code) }}" lang="{{ $code }}" hreflang="{{ $code }}"
                           @class([
                               'block rounded px-2 py-1 uppercase tracking-wide transition-colors',
                               'bg-brand-600 text-white' => $code === $currentLocale,
                               'text-ink-muted hover:text-ink' => $code !== $currentLocale,
                           ])
                           @if ($code === $currentLocale) aria-current="true" @endif>
                            <span aria-hidden="true">{{ $code }}</span>
                            <span class="sr-only">{{ $name }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>

            {{-- Badge diisi JS dari localStorage; kosong = disembunyikan --}}
            <a href="{{ route('cart') }}" data-module="cart-badge"
               @class(['btn-ghost relative px-2.5', 'text-brand-ink' => request()->routeIs('cart')])
               @if (request()->routeIs('cart')) aria-current="page" @endif>
                <x-icon name="shopping-cart" class="size-6" />
                <span class="sr-only">{{ __('Cart') }}, <span data-cart-count-label>0</span> {{ __('tickets') }}</span>
                <span class="absolute -top-0.5 -right-0.5 grid h-5 min-w-5 place-items-center rounded-full bg-brand-600 px-1 text-[11px] font-bold tabular-nums text-white ring-2 ring-canvas"
                      data-cart-count aria-hidden="true" hidden>0</span>
            </a>

            {{-- Default tamu; JS menukar ke "Akun" bila ri_session ada --}}
            <div data-module="account-nav">
                <a href="{{ route('login') }}" class="btn-ghost px-2.5 sm:px-3" data-session="guest">
                    <x-icon name="user" class="size-5" />
                    <span class="max-sm:sr-only">{{ __('Sign in') }}</span>
                </a>
                <a href="{{ route('account.dashboard') }}" class="btn-ghost px-2.5 sm:px-3" data-session="user" hidden>
                    <span class="grid size-7 place-items-center rounded-full bg-brand-soft text-xs font-bold text-brand-ink" aria-hidden="true" data-session-initial>A</span>
                    <span class="max-sm:sr-only">{{ __('Account') }}</span>
                </a>
            </div>

            <button type="button" class="btn-ghost md:hidden" data-nav-toggle aria-expanded="false" aria-controls="menu-mobile">
                <x-icon name="menu-2" class="size-6" data-nav-icon="open" />
                <x-icon name="x" class="hidden size-6" data-nav-icon="close" />
                <span class="sr-only">{{ __('Open menu') }}</span>
            </button>
        </div>
    </nav>

    <div id="menu-mobile" class="border-t border-line md:hidden" hidden>
        <ul class="container-page flex flex-col gap-1 py-3">
            @foreach ($links as $link)
                <li>
                    <a href="{{ $link['href'] }}"
                       @class([
                           'block rounded-md px-3.5 py-3 text-base font-medium',
                           'bg-brand-soft text-brand-ink' => $link['active'],
                           'text-ink hover:bg-surface-muted' => ! $link['active'],
                       ])
                       @if ($link['active']) aria-current="page" @endif>{{ $link['label'] }}</a>
                </li>
            @endforeach
        </ul>
    </div>
</header>

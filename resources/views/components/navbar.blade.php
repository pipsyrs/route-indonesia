@php
    $links = [
        ['label' => 'Beranda', 'href' => route('home'), 'active' => request()->routeIs('home')],
        ['label' => 'Katalog', 'href' => route('catalog.index'), 'active' => request()->routeIs('catalog.*')],
        ['label' => 'Cek Pesanan', 'href' => route('order.check'), 'active' => request()->routeIs('order.check')],
    ];
@endphp

<header class="sticky top-0 z-30 border-b border-line bg-canvas/85 backdrop-blur-md print:hidden" data-module="nav">
    <nav class="container-page flex h-16 items-center justify-between gap-4" aria-label="Navigasi utama">
        <x-logo />

        <ul class="hidden flex-1 items-center gap-1 md:flex">
            @foreach ($links as $link)
                <li>
                    <a href="{{ $link['href'] }}"
                       @class([
                           'rounded-xl px-3.5 py-2 text-sm font-medium transition-colors',
                           'bg-brand-soft text-brand-ink' => $link['active'],
                           'text-ink-muted hover:bg-surface-muted hover:text-ink' => ! $link['active'],
                       ])
                       @if ($link['active']) aria-current="page" @endif>{{ $link['label'] }}</a>
                </li>
            @endforeach
        </ul>

        <div class="flex items-center gap-1">
            {{-- Badge diisi JS dari localStorage; kosong = disembunyikan --}}
            <a href="{{ route('cart') }}" data-module="cart-badge"
               @class(['btn-ghost relative px-2.5', 'text-brand-ink' => request()->routeIs('cart')])
               @if (request()->routeIs('cart')) aria-current="page" @endif>
                <x-icon name="shopping-cart" class="size-6" />
                <span class="sr-only">Keranjang, <span data-cart-count-label>0</span> tiket</span>
                <span class="absolute -top-0.5 -right-0.5 grid h-5 min-w-5 place-items-center rounded-full bg-brand-600 px-1 text-[11px] font-bold tabular-nums text-white ring-2 ring-canvas"
                      data-cart-count aria-hidden="true" hidden>0</span>
            </a>

            {{-- Default tamu; JS menukar ke "Akun" bila ri_session ada --}}
            <div data-module="account-nav">
                <a href="{{ route('login') }}" class="btn-ghost px-2.5 sm:px-3" data-session="guest">
                    <x-icon name="user" class="size-5" />
                    <span class="max-sm:sr-only">Masuk</span>
                </a>
                <a href="{{ route('account.dashboard') }}" class="btn-ghost px-2.5 sm:px-3" data-session="user" hidden>
                    <span class="grid size-7 place-items-center rounded-full bg-brand-soft text-xs font-bold text-brand-ink" aria-hidden="true" data-session-initial>A</span>
                    <span class="max-sm:sr-only">Akun</span>
                </a>
            </div>

            <button type="button" class="btn-ghost md:hidden" data-nav-toggle aria-expanded="false" aria-controls="menu-mobile">
                <x-icon name="menu-2" class="size-6" data-nav-icon="open" />
                <x-icon name="x" class="hidden size-6" data-nav-icon="close" />
                <span class="sr-only">Buka menu</span>
            </button>
        </div>
    </nav>

    <div id="menu-mobile" class="border-t border-line md:hidden" hidden>
        <ul class="container-page flex flex-col gap-1 py-3">
            @foreach ($links as $link)
                <li>
                    <a href="{{ $link['href'] }}"
                       @class([
                           'block rounded-xl px-3.5 py-3 text-base font-medium',
                           'bg-brand-soft text-brand-ink' => $link['active'],
                           'text-ink hover:bg-surface-muted' => ! $link['active'],
                       ])
                       @if ($link['active']) aria-current="page" @endif>{{ $link['label'] }}</a>
                </li>
            @endforeach
        </ul>
    </div>
</header>

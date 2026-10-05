@props(['user'])
@php
    $links = [
        ['label' => 'Dashboard', 'icon' => 'layout-dashboard', 'route' => 'account.dashboard'],
        ['label' => 'Pesanan', 'icon' => 'ticket', 'route' => 'account.orders'],
        ['label' => 'Profil', 'icon' => 'user', 'route' => 'account.profile'],
    ];
@endphp
<aside {{ $attributes->merge(['class' => 'lg:sticky lg:top-24 lg:self-start']) }} aria-label="Menu akun">
    <div class="flex items-center gap-3 px-1">
        <span class="grid size-11 place-items-center rounded-full bg-brand-600 text-sm font-bold text-white" aria-hidden="true">
            {{ collect(explode(' ', $user['name']))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('') }}
        </span>
        <div class="min-w-0">
            <p class="truncate font-semibold text-ink">{{ $user['name'] }}</p>
            <p class="truncate text-xs text-ink-subtle">{{ $user['email'] }}</p>
        </div>
    </div>

    {{-- Mobile: tab horizontal; desktop: daftar vertikal --}}
    <nav class="mt-5">
        <ul class="-mx-4 flex gap-1 overflow-x-auto px-4 lg:mx-0 lg:flex-col lg:px-0">
            @foreach ($links as $link)
                @php $isActive = request()->routeIs($link['route']); @endphp
                <li class="shrink-0">
                    <a href="{{ route($link['route']) }}"
                       @class([
                           'flex items-center gap-2.5 rounded-xl px-3.5 py-2.5 text-sm font-medium transition-colors',
                           'bg-brand-soft text-brand-ink' => $isActive,
                           'text-ink-muted hover:bg-surface-muted hover:text-ink' => ! $isActive,
                       ])
                       @if ($isActive) aria-current="page" @endif>
                        <x-icon :name="$link['icon']" class="size-5" />
                        {{ $link['label'] }}
                    </a>
                </li>
            @endforeach
            <li class="shrink-0 lg:mt-2 lg:border-t lg:border-line lg:pt-2">
                <button type="button" class="flex w-full items-center gap-2.5 rounded-xl px-3.5 py-2.5 text-sm font-medium text-ink-muted transition-colors hover:bg-danger-soft hover:text-danger-ink"
                        data-action="logout" data-redirect="{{ route('home') }}">
                    <x-icon name="logout" class="size-5" />
                    Keluar
                </button>
            </li>
        </ul>
    </nav>

    <p class="mt-5 hidden rounded-xl bg-surface-muted px-3.5 py-3 text-xs text-ink-subtle lg:block">
        <span class="font-semibold text-ink-muted">Mode demo.</span> Data akun ini contoh dan tidak tersimpan di server.
    </p>
</aside>

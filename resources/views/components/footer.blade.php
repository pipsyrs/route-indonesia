<footer class="mt-20 border-t border-line bg-surface print:hidden">
    <div class="container-page grid gap-10 py-12 md:grid-cols-[1.4fr_1fr_1fr]">
        <div class="max-w-sm">
            <x-logo />
            <p class="mt-4 text-sm leading-relaxed text-ink-muted">
                {{ __('Intercity travel and shuttle tickets from verified operators. Choose your own seat and pay the way you like.') }}
            </p>
        </div>

        <div>
            <h2 class="text-sm font-semibold text-ink">{{ __('Explore') }}</h2>
            <ul class="mt-3 space-y-2 text-sm text-ink-muted">
                <li><a class="hover:text-brand-ink" href="{{ route('shop') }}">{{ __('Shop') }}</a></li>
                <li><a class="hover:text-brand-ink" href="{{ route('trip') }}">{{ __('Trip') }}</a></li>
                <li><a class="hover:text-brand-ink" href="{{ route('collaboration') }}">{{ __('Collaboration') }}</a></li>
                <li><a class="hover:text-brand-ink" href="{{ route('career') }}">{{ __('Careers') }}</a></li>
            </ul>
        </div>

        <div>
            <h2 class="text-sm font-semibold text-ink">{{ __('Help') }}</h2>
            <ul class="mt-3 space-y-2 text-sm text-ink-muted">
                <li class="flex items-center gap-2"><x-icon name="phone" class="size-4" /> <a class="hover:text-brand-ink" href="tel:+622150891234">(021) 5089-1234</a></li>
                <li class="flex items-center gap-2"><x-icon name="mail" class="size-4" /> <a class="hover:text-brand-ink" href="mailto:halo@routeindonesia.id">halo@routeindonesia.id</a></li>
            </ul>
        </div>
    </div>

    <div class="border-t border-line">
        <div class="container-page flex flex-col gap-4 py-6 text-xs text-ink-subtle sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ now()->year }} RouteIndonesia. {{ __('Schedules and prices on this site are sample data.') }}</p>
            @php
                $socials = array_filter([
                    'instagram' => ['label' => 'Instagram', 'url' => config('services.social.instagram')],
                    'tiktok' => ['label' => 'TikTok', 'url' => config('services.social.tiktok')],
                    'facebook' => ['label' => 'Facebook', 'url' => config('services.social.facebook')],
                ], fn ($social) => filled($social['url']));
            @endphp
            @if ($socials)
                <ul class="flex items-center gap-1" aria-label="{{ __('Follow us') }}">
                    @foreach ($socials as $icon => $social)
                        <li>
                            <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer"
                               class="grid size-9 place-items-center rounded-md text-ink-muted transition-colors hover:bg-surface-muted hover:text-brand-ink"
                               aria-label="{{ __(':network (opens in a new tab)', ['network' => $social['label']]) }}">
                                <x-icon :name="'brand-'.$icon" class="size-5" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</footer>

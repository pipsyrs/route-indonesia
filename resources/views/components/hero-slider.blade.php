@php
    // TODO: ganti foto picsum dengan foto armada/rute asli (1600x900)
    $slides = [
        [
            'eyebrow' => __('Intercity travel'),
            'title' => __('Book intercity travel without queuing at the counter.'),
            'desc' => __('Compare shuttle schedules from verified operators and pay via VA or QRIS.'),
            'cta' => __('Browse schedules'),
            'href' => route('catalog.index'),
            'image' => 'https://picsum.photos/seed/routeindonesia-perjalanan-darat/1600/900',
        ],
        [
            'eyebrow' => __('West Java'),
            'title' => __('Jakarta to Bandung, departing every hour.'),
            'desc' => __('Premium Hiace and Elf vans with pickup at the nearest pool.'),
            'cta' => __('See schedules'),
            'href' => route('catalog.index', ['from' => 'Jakarta', 'to' => 'Bandung']),
            'image' => 'https://picsum.photos/seed/routeindonesia-bandung/1600/900',
        ],
        [
            'eyebrow' => __('East Java'),
            'title' => __('Surabaya to Malang, just sit back and arrive.'),
            'desc' => __('Pick a departure time, add it to your cart, and finish in a single checkout.'),
            'cta' => __('See schedules'),
            'href' => route('catalog.index', ['from' => 'Surabaya', 'to' => 'Malang']),
            'image' => 'https://picsum.photos/seed/routeindonesia-malang/1600/900',
        ],
    ];
@endphp

<section class="container-page pt-4 sm:pt-6" aria-roledescription="carousel" aria-label="{{ __('Route highlights') }}" data-module="hero-slider">
    {{-- H1 di luar slide agar tidak ikut inert saat slide berganti --}}
    <h1 class="sr-only">{{ __('RouteIndonesia: book intercity travel tickets') }}</h1>

    <div class="relative touch-pan-y overflow-hidden rounded-lg bg-brand-950 select-none" data-slider-viewport>
        <div class="slider-track" data-slider-track>
            @foreach ($slides as $slide)
                <div class="relative w-full shrink-0" role="group" aria-roledescription="slide"
                    aria-label="{{ __(':current of :total', ['current' => $loop->iteration, 'total' => count($slides)]) }}" data-slide
                    @unless ($loop->first) aria-hidden="true" inert @endunless>
                    <img src="{{ $slide['image'] }}" alt="" width="1600" height="900" draggable="false"
                         @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif
                         class="absolute inset-0 size-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-r from-brand-950/90 via-brand-950/60 to-brand-950/10" aria-hidden="true"></div>

                    <div class="relative flex min-h-[26rem] max-w-2xl flex-col justify-center px-6 pt-10 pb-24 sm:min-h-[30rem] sm:px-10 lg:min-h-[34rem] lg:px-14">
                        <p class="text-xs font-semibold tracking-[0.18em] text-brand-200 uppercase">{{ $slide['eyebrow'] }}</p>
                        <p class="mt-3 text-4xl leading-[1.05] font-extrabold tracking-tight text-balance text-white md:text-5xl lg:text-[3.6rem]">{{ $slide['title'] }}</p>
                        <p class="mt-4 max-w-[44ch] text-base leading-relaxed text-brand-100 md:text-lg">{{ $slide['desc'] }}</p>
                        <a href="{{ $slide['href'] }}" class="btn mt-7 w-fit bg-white text-brand-800 hover:bg-brand-50">
                            {{ $slide['cta'] }}
                            <x-icon name="arrow-right" class="size-4" />
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Kontrol: di atas area form yang menumpuk, jadi diletakkan kanan atas --}}
        <div class="absolute top-4 right-4 flex items-center gap-2 sm:top-6 sm:right-6">
            {{-- Jeda/putar autoplay; label ditukar JS --}}
            <button type="button" class="grid size-10 place-items-center rounded-md bg-white/15 text-white backdrop-blur transition hover:bg-white/25" data-slider-toggle
                    data-label-play="{{ __('Play slideshow') }}" data-label-pause="{{ __('Pause slideshow') }}">
                <x-icon name="player-pause" class="size-5" data-slider-icon="pause" />
                <x-icon name="player-play" class="hidden size-5" data-slider-icon="play" />
                <span class="sr-only" data-slider-toggle-label>{{ __('Pause slideshow') }}</span>
            </button>
            <button type="button" class="grid size-10 place-items-center rounded-md bg-white/15 text-white backdrop-blur transition hover:bg-white/25" data-slider-prev>
                <x-icon name="chevron-left" class="size-5" />
                <span class="sr-only">{{ __('Previous slide') }}</span>
            </button>
            <button type="button" class="grid size-10 place-items-center rounded-md bg-white/15 text-white backdrop-blur transition hover:bg-white/25" data-slider-next>
                <x-icon name="chevron-right" class="size-5" />
                <span class="sr-only">{{ __('Next slide') }}</span>
            </button>
        </div>

        <div class="absolute bottom-16 left-6 flex items-center gap-2 sm:bottom-20 sm:left-10 lg:left-14">
            @foreach ($slides as $slide)
                <button type="button" class="h-1.5 w-6 rounded-full bg-white/40 transition-all aria-[current=true]:w-10 aria-[current=true]:bg-white"
                        data-slider-dot="{{ $loop->index }}" @if ($loop->first) aria-current="true" @endif>
                    <span class="sr-only">{{ __('Show slide :number', ['number' => $loop->iteration]) }}</span>
                </button>
            @endforeach
        </div>
    </div>
</section>

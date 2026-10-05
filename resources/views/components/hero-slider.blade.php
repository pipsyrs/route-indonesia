@php
    // TODO: ganti foto picsum dengan foto armada/rute asli (1600x900)
    $slides = [
        [
            'eyebrow' => 'Travel antarkota',
            'title' => 'Pesan travel antarkota tanpa antre di loket.',
            'desc' => 'Bandingkan jadwal shuttle dari operator terverifikasi dan bayar lewat VA atau QRIS.',
            'cta' => 'Lihat katalog',
            'href' => route('catalog.index'),
            'image' => 'https://picsum.photos/seed/routeindonesia-perjalanan-darat/1600/900',
        ],
        [
            'eyebrow' => 'Jawa Barat',
            'title' => 'Jakarta ke Bandung, berangkat tiap jam.',
            'desc' => 'Hiace dan Elf premium dengan titik jemput di pool terdekat.',
            'cta' => 'Cek jadwal',
            'href' => route('catalog.index', ['from' => 'Jakarta', 'to' => 'Bandung']),
            'image' => 'https://picsum.photos/seed/routeindonesia-bandung/1600/900',
        ],
        [
            'eyebrow' => 'Jawa Timur',
            'title' => 'Surabaya ke Malang, cukup duduk dan tiba.',
            'desc' => 'Pilih jam berangkat, masukkan ke keranjang, selesaikan dalam satu kali checkout.',
            'cta' => 'Cek jadwal',
            'href' => route('catalog.index', ['from' => 'Surabaya', 'to' => 'Malang']),
            'image' => 'https://picsum.photos/seed/routeindonesia-malang/1600/900',
        ],
    ];
@endphp

<section class="container-page pt-4 sm:pt-6" aria-roledescription="carousel" aria-label="Sorotan rute" data-module="hero-slider">
    {{-- H1 di luar slide agar tidak ikut inert saat slide berganti --}}
    <h1 class="sr-only">RouteIndonesia: pesan tiket travel antarkota</h1>

    <div class="relative touch-pan-y overflow-hidden rounded-3xl bg-brand-950 select-none" data-slider-viewport>
        <div class="slider-track" data-slider-track>
            @foreach ($slides as $slide)
                <div class="relative w-full shrink-0" role="group" aria-roledescription="slide"
                    aria-label="{{ $loop->iteration }} dari {{ count($slides) }}" data-slide
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
            <button type="button" class="grid size-10 place-items-center rounded-full bg-white/15 text-white backdrop-blur transition hover:bg-white/25" data-slider-toggle>
                <x-icon name="player-pause" class="size-5" data-slider-icon="pause" />
                <x-icon name="player-play" class="hidden size-5" data-slider-icon="play" />
                <span class="sr-only" data-slider-toggle-label>Jeda slide otomatis</span>
            </button>
            <button type="button" class="grid size-10 place-items-center rounded-full bg-white/15 text-white backdrop-blur transition hover:bg-white/25" data-slider-prev>
                <x-icon name="chevron-left" class="size-5" />
                <span class="sr-only">Slide sebelumnya</span>
            </button>
            <button type="button" class="grid size-10 place-items-center rounded-full bg-white/15 text-white backdrop-blur transition hover:bg-white/25" data-slider-next>
                <x-icon name="chevron-right" class="size-5" />
                <span class="sr-only">Slide berikutnya</span>
            </button>
        </div>

        <div class="absolute bottom-16 left-6 flex items-center gap-2 sm:bottom-20 sm:left-10 lg:left-14">
            @foreach ($slides as $slide)
                <button type="button" class="h-1.5 w-6 rounded-full bg-white/40 transition-all aria-[current=true]:w-10 aria-[current=true]:bg-white"
                        data-slider-dot="{{ $loop->index }}" @if ($loop->first) aria-current="true" @endif>
                    <span class="sr-only">Tampilkan slide {{ $loop->iteration }}</span>
                </button>
            @endforeach
        </div>
    </div>
</section>

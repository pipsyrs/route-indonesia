@extends('layouts.app')

@section('title', 'Pilih kursi '.$trip['operator'])

@section('module', 'seat-picker')

@section('content')
    <div class="container-page py-6 md:py-8">
        <x-stepper :current="1" />

        <a href="{{ route('search', $backQuery) }}" class="mt-6 inline-flex items-center gap-1.5 text-sm font-medium text-ink-muted hover:text-brand-ink">
            <x-icon name="arrow-left" class="size-4" />
            Kembali ke hasil pencarian
        </a>

        {{-- Info trip --}}
        <header class="mt-4 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                    <h1 class="text-2xl font-bold tracking-tight text-ink md:text-3xl">{{ $trip['operator'] }}</h1>
                    <x-rating :value="$trip['rating']" />
                </div>
                <p class="mt-1 text-ink-muted">{{ $trip['vehicle'] }}, {{ $dateLabel }}</p>
            </div>
            <div class="flex items-center gap-4">
                <div>
                    <p class="text-2xl font-bold tabular-nums text-ink">{{ $trip['depart'] }}</p>
                    <p class="text-xs text-ink-subtle">{{ $trip['origin'] }}</p>
                </div>
                <div class="flex flex-col items-center px-2 text-xs text-ink-muted">
                    {{ $trip['duration'] }}
                    <x-icon name="arrow-right" class="size-4 text-ink-subtle" />
                </div>
                <div>
                    <p class="text-2xl font-bold tabular-nums text-ink">
                        {{ $trip['arrive'] }}@if ($trip['arrives_next_day'])<sup class="ml-0.5 text-[10px] font-semibold text-warn-ink">+1</sup>@endif
                    </p>
                    <p class="text-xs text-ink-subtle">{{ $trip['destination'] }}</p>
                </div>
            </div>
        </header>

        <ul class="mt-4 flex flex-wrap gap-1.5" aria-label="Fasilitas">
            @foreach ($trip['facilities'] as $code)
                <li><x-facility-badge :code="$code" :label="$facilityLabels[$code] ?? $code" /></li>
            @endforeach
        </ul>

        @if ($notice || $passengersReduced)
            <div class="mt-6 flex items-start gap-3 rounded-xl bg-warn-soft p-4 text-sm text-warn-ink" role="alert">
                <x-icon name="alert-circle" class="size-5" />
                <p>
                    {{ $notice }}
                    @if ($passengersReduced)
                        Sisa kursi di jadwal ini {{ $trip['seats_left'] }}, jadi jumlah penumpang disesuaikan menjadi {{ $passengers }}.
                    @endif
                </p>
            </div>
        @endif

        <form action="{{ route('booking') }}" method="get"
              class="mt-8 grid gap-8 pb-28 lg:grid-cols-[1fr_22rem] lg:pb-0"
              data-seat-form
              data-max-seats="{{ $passengers }}"
              data-price="{{ $trip['price'] }}"
              data-service-fee="{{ $serviceFee }}">
            <input type="hidden" name="trip" value="{{ $trip['id'] }}">
            <input type="hidden" name="from" value="{{ $trip['origin'] }}">
            <input type="hidden" name="to" value="{{ $trip['destination'] }}">
            <input type="hidden" name="date" value="{{ $date }}">

            <div class="min-w-0 space-y-10">
                {{-- Denah kursi: checkbox asli agar bisa dipakai keyboard dan tanpa JS --}}
                <section aria-labelledby="denah-title">
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <h2 id="denah-title" class="text-lg font-bold text-ink">Pilih {{ $passengers }} kursi</h2>
                        <p class="text-sm text-ink-muted"><span data-seat-count>0</span> dari {{ $passengers }} dipilih</p>
                    </div>

                    <div class="mt-5 flex flex-col gap-6 sm:flex-row sm:items-start">
                        <fieldset class="w-fit rounded-2xl border border-line bg-surface p-4 sm:p-5">
                            <legend class="sr-only">Denah kursi, bagian depan kendaraan di atas</legend>
                            <div class="grid grid-cols-4 gap-2.5">
                                @foreach ($seatMap as $row)
                                    @foreach ($row as $cell)
                                        @if ($cell === 'D')
                                            <span class="grid size-12 place-items-center rounded-xl bg-surface-muted text-ink-subtle" title="Sopir">
                                                <x-icon name="steering-wheel" class="size-6" />
                                                <span class="sr-only">Sopir</span>
                                            </span>
                                        @elseif ($cell === null)
                                            <span class="size-12" aria-hidden="true"></span>
                                        @else
                                            @php $isOccupied = in_array($cell, $occupied, true); @endphp
                                            <label @class(['relative', 'cursor-pointer' => ! $isOccupied])>
                                                <input type="checkbox" name="seats[]" value="{{ $cell }}"
                                                       class="peer sr-only" data-seat
                                                       aria-label="Kursi {{ $cell }}, {{ $isOccupied ? 'terisi' : 'tersedia' }}"
                                                       @disabled($isOccupied)>
                                                <span @class([
                                                    'grid size-12 place-items-center rounded-xl border text-sm font-semibold tabular-nums transition',
                                                    'border-transparent bg-seat-occupied text-ink-subtle line-through' => $isOccupied,
                                                    'border-line-strong bg-seat-available text-ink hover:border-brand-500 peer-checked:border-seat-selected peer-checked:bg-seat-selected peer-checked:text-white peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand-500 active:scale-95' => ! $isOccupied,
                                                ])>{{ $cell }}</span>
                                            </label>
                                        @endif
                                    @endforeach
                                @endforeach
                            </div>
                        </fieldset>

                        <ul class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm text-ink-muted sm:grid-cols-1" aria-label="Keterangan denah">
                            <li class="flex items-center gap-2.5"><span class="size-5 rounded-md border border-line-strong bg-seat-available"></span> Tersedia</li>
                            <li class="flex items-center gap-2.5"><span class="size-5 rounded-md bg-seat-occupied"></span> Terisi</li>
                            <li class="flex items-center gap-2.5"><span class="size-5 rounded-md bg-seat-selected"></span> Dipilih</li>
                            <li class="flex items-center gap-2.5"><x-icon name="steering-wheel" class="size-5 text-ink-subtle" /> Sopir</li>
                        </ul>
                    </div>
                    <p class="form-error" data-seat-error hidden></p>
                </section>

                @foreach (['pickup' => ['Titik jemput', $trip['pickups'], 'map-pin'], 'dropoff' => ['Titik antar', $trip['dropoffs'], 'map-2']] as $field => [$title, $stops, $icon])
                    <fieldset>
                        <legend class="text-lg font-bold text-ink">{{ $title }}</legend>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            @foreach ($stops as $stop)
                                <label class="cursor-pointer">
                                    <input type="radio" name="{{ $field }}" value="{{ $stop['id'] }}" class="peer sr-only"
                                           data-stop="{{ $field }}" data-stop-label="{{ $stop['name'] }} ({{ $stop['time'] }})"
                                           @checked($loop->first)>
                                    <span class="flex h-full gap-3 rounded-2xl border border-line bg-surface p-4 transition-colors hover:border-brand-500 peer-checked:border-brand-600 peer-checked:bg-brand-soft peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand-500">
                                        <x-icon :name="$icon" class="mt-0.5 size-5 text-brand-ink" />
                                        <span class="min-w-0 flex-1">
                                            <span class="flex items-baseline justify-between gap-3">
                                                <span class="font-semibold text-ink">{{ $stop['name'] }}</span>
                                                <span class="text-sm font-semibold tabular-nums text-ink">{{ $stop['time'] }}</span>
                                            </span>
                                            <span class="mt-0.5 block text-sm text-ink-muted">{{ $stop['address'] }}</span>
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach
            </div>

            <div class="lg:sticky lg:top-24 lg:self-start">
                <x-order-summary :trip="$trip" :date-label="$dateLabel"
                                 :pickup="$trip['pickups'][0]" :dropoff="$trip['dropoffs'][0]"
                                 :service-fee="$serviceFee" :total="$serviceFee">
                    <button type="submit" class="btn-primary mt-5 hidden w-full lg:flex" data-seat-submit>
                        Lanjutkan
                        <x-icon name="arrow-right" class="size-4" />
                    </button>
                </x-order-summary>
            </div>

            {{-- Bilah bawah mobile: total + aksi selalu terlihat --}}
            <div class="fixed inset-x-0 bottom-0 z-20 border-t border-line bg-surface/95 backdrop-blur lg:hidden">
                <div class="container-page flex items-center justify-between gap-4 py-3">
                    <div>
                        <p class="text-xs text-ink-subtle">Total, <span data-seat-count>0</span> kursi</p>
                        <x-price :amount="$serviceFee" class="text-lg font-bold text-ink" data-summary-total-mobile />
                    </div>
                    <button type="submit" class="btn-primary px-6" data-seat-submit>Lanjutkan</button>
                </div>
            </div>
        </form>
    </div>
@endsection

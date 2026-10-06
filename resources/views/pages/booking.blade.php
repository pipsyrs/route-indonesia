@extends('layouts.app')

@section('title', 'Data penumpang')

@section('module', 'booking-form')

@section('content')
    <div class="container-page py-6 md:py-8">
        <x-stepper :current="2" />

        <a href="{{ route('trip.show', ['id' => $trip['id'], 'date' => $date, 'passengers' => count($seats)]) }}"
           class="mt-6 inline-flex items-center gap-1.5 text-sm font-medium text-ink-muted hover:text-brand-ink">
            <x-icon name="arrow-left" class="size-4" />
            Ubah kursi
        </a>
        <h1 class="mt-3 text-2xl font-bold tracking-tight text-ink md:text-3xl">Data pemesan dan penumpang</h1>

        {{--
            Field data pribadi sengaja TANPA atribut name: form GET hanya membawa
            $orderQuery, sedangkan nama/email/HP disimpan JS di sessionStorage.
            Penyimpanan booking ke server (POST + CSRF) adalah fase berikutnya.
        --}}
        <form action="{{ route('payment') }}" method="get" novalidate
              class="mt-8 grid gap-8 lg:grid-cols-[1fr_22rem]"
              data-booking-form
              data-order-key="{{ $trip['id'] }}:{{ implode(',', $seats) }}"
              data-subtotal="{{ $subtotal }}"
              data-departure-weekday="{{ \Illuminate\Support\Carbon::parse($date)->isoWeekday() }}"
              data-promos='@json($promos)'>
            @include('partials.order-query-fields')

            <div class="min-w-0 space-y-8">
                <section class="card p-5 sm:p-6" aria-labelledby="pemesan-title">
                    <h2 id="pemesan-title" class="text-lg font-bold text-ink">Data pemesan</h2>
                    <p class="mt-1 text-sm text-ink-muted">E-ticket dan status pembayaran dikirim ke kontak ini.</p>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="kontak-nama" class="form-label">Nama lengkap</label>
                            <input id="kontak-nama" type="text" class="form-input" autocomplete="name" maxlength="100"
                                   data-field="contact_name" data-rule="name" aria-describedby="kontak-nama-error" required>
                            <p id="kontak-nama-error" class="form-error" hidden></p>
                        </div>
                        <div>
                            <label for="kontak-email" class="form-label">Email</label>
                            <input id="kontak-email" type="email" class="form-input" autocomplete="email" maxlength="150" inputmode="email"
                                   data-field="contact_email" data-rule="email" aria-describedby="kontak-email-error" required>
                            <p id="kontak-email-error" class="form-error" hidden></p>
                        </div>
                        <div>
                            <label for="kontak-hp" class="form-label">Nomor HP</label>
                            <input id="kontak-hp" type="tel" class="form-input" autocomplete="tel" maxlength="16" inputmode="tel"
                                   data-field="contact_phone" data-rule="phone" aria-describedby="kontak-hp-hint kontak-hp-error" required>
                            <p id="kontak-hp-hint" class="form-hint">Format 08xx atau +628xx.</p>
                            <p id="kontak-hp-error" class="form-error" hidden></p>
                        </div>
                    </div>
                </section>

                <section class="card p-5 sm:p-6" aria-labelledby="penumpang-title">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 id="penumpang-title" class="text-lg font-bold text-ink">Penumpang</h2>
                        <label class="flex cursor-pointer items-center gap-2 text-sm text-ink">
                            <input type="checkbox" class="size-4 rounded accent-brand-600" data-same-as-contact>
                            Pemesan juga penumpang
                        </label>
                    </div>

                    <div class="mt-5 space-y-5">
                        @foreach ($seats as $index => $seat)
                            <fieldset class="rounded-md bg-surface-muted p-4" data-passenger data-seat="{{ $seat }}">
                                <legend class="sr-only">Penumpang {{ $index + 1 }}, kursi {{ $seat }}</legend>
                                <p class="flex items-center gap-2 text-sm font-semibold text-ink" aria-hidden="true">
                                    Penumpang {{ $index + 1 }}
                                    <span class="chip bg-surface text-ink-muted">Kursi {{ $seat }}</span>
                                </p>
                                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="penumpang-{{ $index }}-nama" class="form-label">Nama sesuai identitas</label>
                                        <input id="penumpang-{{ $index }}-nama" type="text" class="form-input" maxlength="100"
                                               data-field="name" data-rule="name" aria-describedby="penumpang-{{ $index }}-nama-error" required>
                                        <p id="penumpang-{{ $index }}-nama-error" class="form-error" hidden></p>
                                    </div>
                                    <div>
                                        <label for="penumpang-{{ $index }}-hp" class="form-label">Nomor HP <span class="font-normal text-ink-subtle">(opsional)</span></label>
                                        <input id="penumpang-{{ $index }}-hp" type="tel" class="form-input" maxlength="16" inputmode="tel"
                                               data-field="phone" data-rule="phone-optional" aria-describedby="penumpang-{{ $index }}-hp-error">
                                        <p id="penumpang-{{ $index }}-hp-error" class="form-error" hidden></p>
                                    </div>
                                </div>
                            </fieldset>
                        @endforeach
                    </div>
                </section>

                <section class="card p-5 sm:p-6" aria-labelledby="promo-title">
                    <h2 id="promo-title" class="text-lg font-bold text-ink">Kode promo</h2>
                    <div class="mt-4 flex gap-2">
                        <label for="kode-promo" class="sr-only">Kode promo</label>
                        <input id="kode-promo" type="text" class="form-input font-mono uppercase" maxlength="30" autocomplete="off"
                               aria-describedby="kode-promo-hint kode-promo-status" data-promo-input>
                        <button type="button" class="btn-outline" data-promo-apply>Pakai</button>
                    </div>
                    <p id="kode-promo-hint" class="form-hint">Potongan di sini hanya simulasi. Promo diterapkan di server pada fase berikutnya.</p>
                    <p id="kode-promo-status" class="mt-1.5 text-xs font-medium" data-promo-status hidden></p>
                </section>

                <div>
                    <label class="flex cursor-pointer items-start gap-2.5 text-sm text-ink">
                        <input type="checkbox" class="mt-0.5 size-4 rounded accent-brand-600" data-rule="agree" aria-describedby="setuju-error" required>
                        Saya sudah memeriksa data di atas dan menyetujui syarat dan ketentuan perjalanan.
                    </label>
                    <p id="setuju-error" class="form-error" hidden></p>
                </div>
            </div>

            <div class="lg:sticky lg:top-24 lg:self-start">
                <x-order-summary :trip="$trip" :date-label="$dateLabel" :seats="$seats"
                                 :pickup="$pickup" :dropoff="$dropoff"
                                 :subtotal="$subtotal" :service-fee="$serviceFee" :total="$total">
                    <button type="submit" class="btn-primary mt-5 w-full">
                        Lanjut ke pembayaran
                        <x-icon name="arrow-right" class="size-4" />
                    </button>
                </x-order-summary>
            </div>
        </form>
    </div>
@endsection

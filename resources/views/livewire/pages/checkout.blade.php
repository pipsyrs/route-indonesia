{{-- Satu root; form dikelola JS vanilla (checkout.js) sehingga di-wire:ignore --}}
<div data-module="checkout">
    <div class="container-page py-6 md:py-10">
        <x-stepper :current="2" />

        <h1 class="mt-6 text-2xl font-bold tracking-tight text-ink md:text-3xl">Checkout</h1>
        <p class="mt-1 text-sm text-ink-muted">Isi data pemesan dan penumpang, lalu lanjut ke pembayaran.</p>

        {{-- Tanpa action/method: JS validasi, simpan ke localStorage, lalu pindah halaman --}}
        <form class="mt-6 grid gap-6 lg:grid-cols-[1fr_22rem] lg:gap-8" novalidate data-checkout-form wire:ignore
              data-empty-url="{{ route('cart') }}" data-next-url="{{ route('payment') }}">
            <div class="min-w-0 space-y-6">
                <section class="card p-5 sm:p-6" aria-labelledby="kontak-title">
                    <h2 id="kontak-title" class="text-lg font-bold text-ink">Data pemesan</h2>
                    <p class="mt-1 text-sm text-ink-muted">E-tiket dan info pembayaran dikirim ke kontak ini.</p>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="kontak-nama" class="form-label">Nama lengkap</label>
                            <input id="kontak-nama" type="text" class="form-input" autocomplete="name" maxlength="60"
                                   data-field="contact_name" data-rule="name" aria-describedby="kontak-nama-error">
                            <p id="kontak-nama-error" class="form-error" hidden></p>
                        </div>
                        <div>
                            <label for="kontak-email" class="form-label">Email</label>
                            <input id="kontak-email" type="email" class="form-input" autocomplete="email" maxlength="100"
                                   data-field="contact_email" data-rule="email" aria-describedby="kontak-email-error">
                            <p id="kontak-email-error" class="form-error" hidden></p>
                        </div>
                        <div>
                            <label for="kontak-hp" class="form-label">Nomor HP</label>
                            <input id="kontak-hp" type="tel" class="form-input" autocomplete="tel" inputmode="tel" maxlength="16" placeholder="08xxxxxxxxxx"
                                   data-field="contact_phone" data-rule="phone" aria-describedby="kontak-hp-error">
                            <p id="kontak-hp-error" class="form-error" hidden></p>
                        </div>
                    </div>
                </section>

                <section class="card p-5 sm:p-6" aria-labelledby="penumpang-title">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 id="penumpang-title" class="text-lg font-bold text-ink">Data penumpang</h2>
                        <label class="flex cursor-pointer items-center gap-2 text-sm text-ink-muted">
                            <input type="checkbox" class="size-4 rounded accent-brand-600" data-same-as-contact>
                            Penumpang pertama = pemesan
                        </label>
                    </div>
                    {{-- Diisi JS: satu fieldset per item keranjang, satu input nama per tiket --}}
                    <div class="mt-5 space-y-6" data-passenger-groups></div>
                </section>

                <section class="card p-5 sm:p-6" aria-labelledby="metode-title">
                    <h2 id="metode-title" class="text-lg font-bold text-ink">Metode pembayaran</h2>
                    {{-- Satu-satunya metode; radio tetap ada karena checkout.js membaca [name="method"]:checked --}}
                    <label class="mt-5 block cursor-pointer">
                        <input type="radio" name="method" value="bank_transfer" class="peer sr-only" checked aria-describedby="metode-note">
                        <span class="flex items-center gap-3 rounded-md border border-brand-600 bg-brand-soft px-4 py-3 text-sm peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand-500">
                            <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-surface text-brand-ink">
                                <x-icon name="building-bank" class="size-5" />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block font-semibold text-ink">Transfer Bank</span>
                                <span class="block text-xs text-ink-muted">Konfirmasi manual oleh admin</span>
                            </span>
                            <x-icon name="circle-check" class="size-5 text-brand-ink" />
                        </span>
                    </label>
                    <p id="metode-note" class="mt-3 flex items-start gap-2 text-sm text-ink-muted">
                        <x-icon name="info-circle" class="mt-0.5 size-4" />
                        <span>Setelah ini kamu akan melihat rekening tujuan. Transfer sesuai total, lalu unggah bukti transfer di halaman pembayaran.</span>
                    </p>
                </section>
            </div>

            <aside class="lg:sticky lg:top-24 lg:self-start">
                <x-cart-summary :service-fee="$serviceFee" title="Pesananmu">
                    <label class="mt-5 flex items-start gap-2.5 text-sm text-ink-muted">
                        <input id="setuju" type="checkbox" class="mt-0.5 size-4 shrink-0 rounded accent-brand-600" data-rule="agree" aria-describedby="setuju-error">
                        <span>Saya setuju dengan syarat perjalanan dan kebijakan pembatalan operator.</span>
                    </label>
                    <p id="setuju-error" class="form-error" hidden></p>

                    <button type="submit" class="btn-primary mt-5 w-full py-3">
                        Lanjut ke pembayaran
                        <x-icon name="arrow-right" class="size-4" />
                    </button>
                    <a href="{{ route('cart') }}" class="btn-ghost mt-2 w-full">Ubah keranjang</a>
                </x-cart-summary>
            </aside>
        </form>

        <template data-passenger-group-template>
            <fieldset class="rounded-md border border-line p-4" data-passenger-group>
                <legend class="px-1 text-sm font-semibold text-ink" data-field="legend"></legend>
                <div class="grid gap-3 sm:grid-cols-2" data-passenger-inputs></div>
            </fieldset>
        </template>
    </div>
</div>

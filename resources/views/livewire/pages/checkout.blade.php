{{-- Satu root; form dikelola JS vanilla (checkout.js) sehingga di-wire:ignore --}}
<div data-module="checkout">
    <div class="container-page py-6 md:py-10">
        <x-stepper :current="2" />

        <h1 class="mt-6 text-2xl font-bold tracking-tight text-ink md:text-3xl">Checkout</h1>
        <p class="mt-1 text-sm text-ink-muted">Isi data pemesan dan alamat, lalu lanjut ke pembayaran.</p>

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

                <section class="card p-5 sm:p-6" aria-labelledby="alamat-title">
                    <h2 id="alamat-title" class="text-lg font-bold text-ink">Alamat lengkap</h2>
                    <p class="mt-1 text-sm text-ink-muted">Alamat pemesan untuk data pesanan.</p>

                    {{-- Alamat tersimpan: hanya pengguna masuk dengan buku alamat (checkout.js) --}}
                    <fieldset class="mt-5" data-saved-addresses hidden>
                        <legend class="form-label">Pilih dari buku alamat</legend>
                        <div class="grid gap-3 sm:grid-cols-2" data-saved-address-list>
                            <label class="cursor-pointer" data-saved-address-new>
                                <input type="radio" name="saved-address" value="new" class="peer sr-only">
                                <span class="flex h-full items-center gap-2 rounded-md border border-dashed border-line-strong p-4 text-sm font-semibold text-ink transition-colors peer-checked:border-brand-600 peer-checked:bg-brand-soft peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand-500">
                                    <x-icon name="plus" class="size-4" /> Pakai alamat lain
                                </span>
                            </label>
                        </div>
                        <button type="button" class="btn-ghost -ml-3 mt-2 text-brand-ink" data-address-edit aria-controls="address-fields" aria-expanded="false" hidden>Ubah<span class="sr-only"> alamat terpilih</span></button>
                    </fieldset>
                    <p class="mt-4 flex items-center gap-2 text-sm font-medium text-warn-ink" aria-live="polite" data-address-hint hidden>
                        <x-icon name="info-circle" class="size-4 shrink-0" /> Lengkapi provinsi dan kecamatan.
                    </p>

                    <div id="address-fields" class="mt-5 grid gap-4 sm:grid-cols-2" data-address-fields>
                        <div class="sm:col-span-2">
                            <label for="alamat-jalan" class="form-label">Alamat</label>
                            <textarea id="alamat-jalan" rows="3" class="form-input resize-none" autocomplete="street-address" maxlength="200"
                                      placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan"
                                      data-field="address_street" data-rule="address" aria-describedby="alamat-jalan-error"></textarea>
                            <p id="alamat-jalan-error" class="form-error" hidden></p>
                        </div>
                        <div>
                            <label for="alamat-provinsi" class="form-label">Provinsi</label>
                            <select id="alamat-provinsi" class="form-input appearance-none" autocomplete="address-level1"
                                    data-field="address_province" data-rule="option" aria-describedby="alamat-provinsi-error">
                                <option value="">Pilih provinsi</option>
                                @foreach ($provinces as $province)
                                    <option value="{{ $province }}">{{ $province }}</option>
                                @endforeach
                            </select>
                            <p id="alamat-provinsi-error" class="form-error" hidden></p>
                        </div>
                        <div>
                            <label for="alamat-kota" class="form-label">Kota/Kabupaten</label>
                            <input id="alamat-kota" type="text" class="form-input" autocomplete="address-level2" maxlength="60"
                                   data-field="address_city" data-rule="place" aria-describedby="alamat-kota-error">
                            <p id="alamat-kota-error" class="form-error" hidden></p>
                        </div>
                        <div>
                            <label for="alamat-kecamatan" class="form-label">Kecamatan</label>
                            <input id="alamat-kecamatan" type="text" class="form-input" autocomplete="address-level3" maxlength="60"
                                   data-field="address_district" data-rule="place" aria-describedby="alamat-kecamatan-error">
                            <p id="alamat-kecamatan-error" class="form-error" hidden></p>
                        </div>
                        <div>
                            <label for="alamat-pos" class="form-label">Kode pos</label>
                            <input id="alamat-pos" type="text" class="form-input" autocomplete="postal-code" inputmode="numeric" maxlength="5"
                                   data-field="address_postal" data-rule="postal" aria-describedby="alamat-pos-error">
                            <p id="alamat-pos-error" class="form-error" hidden></p>
                        </div>
                    </div>
                    <label class="mt-4 flex cursor-pointer items-center gap-2 text-sm text-ink-muted" data-save-address-option hidden>
                        <input type="checkbox" class="size-4 rounded accent-brand-600" data-save-address>
                        Simpan ke buku alamat
                    </label>
                </section>

                <section class="card p-5 sm:p-6" aria-labelledby="info-title">
                    <h2 id="info-title" class="text-lg font-bold text-ink">Info tambahan</h2>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="info-sumber" class="form-label">Dari mana Anda tahu trip ini?</label>
                            <select id="info-sumber" class="form-input appearance-none"
                                    data-field="referral_source" data-rule="option" aria-describedby="info-sumber-error">
                                <option value="">Pilih salah satu</option>
                                @foreach (['instagram' => 'Instagram', 'tiktok' => 'TikTok', 'friend' => 'Teman', 'google' => 'Google', 'other' => 'Lainnya'] as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <p id="info-sumber-error" class="form-error" hidden></p>
                        </div>
                        <div>
                            <label for="info-instagram" class="form-label">Username Instagram <span class="font-normal text-ink-subtle">(opsional)</span></label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-sm text-ink-subtle" aria-hidden="true">@</span>
                                <input id="info-instagram" type="text" class="form-input pl-8" autocomplete="off" autocapitalize="none" spellcheck="false" maxlength="31"
                                       data-field="instagram" data-rule="instagram" aria-describedby="info-instagram-error">
                            </div>
                            <p id="info-instagram-error" class="form-error" hidden></p>
                        </div>
                        <div>
                            <label for="info-referral" class="form-label">Kode referral <span class="font-normal text-ink-subtle">(opsional)</span></label>
                            <input id="info-referral" type="text" class="form-input uppercase" autocomplete="off" autocapitalize="characters" spellcheck="false" maxlength="20"
                                   data-field="referral_code" data-rule="referral" aria-describedby="info-referral-error">
                            <p id="info-referral-error" class="form-error" hidden></p>
                        </div>
                    </div>
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

                    {{-- Kursi ditolak server; isi pesan dari checkout.js --}}
                    <div class="mt-4 rounded-md bg-danger-soft p-3 text-sm text-danger-ink" role="alert" data-seat-error hidden>
                        <p class="flex items-start gap-2">
                            <x-icon name="alert-circle" class="mt-0.5 size-4 shrink-0" />
                            <span data-seat-error-message></span>
                        </p>
                        <a href="{{ route('cart') }}" class="mt-2 inline-block font-semibold underline underline-offset-2">Kembali ke keranjang</a>
                    </div>

                    <button type="submit" class="btn-primary mt-5 w-full py-3" data-checkout-submit>
                        Lanjut ke pembayaran
                        <x-icon name="arrow-right" class="size-4" />
                    </button>
                    <a href="{{ route('cart') }}" class="btn-ghost mt-2 w-full">Ubah keranjang</a>
                </x-cart-summary>
            </aside>
        </form>

        <template data-saved-address-template>
            <label class="cursor-pointer">
                <input type="radio" name="saved-address" class="peer sr-only">
                <span class="flex h-full flex-col rounded-md border border-line-strong p-4 text-sm transition-colors peer-checked:border-brand-600 peer-checked:bg-brand-soft peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand-500">
                    <span class="flex items-center gap-2">
                        <span class="truncate font-semibold text-ink" data-slot="label"></span>
                        <span class="chip ml-auto shrink-0 bg-brand-soft text-brand-ink" data-slot="primary" hidden>Utama</span>
                    </span>
                    <span class="mt-1 font-medium text-ink" data-slot="name"></span>
                    <span class="mt-1 break-words text-ink-muted" data-slot="address"></span>
                    <span class="text-ink-muted" data-slot="region"></span>
                </span>
            </label>
        </template>
    </div>
</div>

{{-- Tagihan & ringkasan dikelola JS vanilla (checkout-payment.js); form bukti dikelola Livewire --}}
<div data-module="checkout-payment">
    <div class="container-page py-6 md:py-10" data-payment-root
         data-back-url="{{ route('checkout') }}" data-success-url="{{ route('payment.success') }}">
        <x-stepper :current="3" />

        {{-- Kerangka awal sampai JS menyiapkan tagihan --}}
        <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_22rem] lg:gap-8" data-payment-loading aria-hidden="true" wire:ignore>
            <div class="space-y-4"><div class="skeleton h-24"></div><div class="skeleton h-64"></div></div>
            <div class="skeleton h-72"></div>
        </div>

        {{-- ignore.self: atribut hidden diubah JS, isi tetap di-morph --}}
        <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_22rem] lg:gap-8" data-payment-ready hidden wire:ignore.self>
            <div class="min-w-0 space-y-6">
                {{-- Hitung mundur: focal point halaman --}}
                <section class="flex flex-wrap items-center justify-between gap-4 rounded-lg bg-brand-soft p-5 text-brand-ink outline-none sm:p-6" tabindex="-1" data-countdown wire:ignore>
                    <div>
                        <p class="text-sm font-medium">Selesaikan transfer dalam</p>
                        <p class="mt-1 text-4xl font-extrabold tracking-tight tabular-nums" data-countdown-value aria-live="off">30:00</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs">Kode booking</p>
                        <p class="font-mono text-lg font-bold tracking-wider" data-booking-code></p>
                    </div>
                </section>

                <section class="rounded-lg bg-danger-soft p-5 text-danger-ink" data-payment-expired hidden role="alert" wire:ignore>
                    <div class="flex items-start gap-3">
                        <x-icon name="circle-x" class="size-6" />
                        <div>
                            <p class="font-bold">Waktu pembayaran habis</p>
                            <p class="mt-1 text-sm">Tagihan ini tidak berlaku lagi. Buat tagihan baru sebelum mentransfer.</p>
                            <button type="button" class="btn-outline mt-4" data-payment-renew>
                                <x-icon name="refresh" class="size-4" />
                                Buat tagihan baru
                            </button>
                        </div>
                    </div>
                </section>

                <section class="card p-5 sm:p-6" aria-labelledby="tagihan-title">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h1 id="tagihan-title" class="text-lg font-bold text-ink">Transfer Bank</h1>
                            <p class="text-sm text-ink-muted">Konfirmasi manual oleh admin</p>
                        </div>
                        <a href="{{ route('checkout') }}" class="btn-ghost text-sm">Ubah data pesanan</a>
                    </div>

                    {{-- Nominal diisi JS dari ri_checkout --}}
                    <div class="mt-5 rounded-md border border-dashed border-line-strong p-4" wire:ignore>
                        <p class="text-xs text-ink-subtle">Nominal yang harus ditransfer</p>
                        <div class="mt-1 flex flex-wrap items-center justify-between gap-3">
                            <p class="text-2xl font-extrabold text-ink tabular-nums" data-payment-total></p>
                            <button type="button" class="btn-outline px-3 py-1.5 text-xs" data-copy="" data-copy-message="Nominal disalin" data-amount-copy aria-label="Salin nominal transfer">
                                <x-icon name="copy" class="size-4" />
                                Salin
                            </button>
                        </div>
                        <p class="mt-2 text-xs text-ink-muted">Transfer tepat sesuai nominal agar mudah diverifikasi.</p>
                    </div>

                    <ol class="mt-6 grid gap-3 text-sm sm:grid-cols-3">
                        @foreach (['Transfer sesuai nominal ke salah satu rekening', 'Unggah bukti transfer di bawah', 'Tunggu konfirmasi admin'] as $step)
                            <li class="flex items-start gap-2.5">
                                <span class="grid size-6 shrink-0 place-items-center rounded-full bg-surface-muted text-xs font-bold text-ink-muted">{{ $loop->iteration }}</span>
                                <span class="text-ink-muted">{{ $step }}</span>
                            </li>
                        @endforeach
                    </ol>

                    <h2 class="mt-6 text-sm font-semibold text-ink">Rekening tujuan</h2>
                    @if ($this->bankAccounts === [])
                        <p class="mt-3 rounded-md bg-warn-soft p-4 text-sm text-warn-ink">Rekening tujuan belum tersedia. Hubungi admin sebelum mentransfer.</p>
                    @else
                        <ul class="mt-3 grid gap-3 sm:grid-cols-2">
                            @foreach ($this->bankAccounts as $account)
                                <x-bank-account-card wire:key="bank-{{ $loop->index }}" :bank="$account['bank']" :number="$account['number']" :holder="$account['holder']" />
                            @endforeach
                        </ul>
                    @endif
                </section>

                {{-- ignore.self: JS menyembunyikan panel saat tagihan kedaluwarsa --}}
                <section class="card p-5 sm:p-6" aria-labelledby="bukti-title" data-proof-panel wire:ignore.self>
                    <h2 id="bukti-title" class="text-lg font-bold text-ink">Unggah bukti transfer</h2>
                    <p class="mt-1 text-sm text-ink-muted">Foto atau screenshot struk/riwayat transfer.</p>

                    <form class="mt-5 space-y-5" wire:submit="submit" novalidate x-data="paymentProofUpload"
                          x-on:payment-proof-submitted.window="sent = true" x-on:payment-bill-renewed.window="reset()">
                        <div x-on:change.capture="check($event)"
                             x-on:livewire-upload-start="start()"
                             x-on:livewire-upload-progress="progress = $event.detail.progress"
                             x-on:livewire-upload-finish="end('File terunggah.')"
                             x-on:livewire-upload-error="end('Unggahan gagal.')"
                             x-on:livewire-upload-cancel="end('Unggahan dibatalkan.')">
                            <input id="bukti-file" type="file" class="peer sr-only" x-ref="input" wire:model="form.proof"
                                   accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf"
                                   aria-describedby="bukti-hint bukti-client-error bukti-error"
                                   x-bind:aria-invalid="clientError || @js($errors->has('form.proof')) ? 'true' : null">

                            @if ($form->proof)
                                <div class="flex flex-col gap-4 rounded-md border border-line p-4 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand-500 sm:flex-row sm:items-center">
                                    @if ($form->proof->isPreviewable())
                                        <img src="{{ $form->proof->temporaryUrl() }}" alt="Pratinjau bukti transfer"
                                             class="max-h-48 w-full rounded-md bg-surface-muted object-contain sm:w-40">
                                    @else
                                        <span class="grid size-16 shrink-0 place-items-center rounded-md bg-surface-muted text-ink-muted">
                                            <x-icon name="file-text" class="size-8" />
                                        </span>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate font-semibold text-ink">{{ $form->proof->getClientOriginalName() }}</p>
                                        <p class="text-xs text-ink-subtle">{{ number_format($form->proof->getSize() / 1024, 0, ',', '.') }} KB</p>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <label for="bukti-file" class="btn-outline cursor-pointer px-3 py-1.5 text-xs">
                                                <x-icon name="refresh" class="size-4" />
                                                Ganti file
                                            </label>
                                            <button type="button" class="btn-ghost px-3 py-1.5 text-xs" wire:click="$set('form.proof', null)" x-bind:disabled="uploading">
                                                <x-icon name="trash" class="size-4" />
                                                Hapus
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <label for="bukti-file"
                                       class="flex cursor-pointer flex-col items-center rounded-md border-2 border-dashed border-line-strong px-4 py-8 text-center transition-colors hover:border-brand-500 hover:bg-brand-soft peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand-500"
                                       x-bind:class="dragging && 'border-brand-500 bg-brand-soft'"
                                       x-on:dragover.prevent="dragging = true" x-on:dragleave="dragging = false" x-on:drop.prevent="drop($event)">
                                    <span class="grid size-12 place-items-center rounded-full bg-surface-muted text-ink-muted">
                                        <x-icon name="upload" class="size-6" />
                                    </span>
                                    <span class="mt-3 font-semibold text-ink">Klik untuk pilih file</span>
                                    <span class="text-sm text-ink-muted">atau seret file ke sini</span>
                                </label>
                            @endif

                            {{-- Progres upload --}}
                            <div class="mt-3" hidden x-bind:hidden="!uploading">
                                <div class="flex justify-between text-xs text-ink-muted">
                                    <span>Mengunggah…</span>
                                    <span x-text="progress + '%'"></span>
                                </div>
                                <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-surface-muted" role="progressbar" aria-label="Progres unggah" aria-valuemin="0" aria-valuemax="100" x-bind:aria-valuenow="progress">
                                    <div class="h-full rounded-full bg-brand-600 transition-[width]" x-bind:style="{ width: progress + '%' }"></div>
                                </div>
                            </div>
                            <p class="sr-only" aria-live="polite" x-text="status"></p>

                            <p id="bukti-hint" class="form-hint">Format JPG, PNG, atau PDF. Maksimal 2 MB.</p>
                            <p id="bukti-client-error" class="form-error" role="alert" hidden x-bind:hidden="!clientError" x-text="clientError"></p>
                            @error('form.proof')
                                <p id="bukti-error" class="form-error" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="bukti-pengirim" class="form-label">Nama pengirim <span class="font-normal text-ink-subtle">(opsional)</span></label>
                            <input id="bukti-pengirim" type="text" class="form-input" maxlength="100" autocomplete="name"
                                   wire:model="form.senderName" aria-describedby="bukti-pengirim-hint bukti-pengirim-error"
                                   @error('form.senderName') aria-invalid="true" @enderror>
                            <p id="bukti-pengirim-hint" class="form-hint">Isi bila nama rekening pengirim berbeda dengan nama pemesan.</p>
                            @error('form.senderName')
                                <p id="bukti-pengirim-error" class="form-error" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="btn-primary w-full py-3" x-bind:disabled="uploading || sent || !$wire.form.proof">
                            <span wire:loading.remove wire:target="submit" x-bind:hidden="sent">
                                <x-icon name="upload" class="inline size-5 align-[-0.3em]" />
                                Kirim Bukti Pembayaran
                            </span>
                            <span wire:loading wire:target="submit">Mengirim…</span>
                            <span hidden x-bind:hidden="!sent">Bukti terkirim, mengalihkan…</span>
                        </button>
                    </form>
                </section>

                <section class="card p-5 sm:p-6" aria-labelledby="pemesan-title" wire:ignore>
                    <h2 id="pemesan-title" class="text-base font-bold text-ink">Pemesan</h2>
                    <p class="mt-2 font-semibold text-ink" data-contact-name></p>
                    <p class="text-sm text-ink-muted" data-contact-detail></p>
                </section>
            </div>

            <aside class="lg:sticky lg:top-24 lg:self-start" wire:ignore>
                {{-- Biaya layanan diambil JS dari ri_checkout --}}
                <x-cart-summary :service-fee="0" title="Pesananmu">
                    <p class="mt-4 text-center text-xs text-ink-subtle">Mode demo: tidak ada transaksi sungguhan.</p>
                </x-cart-summary>
            </aside>
        </div>
    </div>
</div>

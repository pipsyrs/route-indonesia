<footer class="mt-20 border-t border-line bg-surface print:hidden">
    <div class="container-page grid gap-10 py-12 md:grid-cols-[1.4fr_1fr_1fr]">
        <div class="max-w-sm">
            <x-logo />
            <p class="mt-4 text-sm leading-relaxed text-ink-muted">
                Tiket travel dan shuttle antarkota dari operator terverifikasi. Pilih kursi sendiri, bayar dengan cara yang Anda suka.
            </p>
        </div>

        <div>
            <h2 class="text-sm font-semibold text-ink">Layanan</h2>
            <ul class="mt-3 space-y-2 text-sm text-ink-muted">
                <li><a class="hover:text-brand-ink" href="{{ route('home') }}">Cari jadwal</a></li>
                <li><a class="hover:text-brand-ink" href="{{ route('catalog.index') }}">Katalog</a></li>
                <li><a class="hover:text-brand-ink" href="{{ route('order.check') }}">Cek pesanan</a></li>
            </ul>
        </div>

        <div>
            <h2 class="text-sm font-semibold text-ink">Bantuan</h2>
            <ul class="mt-3 space-y-2 text-sm text-ink-muted">
                <li class="flex items-center gap-2"><x-icon name="phone" class="size-4" /> <a class="hover:text-brand-ink" href="tel:+622150891234">(021) 5089-1234</a></li>
                <li class="flex items-center gap-2"><x-icon name="mail" class="size-4" /> <a class="hover:text-brand-ink" href="mailto:halo@routeindonesia.id">halo@routeindonesia.id</a></li>
            </ul>
        </div>
    </div>

    <div class="border-t border-line">
        <div class="container-page flex flex-col gap-4 py-6 text-xs text-ink-subtle sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ now()->year }} RouteIndonesia. Data jadwal dan harga di situs ini adalah contoh.</p>
            <ul class="flex flex-wrap gap-2" aria-label="Metode pembayaran yang didukung">
                @foreach (['BCA', 'Mandiri', 'BNI', 'BRI', 'GoPay', 'OVO', 'DANA', 'QRIS'] as $method)
                    <li class="chip border border-line bg-canvas text-ink-muted">{{ $method }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</footer>

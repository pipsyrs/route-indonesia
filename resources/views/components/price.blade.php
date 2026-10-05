@props(['amount'])
{{-- Format identik dengan formatRupiah() di resources/js/modules/utils/format.js --}}
<span {{ $attributes }}>Rp {{ number_format((int) $amount, 0, ',', '.') }}</span>

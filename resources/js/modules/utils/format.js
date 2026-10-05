const rupiahFormatter = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });

/** Identik dengan komponen Blade <x-price>: "Rp 150.000". */
export function formatRupiah(amount) {
    return `Rp ${rupiahFormatter.format(Math.round(amount))}`;
}

import { cartCount, cartTotals } from './cart';
import { formatRupiah } from './utils/format';

/** Isi <x-cart-summary>; `withItems` menampilkan daftar ringkas per item (checkout/pembayaran). */
export function renderCartSummary(root, cart, { withItems = false } = {}) {
    const summary = root.querySelector('[data-summary-total]')?.closest('section');
    if (!summary) {
        return null;
    }

    const fee = Number(summary.querySelector('[data-summary-fee]').dataset.fee);
    const totals = cartTotals(fee, cart);

    summary.querySelector('[data-summary-count]').textContent = String(cartCount(cart));
    summary.querySelector('[data-summary-subtotal]').textContent = formatRupiah(totals.subtotal);
    summary.querySelector('[data-summary-fee]').textContent = formatRupiah(totals.serviceFee);
    summary.querySelector('[data-summary-total]').textContent = formatRupiah(totals.total);

    const list = summary.querySelector('[data-summary-items]');
    list.replaceChildren();
    list.hidden = !withItems;
    if (withItems) {
        cart.items.forEach((item) => {
            const row = document.createElement('li');
            row.className = 'flex justify-between gap-4';
            const label = document.createElement('span');
            label.className = 'min-w-0 text-ink-muted';
            label.textContent = `${item.origin} → ${item.destination}, ${item.depart} × ${item.qty}`;
            const price = document.createElement('span');
            price.className = 'shrink-0 text-ink';
            price.textContent = formatRupiah(item.price * item.qty);
            row.append(label, price);
            list.append(row);
        });
    }

    return totals;
}

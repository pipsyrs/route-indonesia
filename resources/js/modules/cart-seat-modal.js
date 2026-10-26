import { getCart, isProductItem, setTripSeats } from './cart';
import { checkedSeats, enforceSeatLimit, renderSeatMap } from './utils/seat-map';

/** Modal denah kursi di keranjang; qty mengikuti jumlah kursi yang dipilih. */
export function initCartSeatModal(dialog, { onSaved } = {}) {
    const grid = dialog.querySelector('[data-seat-grid]');
    const count = dialog.querySelector('[data-seat-count]');
    const error = dialog.querySelector('[data-seat-error]');
    const save = dialog.querySelector('[data-seat-save]');
    let item = null;

    const showError = (message) => {
        error.textContent = message ?? '';
        error.hidden = !message;
    };
    const renderCount = () => {
        const total = checkedSeats(grid).length;
        count.textContent = String(total);
        save.disabled = total < 1;
    };

    dialog.addEventListener('modal:open', (event) => {
        const key = event.detail.trigger?.closest('[data-cart-item]')?.dataset.cartItem;
        item = getCart().items.find((entry) => entry.key === key && !isProductItem(entry)) ?? null;
        if (!item) {
            queueMicrotask(() => dialog.close());
            return;
        }

        dialog.querySelector('[data-seat-trip]').textContent = `${item.origin} → ${item.destination}, ${item.dateLabel} ${item.depart}`;
        dialog.querySelector('[data-seat-limit]').textContent = String(item.maxQty);
        renderSeatMap(grid, { layout: item.seatLayout, occupied: item.occupied, selected: item.seats });
        showError(null);
        renderCount();
    });

    grid.addEventListener('change', (event) => {
        const input = event.target.closest('[data-seat]');
        if (!input || !item) {
            return;
        }
        showError(enforceSeatLimit(input, grid, item.maxQty) ? null : `Maksimal ${item.maxQty} kursi per jadwal.`);
        renderCount();
    });

    save.addEventListener('click', () => {
        if (!item || !setTripSeats(item.key, checkedSeats(grid))) {
            showError('Kursi tidak valid. Pilih ulang kursi.');
            return;
        }
        const key = item.key;
        dialog.close();
        onSaved?.(key);
    });
}

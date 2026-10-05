import { LAST_ORDER_KEY } from './cart';
import { formatRupiah } from './utils/format';
import { readLocal, writeLocal } from './utils/storage';

export function initOrderSuccess(root) {
    const page = root.querySelector('[data-success-root]');
    if (!page) {
        return;
    }

    const order = readLocal(LAST_ORDER_KEY);
    if (!order?.bookingCode || !Array.isArray(order.items)) {
        location.replace(page.dataset.emptyUrl);
        return;
    }

    const set = (scope, name, value) => {
        scope.querySelector(`[data-field="${name}"]`).textContent = value;
    };

    set(page, 'email', order.contact?.email ?? 'email pemesan');
    set(page, 'code', order.bookingCode);
    set(page, 'subtotal', formatRupiah(order.subtotal ?? 0));
    set(page, 'fee', formatRupiah(order.serviceFee ?? 0));
    set(page, 'method', order.methodName ?? '-');
    set(page, 'total', formatRupiah(order.total ?? 0));
    page.querySelector('[data-code-copy]').dataset.copy = order.bookingCode;

    const template = root.querySelector('[data-success-item-template]');
    page.querySelector('[data-success-items]').replaceChildren(...order.items.map((item) => {
        const row = template.content.firstElementChild.cloneNode(true);
        const names = order.passengers?.find((entry) => entry.itemKey === item.key)?.names ?? [];
        set(row, 'route', `${item.origin} → ${item.destination} · ${item.qty} tiket`);
        set(row, 'schedule', `${item.dateLabel}, ${item.depart} - ${item.arrive} · ${item.operator}`);
        set(row, 'pickup', item.pickup ? `Jemput: ${item.pickup.name} (${item.pickup.time})` : '');
        set(row, 'names', names.join(', ') || `${item.qty} orang`);
        return row;
    }));

    page.querySelector('[data-print]').addEventListener('click', () => window.print());
    page.querySelector('[data-success-ready]').hidden = false;

    // Data pribadi cukup tampil sekali; simpan ringkasan saja untuk refresh.
    if (order.contact || order.passengers) {
        const { contact, passengers, ...summary } = order;
        writeLocal(LAST_ORDER_KEY, summary);
    }
}

import { LAST_ORDER_KEY, SESSION_KEY, isProductItem, isValidItem } from './cart';
import { AWAITING_CONFIRMATION } from './checkout-payment';
import { openModal } from './modal';
import { formatRupiah } from './utils/format';
import { readLocal, readSession, writeLocal, writeSession } from './utils/storage';

const GUEST_PROMPTED_KEY = 'ri_guest_prompted';

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

    // Pesanan lama tanpa status tetap tampil sebagai "berhasil".
    const isAwaiting = order.status === AWAITING_CONFIRMATION;
    page.querySelectorAll('[data-status-awaiting]').forEach((element) => {
        element.hidden = !isAwaiting;
    });
    page.querySelectorAll('[data-status-paid]').forEach((element) => {
        element.hidden = isAwaiting;
    });
    page.querySelector('[data-print]').hidden = isAwaiting;
    if (isAwaiting) {
        set(page, 'total-label', 'Total tagihan');
    }

    page.querySelectorAll('[data-field="email"]').forEach((element) => {
        element.textContent = order.contact?.email ?? 'email pemesan';
    });
    set(page, 'code', order.bookingCode);
    set(page, 'subtotal', formatRupiah(order.subtotal ?? 0));
    set(page, 'fee', formatRupiah(order.serviceFee ?? 0));
    set(page, 'method', order.methodName ?? '-');
    set(page, 'total', formatRupiah(order.total ?? 0));
    page.querySelector('[data-code-copy]').dataset.copy = order.bookingCode;

    const template = root.querySelector('[data-success-item-template]');
    page.querySelector('[data-success-items]').replaceChildren(...order.items.filter(isValidItem).map((item) => {
        const row = template.content.firstElementChild.cloneNode(true);
        if (isProductItem(item)) {
            set(row, 'route', `${item.name} · ${item.qty} pcs`);
            ['schedule', 'pickup', 'seats'].forEach((field) => set(row, field, ''));
            row.querySelector('[data-seat-line]').hidden = true;
            return row;
        }
        set(row, 'route', `${item.origin} → ${item.destination} · ${item.qty} tiket`);
        set(row, 'schedule', `${item.dateLabel}, ${item.depart} - ${item.arrive} · ${item.operator}`);
        set(row, 'pickup', item.pickup ? `Jemput: ${item.pickup.name} (${item.pickup.time})` : '');
        set(row, 'seats', item.seats.join(', '));
        return row;
    }));

    page.querySelector('[data-print]').addEventListener('click', () => window.print());
    page.querySelector('[data-success-ready]').hidden = false;
    promptGuest(order.bookingCode);

    // Data pribadi cukup tampil sekali; simpan ringkasan saja untuk refresh.
    if (order.contact) {
        const { contact, ...summary } = order;
        writeLocal(LAST_ORDER_KEY, summary);
    }
}

/** Tamu: ajak buat akun, cukup sekali per kode booking (reload tidak mengulang). */
function promptGuest(bookingCode) {
    const session = readLocal(SESSION_KEY);
    const isUser = typeof session?.name === 'string' && session.name.trim() !== '';
    if (isUser || readSession(GUEST_PROMPTED_KEY) === bookingCode) {
        return;
    }
    writeSession(GUEST_PROMPTED_KEY, bookingCode);
    openModal('success-guest-modal');
}

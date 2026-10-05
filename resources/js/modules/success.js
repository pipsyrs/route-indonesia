import { BOOKING_STORAGE_KEY } from './booking-form';
import { deadlineStorageKey } from './payment';
import { readSession, removeSession } from './utils/storage';

export function initSuccess(root) {
    const ticket = root.querySelector('[data-ticket-passengers]');
    const orderKey = ticket?.dataset.orderKey;
    const saved = readSession(BOOKING_STORAGE_KEY);

    if (saved && saved.orderKey === orderKey) {
        saved.passengers.forEach((passenger) => {
            const row = ticket.querySelector(`[data-ticket-passenger="${CSS.escape(passenger.seat)}"]`);
            if (row && passenger.name) {
                row.querySelector('[data-ticket-passenger-name]').textContent = passenger.name;
            }
        });
    }

    // Pesanan selesai: bersihkan data pribadi dari perangkat.
    removeSession(BOOKING_STORAGE_KEY);
    removeSession(deadlineStorageKey(orderKey));

    root.querySelector('[data-print]')?.addEventListener('click', () => window.print());
}

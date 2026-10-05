import { BOOKING_STORAGE_KEY } from './booking-form';
import { readSession, writeSession } from './utils/storage';

const WARNING_THRESHOLD_MS = 5 * 60 * 1000;

export const deadlineStorageKey = (orderKey) => `ri_deadline:${orderKey}`;

export function initPayment(root) {
    const form = root.querySelector('[data-payment-form]');
    if (!form) {
        return;
    }

    const orderKey = root.querySelector('[data-countdown]').dataset.orderKey;
    const saved = readSession(BOOKING_STORAGE_KEY);
    const booking = saved?.orderKey === orderKey ? saved : null;

    renderContactSummary(root, booking);
    initMethodPicker(root, form);
    initCountdown(root, form, orderKey);
}

function renderContactSummary(root, booking) {
    const section = root.querySelector('[data-contact-summary]');
    if (!booking) {
        return;
    }

    section.querySelector('[data-contact-name]').textContent = booking.contact.name;
    section.querySelector('[data-contact-detail]').textContent = `${booking.contact.email}, ${booking.contact.phone}`;
    section.hidden = false;
}

function initMethodPicker(root, form) {
    const payButton = form.querySelector('[data-pay-submit]');
    const instructions = root.querySelectorAll('[data-instruction]');

    const render = () => {
        const selected = form.querySelector('[data-method]:checked');
        const group = selected?.dataset.methodGroup ?? 'none';
        instructions.forEach((panel) => {
            panel.hidden = panel.dataset.instruction !== group;
        });
        payButton.disabled = !selected || form.dataset.expired === 'true';
    };

    form.addEventListener('change', render);
    form.addEventListener('submit', (event) => {
        if (payButton.disabled) {
            event.preventDefault();
            return;
        }
        // Simulasi proses pembayaran sebelum pindah ke halaman sukses.
        payButton.disabled = true;
        payButton.textContent = 'Memproses...';
    });
    render();
}

function initCountdown(root, form, orderKey) {
    const banner = root.querySelector('[data-countdown]');
    const value = banner.querySelector('[data-countdown-value]');
    const deadline = resolveDeadline(orderKey, banner.dataset.deadline);

    const tick = () => {
        const remainingMs = Math.max(0, deadline - Date.now());
        const minutes = Math.floor(remainingMs / 60000);
        const seconds = Math.floor((remainingMs % 60000) / 1000);
        value.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

        const isWarning = remainingMs <= WARNING_THRESHOLD_MS;
        banner.classList.toggle('bg-warn-soft', isWarning);
        banner.classList.toggle('text-warn-ink', isWarning);
        banner.classList.toggle('bg-brand-soft', !isWarning);
        banner.classList.toggle('text-brand-ink', !isWarning);

        if (remainingMs === 0) {
            expire(root, form, banner);
            clearInterval(timer);
        }
    };

    const timer = setInterval(tick, 1000);
    tick();
}

/** Deadline pertama disimpan per pesanan agar refresh tidak mereset hitung mundur. */
function resolveDeadline(orderKey, serverDeadline) {
    const key = deadlineStorageKey(orderKey);
    const stored = readSession(key);
    if (typeof stored === 'number') {
        return stored;
    }

    const deadline = Date.parse(serverDeadline);
    writeSession(key, deadline);

    return deadline;
}

function expire(root, form, banner) {
    form.dataset.expired = 'true';
    form.querySelector('[data-pay-submit]').disabled = true;
    form.querySelectorAll('[data-method]').forEach((input) => {
        input.disabled = true;
    });
    banner.hidden = true;
    const expired = root.querySelector('[data-countdown-expired]');
    expired.classList.remove('hidden');
    expired.classList.add('flex');
}

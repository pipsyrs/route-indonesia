import { CART_KEY, CHECKOUT_KEY, LAST_ORDER_KEY, PAYMENT_KEY, cartTotals, clearCart, getCart } from './cart';
import { renderCartSummary } from './cart-summary';
import { formatRupiah } from './utils/format';
import { readLocal, removeLocal, writeLocal } from './utils/storage';

const PAYMENT_WINDOW_MS = 30 * 60 * 1000;
const WARNING_THRESHOLD_MS = 5 * 60 * 1000;
const CODE_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
const VA_PREFIX = { bca_va: '8808', bni_va: '9881', mandiri_va: '8900' };

export function initCheckoutPayment(root) {
    const page = root.querySelector('[data-payment-root]');
    if (!page) {
        return;
    }

    const cart = getCart();
    const checkout = readLocal(CHECKOUT_KEY);
    if (!checkout || cart.items.length === 0 || !matchesCheckout(cart, checkout)) {
        location.replace(page.dataset.backUrl);
        return;
    }
    // Keranjang diubah di tab lain: tagihan ini tidak berlaku.
    window.addEventListener('storage', (event) => {
        if (event.key === CART_KEY || event.key === CHECKOUT_KEY || event.key === null) {
            location.replace(page.dataset.backUrl);
        }
    });

    const methods = readJson(root, '[data-payment-methods]') ?? [];
    const method = methods.find((item) => item.id === checkout.methodId);
    if (!method) {
        location.replace(page.dataset.backUrl);
        return;
    }

    let payment = resolvePayment(checkout, method);
    let timer = null;

    const renderAll = () => {
        renderBill(root, payment, checkout, cart);
        clearInterval(timer);
        timer = startCountdown(root, payment, () => {
            payment = { ...payment, status: 'expired' };
            writeLocal(PAYMENT_KEY, payment);
            renderExpired(root, true);
        });
    };

    root.querySelector('[data-payment-renew]').addEventListener('click', () => {
        payment = createPayment(checkout, method);
        writeLocal(PAYMENT_KEY, payment);
        renderAll();
        root.querySelector('[data-countdown]').focus();
    });

    root.querySelector('[data-pay-confirm]').addEventListener('click', (event) => {
        if (payment.status !== 'pending') {
            return;
        }
        event.currentTarget.disabled = true;
        writeLocal(LAST_ORDER_KEY, {
            bookingCode: payment.bookingCode,
            items: getCart().items,
            contact: checkout.contact,
            passengers: checkout.passengers,
            methodName: payment.methodName,
            subtotal: checkout.subtotal,
            serviceFee: checkout.serviceFee,
            total: checkout.total,
            paidAt: new Date().toISOString(),
        });
        removeLocal(CHECKOUT_KEY);
        removeLocal(PAYMENT_KEY);
        clearCart();
        location.replace(page.dataset.successUrl);
    });

    root.querySelector('[data-payment-loading]').hidden = true;
    root.querySelector('[data-payment-ready]').hidden = false;
    renderAll();
}

/** Isi & total keranjang harus sama persis dengan snapshot checkout. */
function matchesCheckout(cart, checkout) {
    const passengers = Array.isArray(checkout.passengers) ? checkout.passengers : [];
    const sameItems = passengers.length === cart.items.length
        && cart.items.every((item) => passengers.find((entry) => entry.itemKey === item.key)?.names?.length === item.qty);

    return sameItems && cartTotals(checkout.serviceFee ?? 0, cart).total === checkout.total;
}

/** Pakai ulang tagihan yang masih berlaku untuk metode & total yang sama. */
function resolvePayment(checkout, method) {
    const saved = readLocal(PAYMENT_KEY);
    const isReusable = saved
        && saved.methodId === method.id
        && saved.total === checkout.total
        && saved.status === 'pending'
        && Date.parse(saved.deadline) > Date.now();
    if (isReusable) {
        return saved;
    }

    const payment = createPayment(checkout, method);
    writeLocal(PAYMENT_KEY, payment);

    return payment;
}

function createPayment(checkout, method) {
    const bookingCode = `RI-${randomString(CODE_ALPHABET, 6)}`;

    return {
        bookingCode,
        methodId: method.id,
        methodName: method.name,
        type: method.type,
        vaNumber: method.type === 'va' ? `${VA_PREFIX[method.id] ?? '8808'}${randomString('0123456789', 12)}`.replace(/(\d{4})(?=\d)/g, '$1 ') : null,
        qrisPayload: method.type === 'qris' ? `DUMMY-QRIS-${bookingCode}` : null,
        total: checkout.total,
        deadline: new Date(Date.now() + PAYMENT_WINDOW_MS).toISOString(),
        status: 'pending',
    };
}

function renderBill(root, payment, checkout, cart) {
    const text = (selector, value) => {
        root.querySelector(selector).textContent = value;
    };

    text('[data-booking-code]', payment.bookingCode);
    text('[data-method-name]', payment.methodName);
    text('[data-payment-total]', formatRupiah(payment.total));
    text('[data-contact-name]', checkout.contact.name);
    text('[data-contact-detail]', `${checkout.contact.email}, ${checkout.contact.phone}`);

    const vaPanel = root.querySelector('[data-va-panel]');
    vaPanel.hidden = payment.type !== 'va';
    if (payment.vaNumber) {
        text('[data-va-number]', payment.vaNumber);
        root.querySelector('[data-va-copy]').dataset.copy = payment.vaNumber.replace(/\s/g, '');
    }

    const qrisPanel = root.querySelector('[data-qris-panel]');
    qrisPanel.hidden = payment.type !== 'qris';
    if (payment.qrisPayload) {
        text('[data-qris-payload]', payment.qrisPayload);
        drawQrisPlaceholder(root.querySelector('[data-qris-art]'), payment.qrisPayload);
    }

    root.querySelectorAll('[data-instruction]').forEach((panel) => {
        panel.hidden = panel.dataset.instruction !== payment.methodId;
    });

    // Biaya layanan mengikuti snapshot checkout.
    root.querySelector('[data-summary-fee]').dataset.fee = String(checkout.serviceFee ?? 0);
    renderCartSummary(root, cart, { withItems: true });

    renderExpired(root, payment.status === 'expired');
}

function renderExpired(root, isExpired) {
    root.querySelector('[data-countdown]').hidden = isExpired;
    root.querySelector('[data-payment-expired]').hidden = !isExpired;
    root.querySelector('[data-pay-confirm]').disabled = isExpired;
}

function startCountdown(root, payment, onExpire) {
    const banner = root.querySelector('[data-countdown]');
    const value = root.querySelector('[data-countdown-value]');
    const deadline = Date.parse(payment.deadline);

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
            clearInterval(timer);
            onExpire();
        }
    };

    if (payment.status !== 'pending') {
        return null;
    }
    const timer = setInterval(tick, 1000);
    tick();

    return timer;
}

/** Pola kotak deterministik dari payload; hanya dekorasi. */
function drawQrisPlaceholder(container, payload) {
    let seed = [...payload].reduce((hash, char) => (hash * 31 + char.charCodeAt(0)) >>> 0, 7);
    const cells = Array.from({ length: 64 }, () => {
        seed = (seed * 1103515245 + 12345) >>> 0;
        const cell = document.createElement('span');
        cell.className = (seed >>> 16) % 2 === 0 ? 'rounded-[2px] bg-ink' : 'rounded-[2px] bg-transparent';
        return cell;
    });
    container.replaceChildren(...cells);
}

function randomString(alphabet, length) {
    const bytes = crypto.getRandomValues(new Uint32Array(length));

    return [...bytes].map((byte) => alphabet[byte % alphabet.length]).join('');
}

function readJson(root, selector) {
    try {
        return JSON.parse(root.querySelector(selector)?.textContent ?? 'null');
    } catch {
        return null;
    }
}

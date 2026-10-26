import { CART_KEY, CHECKOUT_KEY, LAST_ORDER_KEY, PAYMENT_KEY, allSeatsComplete, cartSignature, cartTotals, clearCart, getCart } from './cart';
import { renderCartSummary } from './cart-summary';
import { formatRupiah } from './utils/format';
import { readLocal, removeLocal, writeLocal } from './utils/storage';

const PAYMENT_WINDOW_MS = 30 * 60 * 1000;
const WARNING_THRESHOLD_MS = 5 * 60 * 1000;
const CODE_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
// Satu-satunya metode: transfer manual, dikonfirmasi admin.
const METHOD = { id: 'bank_transfer', name: 'Transfer Bank', type: 'bank_transfer' };
export const AWAITING_CONFIRMATION = 'awaiting_confirmation';

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

    let payment = resolvePayment(checkout, METHOD);
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
        payment = createPayment(checkout, METHOD);
        writeLocal(PAYMENT_KEY, payment);
        renderAll();
        // Buka kunci form bukti (state Alpine) untuk tagihan baru.
        window.dispatchEvent(new CustomEvent('payment-bill-renewed'));
        root.querySelector('[data-countdown]').focus();
    });

    // Dikirim komponen Livewire Payment setelah bukti valid diterima server.
    window.addEventListener('payment-proof-submitted', () => {
        if (payment.status !== 'pending') {
            return;
        }
        clearInterval(timer);
        // Cegah event ganda sebelum redirect selesai.
        payment = { ...payment, status: AWAITING_CONFIRMATION };
        writeLocal(LAST_ORDER_KEY, {
            bookingCode: payment.bookingCode,
            items: getCart().items,
            contact: checkout.contact,
            methodName: payment.methodName,
            subtotal: checkout.subtotal,
            serviceFee: checkout.serviceFee,
            total: checkout.total,
            status: AWAITING_CONFIRMATION,
            submittedAt: new Date().toISOString(),
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

/** Isi, kursi & total keranjang harus sama persis dengan snapshot checkout. */
function matchesCheckout(cart, checkout) {
    return checkout.signature === cartSignature(cart)
        && allSeatsComplete(cart)
        && cartTotals(checkout.serviceFee ?? 0, cart).total === checkout.total;
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
    text('[data-payment-total]', formatRupiah(payment.total));
    root.querySelector('[data-amount-copy]').dataset.copy = String(payment.total);
    text('[data-contact-name]', checkout.contact.name);
    text('[data-contact-detail]', `${checkout.contact.email}, ${checkout.contact.phone}`);

    // Biaya layanan mengikuti snapshot checkout.
    root.querySelector('[data-summary-fee]').dataset.fee = String(checkout.serviceFee ?? 0);
    renderCartSummary(root, cart, { withItems: true });

    renderExpired(root, payment.status === 'expired');
}

function renderExpired(root, isExpired) {
    root.querySelector('[data-countdown]').hidden = isExpired;
    root.querySelector('[data-payment-expired]').hidden = !isExpired;
    // Tagihan kedaluwarsa: bukti tidak bisa dikirim sampai tagihan dibuat ulang.
    root.querySelector('[data-proof-panel]').hidden = isExpired;
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

function randomString(alphabet, length) {
    const bytes = crypto.getRandomValues(new Uint32Array(length));

    return [...bytes].map((byte) => alphabet[byte % alphabet.length]).join('');
}

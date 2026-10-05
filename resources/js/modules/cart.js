import { readLocal, removeLocal, writeLocal } from './utils/storage';

export const CART_KEY = 'ri_cart';
export const CHECKOUT_KEY = 'ri_checkout';
export const PAYMENT_KEY = 'ri_payment';
export const LAST_ORDER_KEY = 'ri_last_order';
export const SESSION_KEY = 'ri_session';

const CART_VERSION = 1;

/** Keranjang rusak / versi lain dianggap kosong. */
export function getCart() {
    const cart = readLocal(CART_KEY);
    if (!cart || cart.v !== CART_VERSION || !Array.isArray(cart.items)) {
        return { v: CART_VERSION, items: [] };
    }

    return { v: CART_VERSION, items: cart.items.filter(isValidItem) };
}

/** @returns {{ added: number, qty: number, maxQty: number }} added = tiket yang benar-benar masuk */
export function addItem(item) {
    const cart = getCart();
    const existing = cart.items.find((entry) => entry.key === item.key);
    const before = existing?.qty ?? 0;
    const maxQty = existing?.maxQty ?? item.maxQty;
    const qty = Math.min(before + Math.max(1, Math.trunc(Number(item.qty)) || 1), maxQty);
    if (qty === before) {
        return { added: 0, qty, maxQty };
    }

    if (existing) {
        existing.qty = qty;
    } else {
        cart.items.push({ ...item, qty });
    }
    saveCart(cart);

    return { added: qty - before, qty, maxQty };
}

/** Sidik isi keranjang (key + qty) untuk mendeteksi perubahan. */
export function cartSignature(cart = getCart()) {
    return cart.items.map((item) => `${item.key}:${item.qty}`).sort().join(',');
}

export function updateQty(key, qty) {
    const cart = getCart();
    const item = cart.items.find((entry) => entry.key === key);
    if (item) {
        item.qty = clampQty(qty, item.maxQty);
        saveCart(cart);
    }
}

export function removeItem(key) {
    const cart = getCart();
    saveCart({ ...cart, items: cart.items.filter((entry) => entry.key !== key) });
}

export function clearCart() {
    removeLocal(CART_KEY);
    removeLocal(CHECKOUT_KEY);
    dispatchChange(0);
}

export function cartCount(cart = getCart()) {
    return cart.items.reduce((sum, item) => sum + item.qty, 0);
}

export function cartTotals(serviceFee, cart = getCart()) {
    const subtotal = cart.items.reduce((sum, item) => sum + item.price * item.qty, 0);
    const fee = cart.items.length > 0 ? serviceFee : 0;

    return { subtotal, serviceFee: fee, total: subtotal + fee };
}

/** Badge navbar: sinkron dengan perubahan di tab ini dan tab lain. */
export function initCartBadge(link) {
    const badge = link.querySelector('[data-cart-count]');
    const label = link.querySelector('[data-cart-count-label]');

    const render = (count) => {
        badge.textContent = count > 99 ? '99+' : String(count);
        badge.hidden = count === 0;
        label.textContent = String(count);
    };

    window.addEventListener('cart:change', (event) => render(event.detail.count));
    window.addEventListener('storage', (event) => {
        if (event.key === CART_KEY || event.key === null) {
            render(cartCount());
        }
    });
    render(cartCount());
}

function saveCart(cart) {
    writeLocal(CART_KEY, { v: CART_VERSION, items: cart.items });
    // Data checkout lama tidak lagi cocok dengan isi keranjang.
    removeLocal(CHECKOUT_KEY);
    dispatchChange(cartCount(cart));
}

function dispatchChange(count) {
    window.dispatchEvent(new CustomEvent('cart:change', { detail: { count } }));
}

function clampQty(qty, maxQty) {
    const value = Math.trunc(Number(qty)) || 1;

    return Math.min(Math.max(value, 1), maxQty);
}

const TEXT_FIELDS = ['key', 'operator', 'vehicle', 'origin', 'destination', 'date', 'dateLabel', 'depart', 'arrive'];

/** Data dari localStorage tidak dipercaya: tipe & rentang wajib benar. */
function isValidItem(item) {
    return item !== null
        && typeof item === 'object'
        && TEXT_FIELDS.every((field) => typeof item[field] === 'string')
        && Number.isFinite(item.price) && item.price > 0
        && Number.isInteger(item.maxQty) && item.maxQty >= 1
        && Number.isInteger(item.qty) && item.qty >= 1 && item.qty <= item.maxQty;
}

import { isSeatCode } from './utils/seat-map';
import { readLocal, removeLocal, writeLocal } from './utils/storage';

export const CART_KEY = 'ri_cart';
export const CHECKOUT_KEY = 'ri_checkout';
export const PAYMENT_KEY = 'ri_payment';
export const LAST_ORDER_KEY = 'ri_last_order';
export const SESSION_KEY = 'ri_session';

const CART_VERSION = 2;
export const PRODUCT_MAX_QTY = 10;

/** Keranjang rusak / versi lain dianggap kosong. */
export function getCart() {
    const cart = readLocal(CART_KEY);
    if (!cart || cart.v !== CART_VERSION || !Array.isArray(cart.items)) {
        return { v: CART_VERSION, items: [] };
    }

    return { v: CART_VERSION, items: cart.items.map(normalizeProduct).filter(isValidItem) };
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

/** Produk toko: key `product:<slug>` agar qty dijumlahkan untuk slug yang sama. */
export function addProduct({ slug, name, price, image }, qty) {
    return addItem({
        type: 'product',
        key: `product:${slug}`,
        id: slug,
        name,
        price,
        image: image || null,
        qty,
        maxQty: PRODUCT_MAX_QTY,
    });
}

export function isProductItem(item) {
    return item.type === 'product';
}

/** Sidik isi keranjang (key + qty + kursi) untuk mendeteksi perubahan. */
export function cartSignature(cart = getCart()) {
    return cart.items.map((item) => `${item.key}:${item.qty}:${(item.seats ?? []).join('.')}`).sort().join(',');
}

export function updateQty(key, qty) {
    const cart = getCart();
    const item = cart.items.find((entry) => entry.key === key);
    if (item) {
        item.qty = clampQty(qty, item.maxQty);
        if (!isProductItem(item)) {
            item.seats = item.seats.slice(0, item.qty);
        }
        saveCart(cart);
    }
}

/** Trip dari detail: kursi & qty diganti, bukan dijumlah. @returns {'added'|'updated'|null} null = tidak valid */
export function upsertTrip(item) {
    const seats = Array.isArray(item.seats) ? [...item.seats].sort(compareSeats) : [];
    const next = { ...item, seats, qty: seats.length };
    if (isProductItem(next) || !isValidItem(next)) {
        return null;
    }
    const cart = getCart();
    const index = cart.items.findIndex((entry) => entry.key === next.key);
    if (index >= 0) {
        cart.items[index] = { ...next, addedAt: cart.items[index].addedAt };
    } else {
        cart.items.push(next);
    }
    saveCart(cart);

    return index >= 0 ? 'updated' : 'added';
}

/** Kursi & qty diset bersama (qty = jumlah kursi). @returns {boolean} false bila tidak valid */
export function setTripSeats(key, seats) {
    const cart = getCart();
    const item = cart.items.find((entry) => entry.key === key);
    const sorted = Array.isArray(seats) ? [...seats].sort(compareSeats) : [];
    if (!item || isProductItem(item) || sorted.length < 1 || sorted.length > item.maxQty
        || !areValidSeats({ ...item, qty: sorted.length }, sorted)) {
        return false;
    }
    item.qty = sorted.length;
    item.seats = sorted;
    saveCart(cart);

    return true;
}

/** Kode kursi di denah (tanpa sopir & lorong). */
export function seatCodes(layout) {
    return layout.flat().filter(isSeatCode);
}

export function isSeatComplete(item) {
    return isProductItem(item) || item.seats.length === item.qty;
}

export function allSeatsComplete(cart = getCart()) {
    return cart.items.every(isSeatComplete);
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

const MAX_LAYOUT_ROWS = 15;
const MAX_LAYOUT_COLS = 6;

const compareSeats = (a, b) => Number(a) - Number(b);

function isValidLayout(layout) {
    return Array.isArray(layout) && layout.length >= 1 && layout.length <= MAX_LAYOUT_ROWS
        && layout.every((row) => Array.isArray(row) && row.length >= 1 && row.length <= MAX_LAYOUT_COLS
            && row.every((cell) => cell === null || typeof cell === 'string'));
}

/** Unik, <= qty, ada di denah, dan tidak terisi. */
function areValidSeats(item, seats) {
    const codes = seatCodes(item.seatLayout);

    return Array.isArray(seats)
        && seats.length <= item.qty
        && new Set(seats).size === seats.length
        && seats.every((seat) => typeof seat === 'string' && codes.includes(seat) && !item.occupied.includes(seat));
}

const TEXT_FIELDS = ['key', 'operator', 'vehicle', 'origin', 'destination', 'date', 'dateLabel', 'depart', 'arrive'];
const PRODUCT_FIELDS = ['key', 'id', 'name'];

/** Batas qty produk ditentukan kode, bukan localStorage; qty berlebih dijepit ke batas. */
function normalizeProduct(item) {
    if (item === null || typeof item !== 'object' || !isProductItem(item)) {
        return item;
    }

    return {
        ...item,
        maxQty: PRODUCT_MAX_QTY,
        qty: Number.isInteger(item.qty) ? Math.min(item.qty, PRODUCT_MAX_QTY) : item.qty,
    };
}

/** Data dari localStorage tidak dipercaya: tipe & rentang wajib benar. */
export function isValidItem(item) {
    if (item === null || typeof item !== 'object') {
        return false;
    }
    const hasFields = isProductItem(item)
        ? PRODUCT_FIELDS.every((field) => typeof item[field] === 'string')
            && (item.image === null || typeof item.image === 'string')
        : TEXT_FIELDS.every((field) => typeof item[field] === 'string');

    const isBaseValid = hasFields
        && Number.isFinite(item.price) && item.price > 0
        && Number.isInteger(item.maxQty) && item.maxQty >= 1
        && Number.isInteger(item.qty) && item.qty >= 1 && item.qty <= item.maxQty;
    if (!isBaseValid || isProductItem(item)) {
        return isBaseValid;
    }

    return isValidLayout(item.seatLayout)
        && Array.isArray(item.occupied) && item.occupied.every((seat) => typeof seat === 'string')
        && areValidSeats(item, item.seats);
}

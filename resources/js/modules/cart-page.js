import { getCart, isProductItem, removeItem, updateQty } from './cart';
import { renderCartSummary } from './cart-summary';
import { formatRupiah } from './utils/format';

export function initCartPage(root) {
    const list = root.querySelector('[data-cart-list]');
    const template = root.querySelector('[data-cart-item-template]');
    if (!list || !template) {
        return;
    }

    // Baris dirender ulang tiap perubahan; kembalikan fokus ke kontrol yang sama.
    let pendingFocus = null;

    const render = () => {
        const cart = getCart();
        const isEmpty = cart.items.length === 0;
        root.querySelector('[data-cart-empty]').hidden = !isEmpty;
        root.querySelector('[data-cart-filled]').hidden = isEmpty;

        list.replaceChildren(...cart.items.map((item) => buildRow(template, item)));
        renderCartSummary(root, cart);

        if (pendingFocus) {
            const row = [...list.querySelectorAll('[data-cart-item]')].find((el) => el.dataset.cartItem === pendingFocus.key);
            const target = row?.querySelector(pendingFocus.selector);
            (target && !target.disabled ? target : row?.querySelector('[data-qty-input]') ?? root.querySelector('[data-cart-heading]'))?.focus();
            pendingFocus = null;
        }
    };

    list.addEventListener('click', (event) => {
        const row = event.target.closest('[data-cart-item]');
        if (!row) {
            return;
        }
        const step = event.target.closest('[data-qty-step]');
        if (step) {
            pendingFocus = { key: row.dataset.cartItem, selector: `[data-qty-step="${step.dataset.qtyStep}"]` };
            const input = row.querySelector('[data-qty-input]');
            updateQty(row.dataset.cartItem, Number(input.value) + Number(step.dataset.qtyStep));
        } else if (event.target.closest('[data-remove]')) {
            pendingFocus = { key: null, selector: '' };
            removeItem(row.dataset.cartItem);
        }
    });
    list.addEventListener('change', (event) => {
        const input = event.target.closest('[data-qty-input]');
        if (input) {
            updateQty(input.closest('[data-cart-item]').dataset.cartItem, Number(input.value));
        }
    });

    window.addEventListener('cart:change', render);
    window.addEventListener('storage', render);
    render();
}

/** textContent saja agar data dari localStorage tidak pernah jadi HTML. */
function buildRow(template, item) {
    const row = template.content.firstElementChild.cloneNode(true);
    const set = (name, value) => {
        row.querySelector(`[data-field="${name}"]`).textContent = value;
    };

    row.dataset.cartItem = item.key;
    if (isProductItem(item)) {
        // Produk toko: tanpa info jadwal/operator.
        set('route', item.name);
        row.querySelector('[data-field="operator"]').hidden = true;
        row.querySelector('dl').hidden = true;
        set('price', `${formatRupiah(item.price)} / pcs`);
    } else {
        set('route', `${item.origin} → ${item.destination}`);
        set('operator', `${item.operator}, ${item.vehicle}`);
        set('date', item.dateLabel);
        set('time', `${item.depart} - ${item.arrive}`);
        set('pickup', item.pickup ? `${item.pickup.name} (${item.pickup.time})` : '-');
        set('price', `${formatRupiah(item.price)} / orang`);
    }
    set('line-total', formatRupiah(item.price * item.qty));

    const input = row.querySelector('[data-qty-input]');
    input.value = String(item.qty);
    input.max = String(item.maxQty);
    input.id = `qty-${item.key.replace(/[^\w-]/g, '-')}`;
    row.querySelector('[data-qty-label]').htmlFor = input.id;
    row.querySelector('[data-qty-step="-1"]').disabled = item.qty <= 1;
    row.querySelector('[data-qty-step="1"]').disabled = item.qty >= item.maxQty;

    return row;
}

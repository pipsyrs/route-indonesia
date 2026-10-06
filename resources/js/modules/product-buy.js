import { PRODUCT_MAX_QTY, addProduct } from './cart';
import { showToast } from './toast';
import { formatRupiah } from './utils/format';

/** Satu modal untuk semua tombol BUY; data produk dibaca dari data-* pemicu. */
export function initProductBuy(dialog) {
    const form = dialog.querySelector('form');
    const input = form.querySelector('[data-qty-input]');
    const error = form.querySelector('[data-qty-error]');
    let product = null;

    const setQty = (value) => {
        input.value = String(value);
        form.querySelector('[data-qty-step="-1"]').disabled = value <= 1;
        form.querySelector('[data-qty-step="1"]').disabled = value >= PRODUCT_MAX_QTY;
    };
    const showError = (message) => {
        error.textContent = message ?? '';
        error.hidden = !message;
        input.toggleAttribute('aria-invalid', Boolean(message));
    };

    dialog.addEventListener('modal:open', (event) => {
        const data = event.detail.trigger?.dataset ?? {};
        product = {
            slug: data.productSlug,
            name: data.productName,
            price: Number(data.productPrice),
            image: data.productImage || null,
        };
        dialog.querySelector('[data-product-name]').textContent = product.name;
        dialog.querySelector('[data-product-price]').textContent = formatRupiah(product.price);
        showError(null);
        setQty(1);
    });

    form.addEventListener('click', (event) => {
        const step = event.target.closest('[data-qty-step]');
        if (step) {
            const current = Math.trunc(Number(input.value)) || 1;
            setQty(Math.min(Math.max(current + Number(step.dataset.qtyStep), 1), PRODUCT_MAX_QTY));
            showError(null);
        }
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const qty = Number(input.value);
        if (!Number.isInteger(qty) || qty < 1 || qty > PRODUCT_MAX_QTY) {
            showError(dialog.dataset.msgInvalid);
            input.focus();
            return;
        }
        if (!product?.slug || !product.name || !(product.price > 0)) {
            return;
        }

        const result = addProduct(product, qty);
        dialog.close();
        if (result.added === 0) {
            showToast(dialog.dataset.msgMax);
        } else if (result.added < qty) {
            showToast(dialog.dataset.msgPartial.replace(':count', String(result.added)));
        } else {
            showToast(dialog.dataset.msgAdded.replace(':name', product.name));
        }
    });
}

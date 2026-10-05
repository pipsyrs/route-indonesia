import { CHECKOUT_KEY, PAYMENT_KEY, cartSignature, getCart } from './cart';
import { renderCartSummary } from './cart-summary';
import { showToast } from './toast';
import { readLocal, removeLocal, writeLocal } from './utils/storage';
import { bindLiveValidation, normalizePhone, validateForm } from './utils/validate';

export function initCheckout(root) {
    const form = root.querySelector('[data-checkout-form]');
    if (!form) {
        return;
    }

    const cart = getCart();
    if (cart.items.length === 0) {
        location.replace(form.dataset.emptyUrl);
        return;
    }

    let renderedSignature = cartSignature(cart);
    renderPassengerInputs(root, form, cart);
    renderCartSummary(root, cart, { withItems: true });
    restoreSaved(form, readLocal(CHECKOUT_KEY));
    initSameAsContact(form);
    bindLiveValidation(form);

    form.addEventListener('submit', (event) => {
        event.preventDefault();

        // Keranjang berubah (mis. di tab lain): render ulang field penumpang, jangan kirim.
        const latest = getCart();
        if (latest.items.length === 0) {
            location.replace(form.dataset.emptyUrl);
            return;
        }
        if (cartSignature(latest) !== renderedSignature) {
            const names = collectNames(form);
            renderedSignature = cartSignature(latest);
            renderPassengerInputs(root, form, latest);
            restorePassengerNames(form, names);
            renderCartSummary(root, latest, { withItems: true });
            showToast('Keranjang berubah. Periksa lagi data penumpang.');
            form.querySelector('[data-passenger-groups]').scrollIntoView({ block: 'start' });
            return;
        }

        if (!validateForm(form)) {
            return;
        }

        // Total dihitung ulang dari keranjang terbaru saat submit.
        const totals = renderCartSummary(root, latest, { withItems: true });
        const field = (name) => form.querySelector(`[data-field="${name}"]`).value.trim();

        writeLocal(CHECKOUT_KEY, {
            contact: {
                name: field('contact_name'),
                email: field('contact_email').toLowerCase(),
                phone: normalizePhone(field('contact_phone')),
            },
            passengers: latest.items.map((item) => ({
                itemKey: item.key,
                names: [...form.querySelectorAll('[data-passenger-name]')]
                    .filter((input) => input.dataset.itemKey === item.key)
                    .map((input) => input.value.trim()),
            })),
            methodId: form.querySelector('[name="method"]:checked').value,
            ...totals,
            createdAt: new Date().toISOString(),
        });
        // Metode/total bisa berubah; tagihan lama tidak dipakai lagi.
        removeLocal(PAYMENT_KEY);
        location.assign(form.dataset.nextUrl);
    });
}

function renderPassengerInputs(root, form, cart) {
    const container = form.querySelector('[data-passenger-groups]');
    const template = root.querySelector('[data-passenger-group-template]');
    let number = 0;

    container.replaceChildren(...cart.items.map((item, itemIndex) => {
        const group = template.content.firstElementChild.cloneNode(true);
        group.querySelector('[data-field="legend"]').textContent = `${item.origin} → ${item.destination}, ${item.dateLabel} ${item.depart}`;
        const inputs = group.querySelector('[data-passenger-inputs]');

        for (let seat = 0; seat < item.qty; seat++) {
            number += 1;
            const id = `penumpang-${itemIndex}-${seat}`;
            const wrapper = document.createElement('div');
            const label = document.createElement('label');
            label.className = 'form-label';
            label.htmlFor = id;
            label.textContent = `Penumpang ${number}`;
            const input = document.createElement('input');
            Object.assign(input, { id, type: 'text', className: 'form-input', maxLength: 60, autocomplete: 'off' });
            input.dataset.rule = 'name';
            input.dataset.passengerName = '';
            input.dataset.itemKey = item.key;
            input.setAttribute('aria-describedby', `${id}-error`);
            const error = document.createElement('p');
            Object.assign(error, { id: `${id}-error`, className: 'form-error', hidden: true });
            wrapper.append(label, input, error);
            inputs.append(wrapper);
        }

        return group;
    }));
}

function collectNames(form) {
    const names = {};
    form.querySelectorAll('[data-passenger-name]').forEach((input) => {
        (names[input.dataset.itemKey] ??= []).push(input.value);
    });

    return names;
}

function restorePassengerNames(form, names) {
    const counters = {};
    form.querySelectorAll('[data-passenger-name]').forEach((input) => {
        const index = counters[input.dataset.itemKey] ?? 0;
        counters[input.dataset.itemKey] = index + 1;
        input.value = names[input.dataset.itemKey]?.[index] ?? '';
    });
}

/** Kembali dari pembayaran: isi ulang form. */
function restoreSaved(form, saved) {
    if (!saved?.contact) {
        return;
    }
    form.querySelector('[data-field="contact_name"]').value = saved.contact.name ?? '';
    form.querySelector('[data-field="contact_email"]').value = saved.contact.email ?? '';
    form.querySelector('[data-field="contact_phone"]').value = saved.contact.phone ?? '';
    (saved.passengers ?? []).forEach(({ itemKey, names }) => {
        const inputs = [...form.querySelectorAll('[data-passenger-name]')].filter((input) => input.dataset.itemKey === itemKey);
        inputs.forEach((input, index) => {
            input.value = names?.[index] ?? '';
        });
    });
    const method = [...form.querySelectorAll('[name="method"]')].find((input) => input.value === saved.methodId);
    if (method) {
        method.checked = true;
    }
}

function initSameAsContact(form) {
    const checkbox = form.querySelector('[data-same-as-contact]');
    const contactName = form.querySelector('[data-field="contact_name"]');
    if (!checkbox) {
        return;
    }

    // Field penumpang bisa dirender ulang; cari setiap kali.
    const sync = () => {
        const firstPassenger = form.querySelector('[data-passenger-name]');
        if (checkbox.checked && firstPassenger) {
            firstPassenger.value = contactName.value;
        }
    };
    checkbox.addEventListener('change', sync);
    contactName.addEventListener('input', sync);
}

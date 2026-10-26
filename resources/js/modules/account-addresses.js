import { showToast } from './toast';
import { ADDRESS_LABELS, formatRegion, readAddresses, saveAddress, writeAddresses } from './utils/address-book';
import { bindLiveValidation, normalizePhone, validateForm } from './utils/validate';

/** Buku alamat mode demo: hanya di localStorage perangkat ini, dirender via textContent (aman XSS). */
const POSTAL_LENGTH = 5;

export function initAccountAddresses(root) {
    const list = root.querySelector('[data-address-list]');
    const empty = root.querySelector('[data-address-empty]');
    const count = root.querySelector('[data-address-count]');
    const template = root.querySelector('[data-address-template]');
    const form = root.querySelector('[data-address-form]');
    const dialog = form.closest('dialog');
    const postal = form.querySelector('[data-postal]');

    let addresses = readAddresses() ?? seedAddresses(root.dataset);
    save();
    render();

    list.addEventListener('click', (event) => {
        const button = event.target.closest('[data-action]');
        const id = button?.closest('[data-address-id]')?.dataset.addressId;
        if (!id) {
            return;
        }
        if (button.dataset.action === 'set-primary') {
            addresses = addresses.map((item) => ({ ...item, primary: item.id === id }));
            commit('Alamat utama diperbarui');
            list.querySelector('[data-primary]')?.focus();
        } else if (button.dataset.action === 'delete') {
            const removed = addresses.find((item) => item.id === id);
            addresses = addresses.filter((item) => item.id !== id);
            if (removed?.primary && addresses.length > 0) {
                addresses[0] = { ...addresses[0], primary: true };
            }
            commit('Alamat dihapus');
            root.querySelector('[data-modal-open]')?.focus();
        }
    });

    dialog.addEventListener('modal:open', () => {
        form.reset();
        form.querySelectorAll('[aria-invalid]').forEach((input) => input.removeAttribute('aria-invalid'));
        form.querySelectorAll('.form-error').forEach((error) => { error.hidden = true; });
    });

    bindLiveValidation(form);
    postal.addEventListener('input', () => {
        postal.value = postal.value.replace(/[^0-9]/g, '').slice(0, POSTAL_LENGTH);
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!validateForm(form)) {
            return;
        }
        const result = saveAddress({
            label: form.querySelector('[name="address-label"]:checked')?.value ?? ADDRESS_LABELS[0],
            name: field(form, 'name'),
            phone: normalizePhone(field(form, 'phone')),
            address: field(form, 'address'),
            province: field(form, 'province'),
            city: field(form, 'city'),
            district: field(form, 'district'),
            postal: field(form, 'postal'),
        });
        if (result === 'duplicate') {
            showToast('Alamat ini sudah ada di buku alamat');
            return;
        }
        addresses = readAddresses() ?? [];
        dialog.close();
        commit('Alamat ditambahkan');
    });

    function commit(message) {
        save();
        render();
        showToast(message);
    }

    function save() {
        writeAddresses(addresses);
    }

    function render() {
        // Alamat utama selalu di urutan pertama.
        const sorted = [...addresses].sort((a, b) => Number(b.primary) - Number(a.primary));
        list.replaceChildren(...sorted.map((item) => renderCard(template, item)));
        list.hidden = sorted.length === 0;
        empty.hidden = sorted.length > 0;
        count.textContent = `${sorted.length} alamat`;
    }
}

function renderCard(template, item) {
    const card = template.content.firstElementChild.cloneNode(true);
    const slot = (name) => card.querySelector(`[data-slot="${name}"]`);

    card.dataset.addressId = item.id;
    card.toggleAttribute('data-primary', item.primary);
    slot('label').textContent = item.label;
    slot('primary').hidden = !item.primary;
    slot('name').textContent = item.name;
    slot('phone').textContent = item.phone;
    slot('address').textContent = item.address;
    slot('region').textContent = formatRegion(item);

    card.querySelector('[data-action="set-primary"]').hidden = item.primary;
    card.querySelector('[data-action="delete"]').setAttribute('aria-label', `Hapus alamat ${item.label}, ${item.name}`);

    return card;
}

function field(form, name) {
    return form.querySelector(`[data-field="${name}"]`).value.trim();
}

function seedAddresses({ seedName = 'Pengguna', seedPhone = '081234567890' }) {
    return [
        { id: 'seed-home', label: 'Rumah', name: seedName, phone: seedPhone, address: 'Jl. Kaliurang Km 5 No. 12, Caturtunggal, Depok', province: 'DI Yogyakarta', city: 'Sleman', district: 'Depok', postal: '55281', primary: true },
        { id: 'seed-office', label: 'Kantor', name: seedName, phone: seedPhone, address: 'Jl. Malioboro No. 56, Suryatmajan, Danurejan', province: 'DI Yogyakarta', city: 'Yogyakarta', district: 'Danurejan', postal: '55213', primary: false },
    ];
}

import { showToast } from './toast';
import { readLocal, writeLocal } from './utils/storage';
import { bindLiveValidation, normalizePhone, validateForm } from './utils/validate';

/** Buku alamat mode demo: hanya di localStorage perangkat ini, dirender via textContent (aman XSS). */
const STORAGE_KEY = 'ri_addresses';
const LABELS = ['Rumah', 'Kantor', 'Lainnya'];
const LIMITS = { name: 60, phone: 16, address: 200, city: 60, postal: 5 };
const POSTAL_PATTERN = /^[0-9]{5}$/;

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
        postal.value = postal.value.replace(/[^0-9]/g, '').slice(0, LIMITS.postal);
        if (postal.getAttribute('aria-invalid') === 'true') {
            validatePostal(postal);
        }
    });
    postal.addEventListener('blur', () => validatePostal(postal));

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const formValid = validateForm(form);
        const postalValid = validatePostal(postal);
        if (!formValid || !postalValid) {
            if (formValid) {
                postal.focus();
            }
            return;
        }
        addresses.push({
            id: `${Date.now().toString(36)}${Math.random().toString(36).slice(2, 8)}`,
            label: form.querySelector('[name="address-label"]:checked')?.value ?? LABELS[0],
            name: field(form, 'name'),
            phone: normalizePhone(field(form, 'phone')),
            address: field(form, 'address'),
            city: field(form, 'city'),
            postal: field(form, 'postal'),
            // Alamat pertama otomatis jadi utama.
            primary: addresses.length === 0,
        });
        dialog.close();
        commit('Alamat ditambahkan');
    });

    function commit(message) {
        save();
        render();
        showToast(message);
    }

    function save() {
        writeLocal(STORAGE_KEY, addresses);
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
    slot('region').textContent = `${item.city} ${item.postal}`;

    card.querySelector('[data-action="set-primary"]').hidden = item.primary;
    card.querySelector('[data-action="delete"]').setAttribute('aria-label', `Hapus alamat ${item.label}, ${item.name}`);

    return card;
}

function validatePostal(input) {
    const valid = POSTAL_PATTERN.test(input.value.trim());
    const error = document.getElementById(input.getAttribute('aria-describedby'));
    input.setAttribute('aria-invalid', String(!valid));
    error.hidden = valid;
    error.textContent = valid ? '' : 'Kode pos harus 5 digit angka.';

    return valid;
}

function field(form, name) {
    return form.querySelector(`[data-field="${name}"]`).value.trim();
}

/** null = belum pernah disimpan (perlu seed); data rusak dibuang, bukan dipercaya. */
function readAddresses() {
    const stored = readLocal(STORAGE_KEY);
    if (stored === null) {
        return null;
    }
    if (!Array.isArray(stored)) {
        return [];
    }

    const valid = stored.filter((item) => item !== null && typeof item === 'object'
        && typeof item.id === 'string' && LABELS.includes(item.label)
        && Object.keys(LIMITS).every((key) => typeof item[key] === 'string' && item[key] !== '' && item[key].length <= LIMITS[key]));

    let hasPrimary = false;
    const addresses = valid.map((item) => {
        const primary = item.primary === true && !hasPrimary;
        hasPrimary ||= primary;

        return { id: item.id, label: item.label, name: item.name, phone: item.phone, address: item.address, city: item.city, postal: item.postal, primary };
    });
    if (!hasPrimary && addresses.length > 0) {
        addresses[0].primary = true;
    }

    return addresses;
}

function seedAddresses({ seedName = 'Pengguna', seedPhone = '081234567890' }) {
    return [
        { id: 'seed-home', label: 'Rumah', name: seedName, phone: seedPhone, address: 'Jl. Kaliurang Km 5 No. 12, Caturtunggal, Depok', city: 'Sleman', postal: '55281', primary: true },
        { id: 'seed-office', label: 'Kantor', name: seedName, phone: seedPhone, address: 'Jl. Malioboro No. 56, Suryatmajan, Danurejan', city: 'Yogyakarta', postal: '55213', primary: false },
    ];
}

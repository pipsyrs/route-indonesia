import { Livewire } from '../../../vendor/livewire/livewire/dist/livewire.esm';
import { CHECKOUT_KEY, PAYMENT_KEY, SESSION_KEY, allSeatsComplete, cartSignature, getCart, isProductItem } from './cart';
import { renderCartSummary } from './cart-summary';
import { showToast } from './toast';
import { formatRegion, readAddresses, saveAddress } from './utils/address-book';
import { readLocal, removeLocal, writeLocal } from './utils/storage';
import { bindLiveValidation, inputError, normalizeInstagram, normalizePhone, validateForm, validateInput } from './utils/validate';

// data-field checkout -> key buku alamat.
const ADDRESS_FIELDS = { address_street: 'address', address_province: 'province', address_city: 'city', address_district: 'district', address_postal: 'postal' };

const SAVED_FIELDS = {
    contact_name: (saved) => saved.contact?.name,
    contact_email: (saved) => saved.contact?.email,
    contact_phone: (saved) => saved.contact?.phone,
    address_street: (saved) => saved.address?.street,
    address_province: (saved) => saved.address?.province,
    address_city: (saved) => saved.address?.city,
    address_district: (saved) => saved.address?.district,
    address_postal: (saved) => saved.address?.postalCode,
    referral_source: (saved) => saved.referralSource,
    instagram: (saved) => saved.instagram,
    referral_code: (saved) => saved.referralCode,
};

export function initCheckout(root) {
    const form = root.querySelector('[data-checkout-form]');
    if (!form) {
        return;
    }

    // Keranjang kosong atau kursi belum lengkap: kembali ke keranjang.
    const cart = getCart();
    if (cart.items.length === 0 || !allSeatsComplete(cart)) {
        location.replace(form.dataset.emptyUrl);
        return;
    }

    let renderedSignature = cartSignature(cart);
    let isSubmitting = false;
    const submit = form.querySelector('[data-checkout-submit]');
    renderCartSummary(root, cart, { withItems: true });
    const saved = readLocal(CHECKOUT_KEY);
    restoreSaved(form, saved);
    const addressPicker = initAddressPicker(root, form, saved?.contact ? saved.address : null);
    bindLiveValidation(form);

    // Keranjang berubah (mis. di tab lain): render ulang ringkasan, jangan kirim.
    const isCartChanged = (latest) => {
        if (latest.items.length === 0 || !allSeatsComplete(latest)) {
            location.replace(form.dataset.emptyUrl);
            return true;
        }
        if (cartSignature(latest) !== renderedSignature) {
            renderedSignature = cartSignature(latest);
            renderCartSummary(root, latest, { withItems: true });
            showToast('Keranjang berubah. Periksa lagi pesananmu.');
            return true;
        }

        return false;
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (isSubmitting || isCartChanged(getCart()) || !validateForm(form)) {
            return;
        }

        showSeatError(form, null);
        isSubmitting = true;
        submit.disabled = true;
        const result = await checkSeats(root, getCart());
        isSubmitting = false;
        submit.disabled = false;
        if (!result.valid) {
            showSeatError(form, result.message);
            return;
        }

        const latest = getCart();
        if (isCartChanged(latest)) {
            return;
        }

        // Total dihitung ulang dari keranjang terbaru saat submit.
        const totals = renderCartSummary(root, latest, { withItems: true });
        const field = (name) => form.querySelector(`[data-field="${name}"]`).value.trim();
        if (addressPicker.shouldSave()) {
            saveAddress({
                label: 'Lainnya',
                name: field('contact_name'),
                phone: normalizePhone(field('contact_phone')),
                ...Object.fromEntries(Object.entries(ADDRESS_FIELDS).map(([name, key]) => [key, field(name)])),
            });
        }

        writeLocal(CHECKOUT_KEY, {
            contact: {
                name: field('contact_name'),
                email: field('contact_email').toLowerCase(),
                phone: normalizePhone(field('contact_phone')),
            },
            address: {
                street: field('address_street'),
                province: field('address_province'),
                city: field('address_city'),
                district: field('address_district'),
                postalCode: field('address_postal'),
            },
            referralSource: field('referral_source'),
            instagram: normalizeInstagram(field('instagram')) || null,
            referralCode: field('referral_code').toUpperCase() || null,
            signature: cartSignature(latest),
            methodId: form.querySelector('[name="method"]:checked').value,
            ...totals,
            createdAt: new Date().toISOString(),
        });
        // Metode/total bisa berubah; tagihan lama tidak dipakai lagi.
        removeLocal(PAYMENT_KEY);
        location.assign(form.dataset.nextUrl);
    });
}

/** Pengguna masuk + buku alamat terisi: pilih alamat tersimpan atau "Pakai alamat lain". */
function initAddressPicker(root, form, restored) {
    // Hanya membaca; seed dibuat di halaman buku alamat.
    const addresses = isLoggedIn() ? [...(readAddresses() ?? [])].sort((a, b) => Number(b.primary) - Number(a.primary)) : [];
    const saveCheckbox = form.querySelector('[data-save-address]');
    if (addresses.length === 0) {
        return { shouldSave: () => false };
    }

    const picker = form.querySelector('[data-saved-addresses]');
    const fields = form.querySelector('[data-address-fields]');
    const hint = form.querySelector('[data-address-hint]');
    const edit = form.querySelector('[data-address-edit]');
    const saveOption = form.querySelector('[data-save-address-option]');
    const template = root.querySelector('[data-saved-address-template]');
    const input = (name) => form.querySelector(`[data-field="${name}"]`);
    const inputs = Object.keys(ADDRESS_FIELDS).map(input);

    picker.querySelector('[data-saved-address-new]').before(...addresses.map((item) => buildAddressCard(template, item)));
    picker.hidden = false;

    // Draf per pilihan ("new" / id kartu) agar ketikan & edit "Ubah" tidak hilang saat berpindah.
    const drafts = new Map();
    let current = null;
    const showFields = (visible) => {
        fields.hidden = !visible;
        edit.hidden = visible;
        edit.setAttribute('aria-expanded', String(visible));
    };

    const select = (id, { fill = true } = {}) => {
        if (current !== null) {
            drafts.set(current, { values: inputs.map((element) => element.value), revealed: !fields.hidden });
        }
        const item = addresses.find((entry) => entry.id === id);
        current = item ? item.id : 'new';
        picker.querySelector(`[name="saved-address"][value="${CSS.escape(current)}"]`).checked = true;
        inputs.forEach((element) => clearError(element));
        saveOption.hidden = Boolean(item);

        const draft = drafts.get(current);
        if (draft) {
            inputs.forEach((element, index) => {
                element.value = draft.values[index];
            });
        } else if (fill) {
            Object.entries(ADDRESS_FIELDS).forEach(([name, key]) => {
                input(name).value = item?.[key] ?? '';
            });
        }
        if (!item) {
            hint.hidden = true;
            showFields(true);
            return;
        }

        // Alamat tersimpan yang tak lolos aturan checkout: tampilkan field + error langsung.
        const invalid = inputs.filter((element) => inputError(element) !== null);
        invalid.forEach((element) => validateInput(element));
        hint.hidden = !(item.district === '' || item.province === '' || input('address_province').value !== item.province);
        showFields(invalid.length > 0 || Boolean(draft?.revealed));
    };

    picker.addEventListener('change', (event) => {
        if (event.target.name === 'saved-address') {
            select(event.target.value);
        }
    });
    // Edit sekali pakai; buku alamat tidak berubah.
    edit.addEventListener('click', () => {
        showFields(true);
        inputs[0].focus();
    });

    // Kembali dari pembayaran: pilih kartu yang sama persis, selain itu "Pakai alamat lain".
    const match = restored && addresses.find((item) => item.address === restored.street && item.province === restored.province
        && item.city === restored.city && item.district === restored.district && item.postal === restored.postalCode);
    if (match) {
        select(match.id);
    } else if (restored) {
        select('new', { fill: false });
    } else {
        select(addresses[0].id);
    }

    // Sesi dicek ulang saat submit: sudah keluar = tidak menyimpan.
    return { shouldSave: () => isLoggedIn() && current === 'new' && saveCheckbox.checked };
}

function isLoggedIn() {
    const session = readLocal(SESSION_KEY);

    return typeof session?.name === 'string' && session.name.trim() !== '';
}

function buildAddressCard(template, item) {
    const card = template.content.firstElementChild.cloneNode(true);
    const slot = (name) => card.querySelector(`[data-slot="${name}"]`);
    card.querySelector('[name="saved-address"]').value = item.id;
    slot('label').textContent = item.label;
    slot('primary').hidden = !item.primary;
    slot('name').textContent = item.name;
    slot('address').textContent = item.address;
    slot('region').textContent = formatRegion(item);

    return card;
}

function clearError(input) {
    input.removeAttribute('aria-invalid');
    const error = document.getElementById(`${input.id}-error`);
    if (error) {
        error.hidden = true;
    }
}

/** Validasi ulang kursi di server (Livewire Checkout::validateSeats). */
async function checkSeats(root, cart) {
    const trips = cart.items.filter((item) => !isProductItem(item));
    const items = trips.map(({ key, tripId, date, qty, seats }) => ({ key, tripId, date, qty, seats }));
    if (items.length === 0) {
        return { valid: true };
    }

    try {
        const component = Livewire.find(root.closest('[wire\\:id]')?.getAttribute('wire:id'));
        const result = await component.validateSeats(items);
        if (result?.valid === true) {
            return { valid: true };
        }
        // Awali tiap pesan dengan label trip agar jelas item mana yang bermasalah.
        const errors = result?.errors && typeof result.errors === 'object' ? Object.entries(result.errors) : [];
        const message = errors
            .filter(([, error]) => typeof error === 'string')
            .map(([key, error]) => {
                const trip = trips.find((item) => item.key === key);
                return trip ? `${trip.origin} → ${trip.destination}, ${trip.dateLabel}: ${error}` : error;
            })
            .join(' ');

        return { valid: false, message: message || 'Kursi tidak tersedia. Pilih ulang kursi.' };
    } catch {
        return { valid: false, message: 'Gagal memeriksa kursi. Coba lagi.' };
    }
}

function showSeatError(form, message) {
    const box = form.querySelector('[data-seat-error]');
    box.querySelector('[data-seat-error-message]').textContent = message ?? '';
    box.hidden = !message;
}

/** Kembali dari pembayaran: isi ulang form. */
function restoreSaved(form, saved) {
    if (!saved?.contact) {
        return;
    }
    Object.entries(SAVED_FIELDS).forEach(([name, read]) => {
        const value = read(saved);
        form.querySelector(`[data-field="${name}"]`).value = typeof value === 'string' ? value : '';
    });
    const method = [...form.querySelectorAll('[name="method"]')].find((input) => input.value === saved.methodId);
    if (method) {
        method.checked = true;
    }
}

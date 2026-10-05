import { readSession, writeSession } from './utils/storage';

export const BOOKING_STORAGE_KEY = 'ri_booking';

const NAME_PATTERN = /^[\p{L}][\p{L} .'-]{2,}$/u;
const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
const PHONE_PATTERN = /^(\+62|62|0)8[0-9]{7,12}$/;

/** Kembalikan pesan error atau null bila valid. */
const RULES = {
    name: (value) => (NAME_PATTERN.test(value) ? null : 'Isi nama minimal 3 huruf.'),
    email: (value) => (EMAIL_PATTERN.test(value) ? null : 'Masukkan email yang valid, contoh nama@email.com.'),
    phone: (value) => (PHONE_PATTERN.test(normalizeSpaces(value)) ? null : 'Nomor HP harus diawali 08 atau +628.'),
    'phone-optional': (value) => (value === '' ? null : RULES.phone(value)),
    agree: (_value, input) => (input.checked ? null : 'Centang persetujuan untuk melanjutkan.'),
};

export function initBookingForm(root) {
    const form = root.querySelector('[data-booking-form]');
    if (!form) {
        return;
    }

    const orderKey = form.dataset.orderKey;
    const ruleInputs = [...form.querySelectorAll('[data-rule]')];

    restoreSavedData(form, orderKey);

    ruleInputs.forEach((input) => {
        input.addEventListener('blur', () => validateInput(input));
        input.addEventListener('input', () => {
            if (input.getAttribute('aria-invalid') === 'true') {
                validateInput(input);
            }
        });
    });

    initSameAsContact(form);

    form.addEventListener('submit', (event) => {
        const invalidInputs = ruleInputs.filter((input) => !validateInput(input));
        if (invalidInputs.length > 0) {
            event.preventDefault();
            invalidInputs[0].focus();
            invalidInputs[0].scrollIntoView({ block: 'center', behavior: 'smooth' });
            return;
        }

        // Data pribadi hanya ke sessionStorage; field-nya tidak punya atribut name sehingga tidak ikut URL.
        writeSession(BOOKING_STORAGE_KEY, { orderKey, ...collectBookingData(form) });
    });
}

function validateInput(input) {
    const message = RULES[input.dataset.rule](input.value.trim(), input);
    const errorId = input.getAttribute('aria-describedby')?.split(' ').find((id) => id.endsWith('-error'));
    const error = errorId ? document.getElementById(errorId) : null;

    input.setAttribute('aria-invalid', String(message !== null));
    if (error) {
        error.hidden = message === null;
        error.textContent = message ?? '';
    }

    return message === null;
}

function initSameAsContact(form) {
    const checkbox = form.querySelector('[data-same-as-contact]');
    const contactName = form.querySelector('[data-field="contact_name"]');
    const contactPhone = form.querySelector('[data-field="contact_phone"]');
    const firstPassenger = form.querySelector('[data-passenger]');

    const sync = () => {
        if (!checkbox.checked) {
            return;
        }
        firstPassenger.querySelector('[data-field="name"]').value = contactName.value;
        firstPassenger.querySelector('[data-field="phone"]').value = contactPhone.value;
    };

    checkbox.addEventListener('change', sync);
    contactName.addEventListener('input', sync);
    contactPhone.addEventListener('input', sync);
}

function collectBookingData(form) {
    const field = (scope, name) => scope.querySelector(`[data-field="${name}"]`).value.trim();

    return {
        contact: {
            name: field(form, 'contact_name'),
            email: field(form, 'contact_email').toLowerCase(),
            phone: normalizeSpaces(field(form, 'contact_phone')),
        },
        passengers: [...form.querySelectorAll('[data-passenger]')].map((fieldset) => ({
            seat: fieldset.dataset.seat,
            name: field(fieldset, 'name'),
            phone: normalizeSpaces(field(fieldset, 'phone')),
        })),
    };
}

/** Kembali dari halaman pembayaran: isi ulang form dari sessionStorage. */
function restoreSavedData(form, orderKey) {
    const saved = readSession(BOOKING_STORAGE_KEY);
    if (!saved || saved.orderKey !== orderKey) {
        return;
    }

    form.querySelector('[data-field="contact_name"]').value = saved.contact.name;
    form.querySelector('[data-field="contact_email"]').value = saved.contact.email;
    form.querySelector('[data-field="contact_phone"]').value = saved.contact.phone;
    form.querySelectorAll('[data-passenger]').forEach((fieldset) => {
        const passenger = saved.passengers.find((item) => item.seat === fieldset.dataset.seat);
        if (passenger) {
            fieldset.querySelector('[data-field="name"]').value = passenger.name;
            fieldset.querySelector('[data-field="phone"]').value = passenger.phone;
        }
    });
}

function normalizeSpaces(value) {
    return value.replace(/[\s-]/g, '');
}

const NAME_PATTERN = /^[\p{L}][\p{L} .'-]{2,}$/u;
const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
const PHONE_PATTERN = /^(\+62|62|0)8[0-9]{7,12}$/;
const PLACE_PATTERN = /^[\p{L}][\p{L} .'()-]{2,59}$/u;
const POSTAL_PATTERN = /^[1-9][0-9]{4}$/;
const INSTAGRAM_PATTERN = /^[A-Za-z0-9._]{1,30}$/;
const REFERRAL_PATTERN = /^[A-Za-z0-9-]{3,20}$/;

export const normalizePhone = (value) => value.replace(/[\s-]/g, '');
export const normalizeInstagram = (value) => value.trim().replace(/^@/, '').toLowerCase();

/** Tiap aturan mengembalikan pesan error atau null bila valid. */
const RULES = {
    name: (value) => (NAME_PATTERN.test(value) ? null : 'Isi nama minimal 3 huruf.'),
    email: (value) => (EMAIL_PATTERN.test(value) ? null : 'Masukkan email yang valid, contoh nama@email.com.'),
    phone: (value) => (PHONE_PATTERN.test(normalizePhone(value)) ? null : 'Nomor HP harus diawali 08 atau +628.'),
    password: (value) => (value.length >= 8 ? null : 'Kata sandi minimal 8 karakter.'),
    required: (value) => (value === '' ? 'Wajib diisi.' : null),
    agree: (_value, input) => (input.checked ? null : 'Centang persetujuan untuk melanjutkan.'),
    choice: (_value, input) => (input.form.querySelector(`[name="${input.name}"]:checked`) ? null : 'Pilih salah satu.'),
    'same-as': (value, input) => (value === input.form.querySelector(input.dataset.sameAs)?.value ? null : 'Konfirmasi kata sandi tidak sama.'),
    // <select>: nilai wajib salah satu opsi yang tersedia.
    option: (value, input) => (value !== '' && [...input.options].some((option) => option.value === value) ? null : 'Pilih salah satu.'),
    address: (value) => (value.length >= 10 && value.length <= 200 ? null : 'Isi alamat lengkap minimal 10 karakter.'),
    place: (value) => (PLACE_PATTERN.test(value) ? null : 'Isi minimal 3 huruf, tanpa angka.'),
    postal: (value) => (POSTAL_PATTERN.test(value) ? null : 'Kode pos harus 5 angka.'),
    // Opsional: kosong lolos.
    instagram: (value) => (value === '' || INSTAGRAM_PATTERN.test(normalizeInstagram(value)) ? null : 'Username hanya huruf, angka, titik, atau garis bawah.'),
    referral: (value) => (value === '' || REFERRAL_PATTERN.test(value) ? null : 'Kode referral 3-20 huruf, angka, atau tanda hubung.'),
};

/** Cek tanpa menampilkan error; null bila valid. */
export const inputError = (input) => RULES[input.dataset.rule]?.(input.value.trim(), input) ?? null;

/** Error ditulis ke elemen `<id input>-error` yang terhubung lewat aria-describedby. */
export function validateInput(input) {
    const message = inputError(input);
    const errorId = input.getAttribute('aria-describedby')?.split(' ').find((id) => id.endsWith('-error'));
    const error = errorId ? document.getElementById(errorId) : null;

    input.setAttribute('aria-invalid', String(message !== null));
    if (error) {
        error.hidden = message === null;
        error.textContent = message ?? '';
    }

    return message === null;
}

/** Validasi saat blur, ulangi saat mengetik bila sudah pernah salah. */
export function bindLiveValidation(form) {
    form.addEventListener('focusout', (event) => {
        if (event.target.matches?.('[data-rule]') && event.target.type !== 'radio') {
            validateInput(event.target);
        }
    });
    form.addEventListener('input', (event) => {
        // Grup radio: aturan dipasang di satu radio saja.
        const input = event.target.type === 'radio'
            ? form.querySelector(`[name="${event.target.name}"][data-rule]`)
            : event.target;
        if (input?.matches('[data-rule]') && input.getAttribute('aria-invalid') === 'true') {
            validateInput(input);
        }
    });
}

/** Validasi semua field; fokus ke error pertama. */
export function validateForm(form) {
    const invalid = [...form.querySelectorAll('[data-rule]')].filter((input) => !validateInput(input));
    if (invalid.length > 0) {
        invalid[0].focus();
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        invalid[0].scrollIntoView({ block: 'center', behavior: reduceMotion ? 'auto' : 'smooth' });
    }

    return invalid.length === 0;
}

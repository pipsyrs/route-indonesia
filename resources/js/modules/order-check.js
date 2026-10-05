const BOOKING_CODE_PATTERN = /^RID-[A-Z0-9]{6}$/;

/**
 * Hanya validasi format. Lookup ke server (POST + rate limit) dibuat di
 * fase berikutnya, jadi tombol kirim masih nonaktif.
 */
export function initOrderCheck(root) {
    const form = root.querySelector('[data-order-check-form]');
    if (!form) {
        return;
    }

    const codeInput = form.querySelector('[data-order-code]');
    const codeError = document.getElementById('kode-booking-error');

    codeInput.addEventListener('input', () => {
        codeInput.value = codeInput.value.toUpperCase().replace(/\s/g, '');
    });
    codeInput.addEventListener('blur', () => {
        const value = codeInput.value.trim();
        const isValid = value === '' || BOOKING_CODE_PATTERN.test(value);
        codeInput.setAttribute('aria-invalid', String(!isValid));
        codeError.hidden = isValid;
        codeError.textContent = isValid ? '' : 'Kode booking harus berformat RID-XXXXXX.';
    });
    form.addEventListener('submit', (event) => event.preventDefault());
}

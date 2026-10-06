export function initSearchForm(form) {
    const from = form.querySelector('[data-search-from]');
    const to = form.querySelector('[data-search-to]');
    const date = form.querySelector('[data-search-date]');
    const error = form.querySelector('[data-search-error]');

    form.querySelector('[data-search-swap]')?.addEventListener('click', () => {
        [from.value, to.value] = [to.value, from.value];
    });

    form.addEventListener('submit', (event) => {
        const message = getSearchError(from.value, to.value, date.value, date.min, form.dataset);
        error.hidden = message === null;
        error.textContent = message ?? '';
        if (message !== null) {
            event.preventDefault();
        }
    });
}

function getSearchError(origin, destination, date, minDate, labels) {
    if (origin === destination) {
        return labels.errorSameCity ?? 'Kota asal dan tujuan tidak boleh sama.';
    }
    if (!date) {
        return labels.errorNoDate ?? 'Pilih tanggal berangkat.';
    }
    // Format Y-m-d bisa dibandingkan sebagai string.
    if (minDate && date < minDate) {
        return labels.errorPastDate ?? 'Tanggal berangkat tidak boleh sebelum hari ini.';
    }

    return null;
}

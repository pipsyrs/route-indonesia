import { formatRupiah } from './utils/format';

/** Jam mulai (inklusif) dan akhir (eksklusif) tiap rentang waktu berangkat. */
const TIME_SLOTS = { pagi: [0, 11], siang: [11, 15], sore: [15, 18], malam: [18, 24] };

const SORTERS = {
    depart: (a, b) => a.dataset.depart.localeCompare(b.dataset.depart),
    price: (a, b) => Number(a.dataset.price) - Number(b.dataset.price),
    duration: (a, b) => Number(a.dataset.duration) - Number(b.dataset.duration),
    rating: (a, b) => Number(b.dataset.rating) - Number(a.dataset.rating),
};

export function initSearchFilter(root) {
    const form = root.querySelector('[data-filter-form]');
    const list = root.querySelector('[data-filter-list]');
    if (!form || !list) {
        return;
    }

    const cards = [...list.querySelectorAll('[data-trip-card]')];
    const sortSelect = root.querySelector('[data-filter-sort]');
    const priceInput = form.querySelector('[data-filter-price]');
    const priceLabel = form.querySelector('[data-filter-price-label]');
    const count = root.querySelector('[data-filter-count]');
    const empty = root.querySelector('[data-filter-empty]');

    const render = () => {
        const criteria = readCriteria(form, priceInput);
        priceLabel.textContent = formatRupiah(criteria.maxPrice);

        const sorted = [...cards].sort(SORTERS[sortSelect.value] ?? SORTERS.depart);
        let visibleCount = 0;
        for (const card of sorted) {
            const isVisible = matchesCriteria(card, criteria);
            card.hidden = !isVisible;
            visibleCount += isVisible ? 1 : 0;
            list.append(card);
        }

        count.textContent = String(visibleCount);
        empty.hidden = visibleCount > 0;
    };

    form.addEventListener('input', render);
    sortSelect.addEventListener('change', render);
    // Event reset terjadi sebelum nilai form dikembalikan; render setelahnya.
    form.addEventListener('reset', () => setTimeout(render));
    root.querySelectorAll('[data-filter-reset]').forEach((button) => {
        button.addEventListener('click', () => form.reset());
    });

    initFilterDrawer(root);
}

function readCriteria(form, priceInput) {
    const checked = (name) => [...form.querySelectorAll(`input[name="${name}"]:checked`)].map((input) => input.value);

    return {
        timeSlots: checked('time'),
        operators: checked('operator'),
        facilities: checked('facility'),
        maxPrice: Number(priceInput.value),
    };
}

function matchesCriteria(card, criteria) {
    const hour = Number(card.dataset.depart.split(':')[0]);
    const facilities = card.dataset.facilities.split(',');

    const matchesTime = criteria.timeSlots.length === 0
        || criteria.timeSlots.some((slot) => hour >= TIME_SLOTS[slot][0] && hour < TIME_SLOTS[slot][1]);
    const matchesOperator = criteria.operators.length === 0 || criteria.operators.includes(card.dataset.operator);
    const matchesFacilities = criteria.facilities.every((code) => facilities.includes(code));

    return matchesTime && matchesOperator && matchesFacilities && Number(card.dataset.price) <= criteria.maxPrice;
}

function initFilterDrawer(root) {
    const panel = root.querySelector('[data-filter-panel]');
    const openButton = root.querySelector('[data-filter-open]');

    const setOpen = (isOpen) => {
        panel.classList.toggle('hidden', !isOpen);
        openButton?.setAttribute('aria-expanded', String(isOpen));
        document.body.classList.toggle('overflow-hidden', isOpen);
    };

    openButton?.addEventListener('click', () => setOpen(true));
    root.querySelectorAll('[data-filter-close]').forEach((button) => {
        button.addEventListener('click', () => setOpen(false));
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !panel.classList.contains('hidden')) {
            setOpen(false);
        }
    });
}

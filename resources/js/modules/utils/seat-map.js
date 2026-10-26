const SEAT_PATTERN = /^[0-9]{1,2}$/;

/** Sel denah: 'D' = sopir, null = lorong, string angka = kursi. */
export const isSeatCode = (cell) => typeof cell === 'string' && SEAT_PATTERN.test(cell);

export const checkedSeats = (grid) => [...grid.querySelectorAll('[data-seat]:checked')].map((input) => input.value);

/** Render denah ke grid dari template <x-seat-map-templates>; hanya textContent/atribut. */
export function renderSeatMap(grid, { layout, occupied, selected = [] }) {
    const templates = {
        driver: document.querySelector('[data-seat-driver-template]'),
        aisle: document.querySelector('[data-seat-aisle-template]'),
        seat: document.querySelector('[data-seat-cell-template]'),
    };
    const clone = (template) => template.content.firstElementChild.cloneNode(true);

    grid.style.gridTemplateColumns = `repeat(${Math.max(...layout.map((row) => row.length))}, minmax(0, 1fr))`;
    grid.replaceChildren(...layout.flat().map((cell) => {
        if (cell === 'D') {
            return clone(templates.driver);
        }
        if (!isSeatCode(cell)) {
            return clone(templates.aisle);
        }

        const label = clone(templates.seat);
        const input = label.querySelector('[data-seat]');
        const isOccupied = occupied.includes(cell);
        input.value = cell;
        input.disabled = isOccupied;
        input.checked = !isOccupied && selected.includes(cell);
        input.setAttribute('aria-label', `Kursi ${cell}, ${isOccupied ? 'terisi' : 'tersedia'}`);
        label.querySelector('[data-seat-label]').textContent = cell;

        return label;
    }));
}

/** Batasi jumlah centang; @returns {boolean} false bila pilihan terakhir dibatalkan */
export function enforceSeatLimit(input, grid, limit) {
    if (input.checked && checkedSeats(grid).length > limit) {
        input.checked = false;
        return false;
    }

    return true;
}

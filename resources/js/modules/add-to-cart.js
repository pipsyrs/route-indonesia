import { CART_KEY, getCart, seatCodes, upsertTrip } from './cart';
import { formatRupiah } from './utils/format';
import { checkedSeats, enforceSeatLimit, renderSeatMap } from './utils/seat-map';

/** Detail trip: qty = jumlah kursi yang dipilih di denah. */
export function initAddToCart(root) {
    const form = root.querySelector('[data-add-to-cart-form]');
    const trip = readTripJson(root);
    if (!form || !trip || trip.maxQty < 1) {
        return;
    }

    const key = `${trip.tripId}|${trip.date}`;
    const grid = form.querySelector('[data-seat-grid]');
    const error = form.querySelector('[data-seat-error]');
    const summary = form.querySelector('[data-seat-summary]');
    const subtotal = form.querySelector('[data-qty-subtotal]');
    const submit = form.querySelector('[data-add-submit]');
    const notice = form.querySelector('[data-added-notice]');
    const codes = seatCodes(trip.seatLayout);

    // Trip yang sudah di keranjang: kursi dipilih ulang, tombol mengganti kursi.
    const inCart = () => getCart().items.find((item) => item.key === key) ?? null;
    const saved = (inCart()?.seats ?? [])
        .filter((seat) => codes.includes(seat) && !trip.occupied.includes(seat))
        .slice(0, trip.maxQty);
    // Titik jemput/antar ikut item keranjang agar "Perbarui" tidak menimpanya.
    [['[data-pickup]', inCart()?.pickup], ['[data-dropoff]', inCart()?.dropoff]].forEach(([selector, point]) => {
        const select = form.querySelector(selector);
        if (select && point && [...select.options].some((option) => option.value === String(point.id))) {
            select.value = String(point.id);
        }
    });

    const showError = (message) => {
        error.textContent = message ?? '';
        error.hidden = !message;
    };
    const render = () => {
        const seats = checkedSeats(grid);
        summary.textContent = seats.length > 0 ? seats.join(', ') : '-';
        subtotal.textContent = formatRupiah(trip.price * seats.length);
        submit.disabled = seats.length < 1;
        form.querySelector('[data-add-label]').textContent = inCart() ? 'Perbarui kursi di keranjang' : 'Tambah ke keranjang';
    };

    renderSeatMap(grid, { layout: trip.seatLayout, occupied: trip.occupied, selected: saved });

    grid.addEventListener('change', (event) => {
        const input = event.target.closest('[data-seat]');
        if (input) {
            showError(enforceSeatLimit(input, grid, trip.maxQty) ? null : `Maksimal ${trip.maxQty} kursi per jadwal.`);
            render();
        }
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const seats = checkedSeats(grid);
        if (seats.length < 1) {
            showError('Pilih minimal 1 kursi.');
            return;
        }

        const result = upsertTrip({
            key,
            tripId: trip.tripId,
            operator: trip.operator,
            vehicle: trip.vehicle,
            origin: trip.origin,
            destination: trip.destination,
            date: trip.date,
            dateLabel: trip.dateLabel,
            depart: trip.depart,
            arrive: trip.arrive,
            price: trip.price,
            maxQty: trip.maxQty,
            pickup: pickPoint(trip.pickups, form.querySelector('[data-pickup]')),
            dropoff: pickPoint(trip.dropoffs, form.querySelector('[data-dropoff]')),
            seatLayout: trip.seatLayout,
            occupied: trip.occupied,
            seats,
            addedAt: new Date().toISOString(),
        });

        if (!result) {
            showError('Kursi tidak valid. Pilih ulang kursi.');
            return;
        }
        showError(null);
        notice.querySelector('[data-added-text]').textContent = result === 'updated'
            ? `Kursi di keranjang diperbarui: ${seats.join(', ')}`
            : 'Ditambahkan ke keranjang';
        notice.hidden = false;
        render();
    });

    window.addEventListener('cart:change', render);
    // Keranjang diubah di tab lain: sinkronkan label tombol.
    window.addEventListener('storage', (event) => {
        if (event.key === CART_KEY || event.key === null) {
            render();
        }
    });
    render();
}

function readTripJson(root) {
    try {
        return JSON.parse(root.querySelector('[data-trip-json]')?.textContent ?? 'null');
    } catch {
        return null;
    }
}

function pickPoint(points, select) {
    const id = select ? Number(select.value) : points[0]?.id;

    return points.find((point) => point.id === id) ?? points[0] ?? null;
}

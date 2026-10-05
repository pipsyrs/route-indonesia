import { addItem } from './cart';
import { showToast } from './toast';
import { formatRupiah } from './utils/format';

export function initAddToCart(root) {
    const form = root.querySelector('[data-add-to-cart-form]');
    const trip = readTripJson(root);
    if (!form || !trip || trip.maxQty < 1) {
        return;
    }

    const input = form.querySelector('[data-qty-input]');
    const subtotal = form.querySelector('[data-qty-subtotal]');
    const notice = form.querySelector('[data-added-notice]');

    const clamp = (value) => Math.min(Math.max(Math.trunc(Number(value)) || 1, 1), trip.maxQty);
    const render = () => {
        input.value = String(clamp(input.value));
        subtotal.textContent = formatRupiah(trip.price * Number(input.value));
    };

    form.querySelectorAll('[data-qty-step]').forEach((button) => {
        button.addEventListener('click', () => {
            input.value = String(clamp(Number(input.value) + Number(button.dataset.qtyStep)));
            render();
        });
    });
    input.addEventListener('change', render);

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        render();

        const result = addItem({
            key: `${trip.tripId}|${trip.date}`,
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
            qty: Number(input.value),
            maxQty: trip.maxQty,
            pickup: pickPoint(trip.pickups, form.querySelector('[data-pickup]')),
            dropoff: pickPoint(trip.dropoffs, form.querySelector('[data-dropoff]')),
            addedAt: new Date().toISOString(),
        });

        if (result.added === 0) {
            showToast(`Batas maksimal ${result.maxQty} tiket untuk jadwal ini sudah tercapai`);
            return;
        }
        notice.hidden = false;
        showToast(result.qty === result.maxQty && result.added < Number(input.value)
            ? `Hanya ${result.added} tiket ditambahkan (maksimal ${result.maxQty} per jadwal)`
            : 'Tiket ditambahkan ke keranjang');
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

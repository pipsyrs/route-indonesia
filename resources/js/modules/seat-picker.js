import { formatRupiah } from './utils/format';
import { showToast } from './toast';

export function initSeatPicker(root) {
    const form = root.querySelector('[data-seat-form]');
    if (!form) {
        return;
    }

    const maxSeats = Number(form.dataset.maxSeats);
    const price = Number(form.dataset.price);
    const serviceFee = Number(form.dataset.serviceFee);
    const seatInputs = [...form.querySelectorAll('[data-seat]')];
    const submitButtons = form.querySelectorAll('[data-seat-submit]');
    const error = form.querySelector('[data-seat-error]');

    const selectedSeats = () => seatInputs.filter((input) => input.checked).map((input) => input.value);

    const render = () => {
        const seats = selectedSeats();
        const subtotal = price * seats.length;
        const total = seats.length > 0 ? subtotal + serviceFee : serviceFee;

        setText(form, '[data-seat-count]', String(seats.length));
        setText(form, '[data-summary-seat-count]', String(seats.length));
        setText(form, '[data-summary-seats]', seats.length > 0 ? seats.join(', ') : 'Belum dipilih');
        setText(form, '[data-summary-subtotal]', formatRupiah(subtotal));
        setText(form, '[data-summary-total]', formatRupiah(total));
        setText(form, '[data-summary-total-mobile]', formatRupiah(total));
        setText(form, '[data-summary-pickup]', form.querySelector('[data-stop="pickup"]:checked')?.dataset.stopLabel ?? '-');
        setText(form, '[data-summary-dropoff]', form.querySelector('[data-stop="dropoff"]:checked')?.dataset.stopLabel ?? '-');

        const isComplete = seats.length === maxSeats;
        submitButtons.forEach((button) => {
            button.disabled = !isComplete;
        });
        if (isComplete) {
            error.hidden = true;
        }
    };

    form.addEventListener('change', (event) => {
        if (event.target.matches('[data-seat]') && event.target.checked && selectedSeats().length > maxSeats) {
            event.target.checked = false;
            showToast(`Maksimal ${maxSeats} kursi`);
        }
        render();
    });

    form.addEventListener('submit', (event) => {
        if (selectedSeats().length !== maxSeats) {
            event.preventDefault();
            error.textContent = `Pilih tepat ${maxSeats} kursi untuk melanjutkan.`;
            error.hidden = false;
        }
    });

    render();
}

function setText(root, selector, text) {
    root.querySelectorAll(selector).forEach((element) => {
        element.textContent = text;
    });
}
